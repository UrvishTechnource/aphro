<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Eav\Model\Validator\Attribute\Backend;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Handle various customer account actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AccountManagement implements AccountManagementInterface
{
    /**
     * Configuration paths for email templates and identities
     *
     * @deprecated
     */
    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';

    /**
     * @deprecated
     */
    const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'customer/create_account/email_no_password_template';

    /**
     * @deprecated
     */
    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';

    /**
     * @deprecated
     */
    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'customer/password/remind_email_template';

    /**
     * @deprecated
     */
    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'customer/password/forgot_email_template';

    /**
     * @deprecated
     */
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';

    const XML_PATH_IS_CONFIRM = 'customer/create_account/confirm';

    /**
     * @deprecated
     */
    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'customer/create_account/email_confirmation_template';

    /**
     * @deprecated
     */
    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'customer/create_account/email_confirmed_template';

    /**
     * Constants for the type of new account email to be sent
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /**
     * Welcome email, when password setting is required
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * Welcome email, when confirmation is enabled
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Confirmation email, when account is confirmed
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';

    /**
     * Constants for types of emails to send out.
     * pdl:
     * forgot, remind, reset email templates
     */
    const EMAIL_REMINDER = 'email_reminder';

    const EMAIL_RESET = 'email_reset';

    /**
     * Configuration path to customer password minimum length
     */
    const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'customer/password/minimum_password_length';

    /**
     * Configuration path to customer password required character classes number
     */
    const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'customer/password/required_character_classes_number';

    /**
     * @deprecated
     */
    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'customer/password/reset_password_template';

    /**
     * @deprecated
     */
    const MIN_PASSWORD_LENGTH = 6;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory
     */
    private $validationResultsDataFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadataService;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var ConfigShare
     */
    private $configShare;

    /**
     * @var StringHelper
     */
    protected $stringHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var CustomerModel
     */
    protected $customerModel;

    /**
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Backend
     */
    private $eavValidator;

    /**
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Validator $validator
     * @param ValidationResultsInterfaceFactory $validationResultsDataFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerMetadataInterface $customerMetadataService
     * @param CustomerRegistry $customerRegistry
     * @param PsrLogger $logger
     * @param Encryptor $encryptor
     * @param ConfigShare $configShare
     * @param StringHelper $stringHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param Registry $registry
     * @param CustomerViewHelper $customerViewHelper
     * @param DateTime $dateTime
     * @param CustomerModel $customerModel
     * @param ObjectFactory $objectFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CustomerFactory $customerFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Validator $validator,
        ValidationResultsInterfaceFactory $validationResultsDataFactory,
        AddressRepositoryInterface $addressRepository,
        CustomerMetadataInterface $customerMetadataService,
        CustomerRegistry $customerRegistry,
        PsrLogger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        Registry $registry,
        CustomerViewHelper $customerViewHelper,
        DateTime $dateTime,
        CustomerModel $customerModel,
        ObjectFactory $objectFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->validator = $validator;
        $this->validationResultsDataFactory = $validationResultsDataFactory;
        $this->addressRepository = $addressRepository;
        $this->customerMetadataService = $customerMetadataService;
        $this->customerRegistry = $customerRegistry;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->configShare = $configShare;
        $this->stringHelper = $stringHelper;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->registry = $registry;
        $this->customerViewHelper = $customerViewHelper;
        $this->dateTime = $dateTime;
        $this->customerModel = $customerModel;
        $this->objectFactory = $objectFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resendConfirmation($email, $websiteId = null, $redirectUrl = '')
    {
        $customer = $this->customerRepository->get($email, $websiteId);
        if (!$customer->getConfirmation()) {
            throw new InvalidTransitionException(__('No confirmation needed.'));
        }

        try {
            $this->getEmailNotification()->newAccount(
                $customer,
                self::NEW_ACCOUNT_EMAIL_CONFIRMATION,
                $redirectUrl,
                $this->storeManager->getStore()->getId()
            );
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function activate($email, $confirmationKey)
    {
        $customer = $this->customerRepository->get($email);
        return $this->activateCustomer($customer, $confirmationKey);
    }

    /**
     * {@inheritdoc}
     */
    public function activateById($customerId, $confirmationKey)
    {
        $customer = $this->customerRepository->getById($customerId);
        return $this->activateCustomer($customer, $confirmationKey);
    }

    /**
     * Activate a customer account using a key that was sent in a confirmation email.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $confirmationKey
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    private function activateCustomer($customer, $confirmationKey)
    {
        // check if customer is inactive
        if (!$customer->getConfirmation()) {
            throw new InvalidTransitionException(__('Account already active'));
        }

        if ($customer->getConfirmation() !== $confirmationKey) {
            throw new InputMismatchException(__('Invalid confirmation token'));
        }

        $customer->setConfirmation(null);
        $this->customerRepository->save($customer);
        $this->getEmailNotification()->newAccount($customer, 'confirmed', '', $this->storeManager->getStore()->getId());
        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($username, $password)
    {
      
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerObj = $objectManager->create('Magento\Customer\Model\ResourceModel\Customer\Collection');
        $collection = $customerObj->addAttributeToSelect('*')->addAttributeToFilter('uname',$username)->load();
        if(!empty($collection->getData())){
          $c_data=$collection->getData();
          $customer_id = $c_data[0]['entity_id'];
          $customer = $this->customerRepository->getById($customer_id);
          //print_r($customer);
        } else {
          try {
              $customer = $this->customerRepository->get($username);
              $customerId = $customer->getId();
              //echo $customerId;die;
          } catch (NoSuchEntityException $e) {
              throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
          }
          $customer_id = $customer->getId();
        }
        if(empty($customer_id)){
          throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        $customerId = $customer_id;
        //echo $customerId;die;
     
        

        
        if ($this->getAuthentication()->isLocked($customerId)) {
            throw new UserLockedException(__('The account is locked.'));
        }
        try {
            $this->getAuthentication()->authenticate($customerId, $password);
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        if ($customer->getConfirmation() && $this->isConfirmationRequired($customer)) {
            throw new EmailNotConfirmedException(__('This account is not confirmed.'));
        }

        $customerModel = $this->customerFactory->create()->updateData($customer);
        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customerModel, 'password' => $password]
        );

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken)
    {
        $this->validateResetPasswordToken($customerId, $resetPasswordLinkToken);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function initiatePasswordReset($email, $template, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        // load customer by email
        $customer = $this->customerRepository->get($email, $websiteId);

        $newPasswordToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newPasswordToken);

        try {
            switch ($template) {
                case AccountManagement::EMAIL_REMINDER:
                    $this->getEmailNotification()->passwordReminder($customer);
                    break;
                case AccountManagement::EMAIL_RESET:
                    $this->getEmailNotification()->passwordResetConfirmation($customer);
                    break;
                default:
                    throw new InputException(
                        __(
                            'Invalid value of "%value" provided for the %fieldName field.',
                            ['value' => $template, 'fieldName' => 'email type']
                        )
                    );
            }
            return true;
        } catch (MailException $e) {
            // If we are not able to send a reset password email, this should be ignored
            $this->logger->critical($e);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resetPassword($email, $resetToken, $newPassword)
    {
        $customer = $this->customerRepository->get($email);
        //Validate Token and new password strength
        $this->validateResetPasswordToken($customer->getId(), $resetToken);
        $this->checkPasswordStrength($newPassword);
        //Update secure data
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $customerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->customerRepository->save($customer);
        return true;
    }

    /**
     * Make sure that password complies with minimum security requirements.
     *
     * @param string $password
     * @return void
     * @throws InputException
     */
    protected function checkPasswordStrength($password)
    {
        $length = $this->stringHelper->strlen($password);
        if ($length > self::MAX_PASSWORD_LENGTH) {
            throw new InputException(
                __(
                    'Please enter a password with at most %1 characters.',
                    self::MAX_PASSWORD_LENGTH
                )
            );
        }
        $configMinPasswordLength = $this->getMinPasswordLength();
        if ($length < $configMinPasswordLength) {
            throw new InputException(
                __(
                    'Please enter a password with at least %1 characters.',
                    $configMinPasswordLength
                )
            );
        }
        if ($this->stringHelper->strlen(trim($password)) != $length) {
            throw new InputException(__('The password can\'t begin or end with a space.'));
        }

        $requiredCharactersCheck = $this->makeRequiredCharactersCheck($password);
        if ($requiredCharactersCheck !== 0) {
            throw new InputException(
                __(
                    'Minimum of different classes of characters in password is %1.' .
                    ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                    $requiredCharactersCheck
                )
            );
        }
    }

    /**
     * Check password for presence of required character sets
     *
     * @param string $password
     * @return int
     */
    protected function makeRequiredCharactersCheck($password)
    {
        $counter = 0;
        $requiredNumber = $this->scopeConfig->getValue(self::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
        $return = 0;

        if (preg_match('/[0-9]+/', $password)) {
            $counter ++;
        }
        if (preg_match('/[A-Z]+/', $password)) {
            $counter ++;
        }
        if (preg_match('/[a-z]+/', $password)) {
            $counter ++;
        }
        if (preg_match('/[^a-zA-Z0-9]+/', $password)) {
            $counter ++;
        }

        if ($counter < $requiredNumber) {
            $return = $requiredNumber;
        }

        return $return;
    }

    /**
     * Retrieve minimum password length
     *
     * @return int
     */
    protected function getMinPasswordLength()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationStatus($customerId)
    {
        // load customer by id
        $customer = $this->customerRepository->getById($customerId);
        if ($this->isConfirmationRequired($customer)) {
            if (!$customer->getConfirmation()) {
                return self::ACCOUNT_CONFIRMED;
            }
            return self::ACCOUNT_CONFIRMATION_REQUIRED;
        }
        return self::ACCOUNT_CONFIRMATION_NOT_REQUIRED;
    }

    /**
     * {@inheritdoc}
     */
    public function createAccount(CustomerInterface $customer, $password = null, $redirectUrl = '')
    {
        if ($password !== null) {
            $this->checkPasswordStrength($password);
            $hash = $this->createPasswordHash($password);
        } else {
            $hash = null;
        }
        return $this->createAccountWithPasswordHash($customer, $hash, $redirectUrl);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function createAccountWithPasswordHash(CustomerInterface $customer, $hash, $redirectUrl = '')
    {
        // This logic allows an existing customer to be added to a different store.  No new account is created.
        // The plan is to move this logic into a new method called something like 'registerAccountWithStore'
        if ($customer->getId()) {
            $customer = $this->customerRepository->get($customer->getEmail());
            $websiteId = $customer->getWebsiteId();

            if ($this->isCustomerInStore($websiteId, $customer->getStoreId())) {
                throw new InputException(__('This customer already exists in this store.'));
            }
            // Existing password hash will be used from secured customer data registry when saving customer
        }

        // Make sure we have a storeId to associate this customer with.
        if (!$customer->getStoreId()) {
            if ($customer->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $storeId = $this->storeManager->getStore()->getId();
            }
            $customer->setStoreId($storeId);
        }

        // Associate website_id with customer
        if (!$customer->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($customer->getStoreId())->getWebsiteId();
            $customer->setWebsiteId($websiteId);
        }

        // Update 'created_in' value with actual store name
        if ($customer->getId() === null) {
            $storeName = $this->storeManager->getStore($customer->getStoreId())->getName();
            $customer->setCreatedIn($storeName);
        }

        $customerAddresses = $customer->getAddresses() ?: [];
        $customer->setAddresses(null);
        try {
            // If customer exists existing hash will be used by Repository
            $customer = $this->customerRepository->save($customer, $hash);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A customer with the same email already exists in an associated website.')
            );
        } catch (LocalizedException $e) {
            throw $e;
        }
        try {
            foreach ($customerAddresses as $address) {
                 if ($address->getId()) {
                    $newAddress = clone $address;
                    $newAddress->setId(null);
                    $newAddress->setCustomerId($customer->getId());
                    $this->addressRepository->save($newAddress);
                 } else {
                    $address->setCustomerId($customer->getId());
                    $this->addressRepository->save($address);
                }
            }
        } catch (InputException $e) {
            $this->customerRepository->delete($customer);
            throw $e;
        }
        $customer = $this->customerRepository->getById($customer->getId());
        $newLinkToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newLinkToken);
        $this->sendEmailConfirmation($customer, $redirectUrl);

        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBillingAddress($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        return $this->getAddressById($customer, $customer->getDefaultBilling());
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultShippingAddress($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        return $this->getAddressById($customer, $customer->getDefaultShipping());
    }

    /**
     * Send either confirmation or welcome email after an account creation
     *
     * @param CustomerInterface $customer
     * @param string $redirectUrl
     * @return void
     */
    protected function sendEmailConfirmation(CustomerInterface $customer, $redirectUrl)
    {
        try {
            $hash = $this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash();
            $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED;
            if ($this->isConfirmationRequired($customer) && $hash != '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_CONFIRMATION;
            } elseif ($hash == '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD;
            }
            $this->getEmailNotification()->newAccount($customer, $templateType, $redirectUrl, $customer->getStoreId());
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        } 
    }

    /**
     * {@inheritdoc}
     */
    public function changePassword($email, $currentPassword, $newPassword)
    {
        try {
            $customer = $this->customerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return $this->changePasswordForCustomer($customer, $currentPassword, $newPassword);
    }

    /**
     * {@inheritdoc}
     */
    public function changePasswordById($customerId, $currentPassword, $newPassword)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return $this->changePasswordForCustomer($customer, $currentPassword, $newPassword);
    }

    /**
     * Change customer password
     *
     * @param CustomerInterface $customer
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws InputException
     * @throws InvalidEmailOrPasswordException
     * @throws UserLockedException
     */
    private function changePasswordForCustomer($customer, $currentPassword, $newPassword)
    {
        try {
            $this->getAuthentication()->authenticate($customer->getId(), $currentPassword);
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(__('The password doesn\'t match this account.'));
        }
        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $this->checkPasswordStrength($newPassword);
        $customerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->customerRepository->save($customer);
        return true;
    }

    /**
     * Create a hash for the given password
     *
     * @param string $password
     * @return string
     */
    protected function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * @return Backend
     */
    private function getEavValidator()
    {
        if ($this->eavValidator === null) {
            $this->eavValidator = ObjectManager::getInstance()->get(Backend::class);
        }
        return $this->eavValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(CustomerInterface $customer)
    {
        $validationResults = $this->validationResultsDataFactory->create();

        $oldAddresses = $customer->getAddresses();
        $customerModel = $this->customerFactory->create()->updateData(
            $customer->setAddresses([])
        );
        $customer->setAddresses($oldAddresses);

        $result = $this->getEavValidator()->isValid($customerModel);
        if ($result === false && is_array($this->getEavValidator()->getMessages())) {
            return $validationResults->setIsValid(false)
                ->setMessages(
                    call_user_func_array(
                        'array_merge',
                        $this->getEavValidator()->getMessages()
                    )
                );
        }
        return $validationResults->setIsValid(true)
            ->setMessages([]);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailAvailable($customerEmail, $websiteId = null)
    {
        try {
            if ($websiteId === null) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }
            $this->customerRepository->get($customerEmail, $websiteId);
            return false;
        } catch (NoSuchEntityException $e) {
            return true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isCustomerInStore($customerWebsiteId, $storeId)
    {
        $ids = [];
        if ((bool)$this->configShare->isWebsiteScope()) {
            $ids = $this->storeManager->getWebsite($customerWebsiteId)->getStoreIds();
        } else {
            foreach ($this->storeManager->getStores() as $store) {
                $ids[] = $store->getId();
            }
        }

        return in_array($storeId, $ids);
    }

    /**
     * Validate the Reset Password Token for a customer.
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @return bool
     * @throws \Magento\Framework\Exception\State\InputMismatchException If token is mismatched
     * @throws \Magento\Framework\Exception\State\ExpiredException If token is expired
     * @throws \Magento\Framework\Exception\InputException If token or customer id is invalid
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer doesn't exist
     */
    private function validateResetPasswordToken($customerId, $resetPasswordLinkToken)
    {
        if (empty($customerId) || $customerId < 0) {
            throw new InputException(__(
                'Invalid value of "%value" provided for the %fieldName field.',
                ['value' => $customerId, 'fieldName' => 'customerId']
            ));
        }
        if (!is_string($resetPasswordLinkToken) || empty($resetPasswordLinkToken)) {
            $params = ['fieldName' => 'resetPasswordLinkToken'];
            throw new InputException(__('%fieldName is a required field.', $params));
        }

        $customerSecureData = $this->customerRegistry->retrieveSecureData($customerId);
        $rpToken = $customerSecureData->getRpToken();
        $rpTokenCreatedAt = $customerSecureData->getRpTokenCreatedAt();

        if (!Security::compareStrings($rpToken, $resetPasswordLinkToken)) {
            throw new InputMismatchException(__('Reset password token mismatch.'));
        } elseif ($this->isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)) {
            throw new ExpiredException(__('Reset password token expired.'));
        }

        return true;
    }

    /**
     * Check if customer can be deleted.
     *
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If group is not found
     * @throws LocalizedException
     */
    public function isReadonly($customerId)
    {
        $customer = $this->customerRegistry->retrieveSecureData($customerId);
        return !$customer->getDeleteable();
    }

    /**
     * Send email with new account related information
     *
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @param string $sendemailStoreId
     * @return $this
     * @throws LocalizedException
     * @deprecated
     */
    protected function sendNewAccountEmail(
        $customer,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = '0',
        $sendemailStoreId = null
    ) {
        $types = $this->getTemplateTypes();

        if (!isset($types[$type])) {
            throw new LocalizedException(__('Please correct the transactional account email type.'));
        }

        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer, $sendemailStoreId);
        }

        $store = $this->storeManager->getStore($customer->getStoreId());

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            $types[$type],
            self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'back_url' => $backUrl, 'store' => $store],
            $storeId
        );

        return $this;
    }

    /**
     * Send email to customer when his password is reset
     *
     * @param CustomerInterface $customer
     * @return $this
     * @deprecated
     */
    protected function sendPasswordResetNotificationEmail($customer)
    {
        return $this->sendPasswordResetConfirmationEmail($customer);
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param CustomerInterface $customer
     * @param int|string|null $defaultStoreId
     * @return int
     * @deprecated
     */
    protected function getWebsiteStoreId($customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * @return array
     * @deprecated
     */
    protected function getTemplateTypes()
    {
        /**
         * self::NEW_ACCOUNT_EMAIL_REGISTERED               welcome email, when confirmation is disabled
         *                                                  and password is set
         * self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD   welcome email, when confirmation is disabled
         *                                                  and password is not set
         * self::NEW_ACCOUNT_EMAIL_CONFIRMED                welcome email, when confirmation is enabled
         *                                                  and password is set
         * self::NEW_ACCOUNT_EMAIL_CONFIRMATION             email with confirmation link
         */
        $types = [
            self::NEW_ACCOUNT_EMAIL_REGISTERED => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_CONFIRMED => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_CONFIRMATION => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
        ];
        return $types;
    }

    /**
     * Send corresponding email template
     *
     * @param CustomerInterface $customer
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param string $email
     * @return $this
     * @deprecated
     */
    protected function sendEmailTemplate(
        $customer,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
        if ($email === null) {
            $email = $customer->getEmail();
        }

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId))
            ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }

    /**
     * Check if accounts confirmation is required in config
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    protected function isConfirmationRequired($customer)
    {
        if ($this->canSkipConfirmation($customer)) {
            return false;
        }

        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_IS_CONFIRM,
            ScopeInterface::SCOPE_WEBSITES,
            $customer->getWebsiteId()
        );
    }

    /**
     * Check whether confirmation may be skipped when registering using certain email address
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    protected function canSkipConfirmation($customer)
    {
        if (!$customer->getId()) {
            return false;
        }

        /* If an email was used to start the registration process and it is the same email as the one
           used to register, then this can skip confirmation.
           */
        $skipConfirmationIfEmail = $this->registry->registry("skip_confirmation_if_email");
        if (!$skipConfirmationIfEmail) {
            return false;
        }

        return strtolower($skipConfirmationIfEmail) === strtolower($customer->getEmail());
    }

    /**
     * Check if rpToken is expired
     *
     * @param string $rpToken
     * @param string $rpTokenCreatedAt
     * @return bool
     */
    public function isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)
    {
        if (empty($rpToken) || empty($rpTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->customerModel->getResetPasswordLinkExpirationPeriod();

        $currentTimestamp = (new \DateTime())->getTimestamp();
        $tokenTimestamp = (new \DateTime($rpTokenCreatedAt))->getTimestamp();
        if ($tokenTimestamp > $currentTimestamp) {
            return true;
        }

        $hourDifference = floor(($currentTimestamp - $tokenTimestamp) / (60 * 60));
        if ($hourDifference >= $expirationPeriod) {
            return true;
        }

        return false;
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token
     *
     * @param CustomerInterface $customer
     * @param string $passwordLinkToken
     * @return bool
     * @throws InputException
     */
    public function changeResetPasswordLinkToken($customer, $passwordLinkToken)
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $passwordLinkToken, 'fieldName' => 'password reset token']
                )
            );
        }
        if (is_string($passwordLinkToken) && !empty($passwordLinkToken)) {
            $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
            $customerSecure->setRpToken($passwordLinkToken);
            $customerSecure->setRpTokenCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
            $this->customerRepository->save($customer);
        }
        return true;
    }

    /**
     * Send email with new customer password
     *
     * @param CustomerInterface $customer
     * @return $this
     * @deprecated
     */
    public function sendPasswordReminderEmail($customer)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            self::XML_PATH_REMIND_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );

        return $this;
    }

    /**
     * Send email with reset password confirmation link
     *
     * @param CustomerInterface $customer
     * @return $this
     * @deprecated
     */
    public function sendPasswordResetConfirmationEmail($customer)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            self::XML_PATH_FORGOT_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );

        return $this;
    }

    /**
     * Get address by id
     *
     * @param CustomerInterface $customer
     * @param int $addressId
     * @return AddressInterface|null
     */
    protected function getAddressById(CustomerInterface $customer, $addressId)
    {
        foreach ($customer->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        return null;
    }

    /**
     * Create an object with data merged from Customer and CustomerSecure
     *
     * @param CustomerInterface $customer
     * @return Data\CustomerSecure
     * @deprecated
     */
    protected function getFullCustomerObject($customer)
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor
            ->buildOutputDataArray($customer, '\Magento\Customer\Api\Data\CustomerInterface');
        $mergedCustomerData->addData($customerData);
        $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
        return $mergedCustomerData;
    }

    /**
     * Return hashed password, which can be directly saved to database.
     *
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password)
    {
        return $this->encryptor->getHash($password);
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }
}
