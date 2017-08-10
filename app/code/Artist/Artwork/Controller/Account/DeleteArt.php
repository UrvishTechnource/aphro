<?php

/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Artist\Artwork\Controller\Account;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteArt extends \Magento\Customer\Controller\Account\EditPost {

    /**
     * Form code for data extractor
     */
    const FORM_DATA_EXTRACTOR_CODE = 'customer_account_edit';

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /** @var EmailNotificationInterface */
    private $emailNotification;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var Mapper
     */
    private $customerMapper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Validator $formKeyValidator
     * @param CustomerExtractor $customerExtractor
     */

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication() {

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return ObjectManager::getInstance()->get(
                            \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated
     */
    private function getEmailNotification() {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(
                            EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute() {
        //print_r($_POST); exit();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        echo "here<br/>";
        var_dump($_POST);

        if (isset($_POST['remove-artwork-id'])) {
            /* $currentCustomerDataObject = $this->getCustomerDataObject($this->session->getCustomerId());
              $customerCandidateDataObject = $this->populateNewCustomerDataObject(
              $this->_request,
              $currentCustomerDataObject
              ); */
            $customerSession = $objectManager->create('Magento\Customer\Model\Session');
            if ($customerSession->isLoggedIn()) {
                $cid = $customerSession->getCustomer()->getId();
            }

            //$cid=$this->session->getCustomerId();

            try {

                $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');

                $connection = $this->_resources->getConnection();

                $repository = $objectManager->get("Magento\Catalog\Model\ProductRepository");

                $registery = $objectManager->get("Magento\Framework\Registry");
                $registery->register('isSecureArea', true);
//                $registery
                //Get the table data  
                $themeTable = $this->_resources->getTableName('art_table');
                $themeTable1 = $this->_resources->getTableName('art_enable_prouct');

                //$art_image=$_POST['art_work'];
                //$path = $art_image;
                //$file = basename($path);         // $file is set to "index.php"
                //echo $art_image;
                //exit();
                //define('DIRECTORY', '$media_url."artwork"');
                //$content = file_get_contents($art_image);
                //echo $content;
                //exit();


                /* $ch = curl_init($art_image);
                  $fp = fopen($media_url."artwork", 'wb');
                  curl_setopt($ch, CURLOPT_FILE, $fp);
                  curl_setopt($ch, CURLOPT_HEADER, 0);
                  curl_exec($ch);
                  curl_close($ch);
                  fclose($fp); */
                //file_put_contents(DIRECTORY . '/image.jpg', $content);
                //$art_image=$_POST['art_work'];
                //$path = $art_image;
                //$file = basename($path);
                //$art_attr=$_POST['art_attr'];
                $artwork_id = $_POST['remove-artwork-id'];
                $result1 = $connection->fetchAll("SELECT * FROM art_table WHERE art_id =" . $artwork_id);

                foreach ($result1 as $key) {
                    $artwork = $key;
                }
                if ($artwork['art_user'] != $cid) {
                    echo "not the owner of this art";
//                    return $resultRedirect->setPath('artwork/account/shop');
                }
                try {
                    $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
                    $productIds = $connection->fetchAll("SELECT * FROM art_enable_prouct WHERE art_id =" . $artwork_id);
//echo $av;
                } catch (Exception $e) {
//    echo $e->getMessage();
                }
                foreach ($productIds as $productId) {
                    $product_id = $productId['art_product'];
//                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);

                    $product = $repository->getById($product_id);
                    if ($product->getTypeId() == "configurable") {
                        $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                         foreach ($childProducts as $child) {
                             $childProduct = $repository->getById($child->getId());
                             $repository->delete($childProduct);
                         }
                    }
                    $repository->delete($product);

//                    $product->delete();
                }

                $sql1 = "DELETE FROM " . $themeTable1 . " WHERE art_id=$artwork_id";
                $connection->query($sql1);

                $sql1 = "DELETE FROM " . $themeTable . " WHERE art_id=$artwork_id";
                $connection->query($sql1);

                echo "artwork deleted.";
                $this->messageManager->addSuccess(__('Your work has been deleted.'));
                die();
                return $resultRedirect->setPath('artwork/account/shop');
            } catch (InvalidEmailOrPasswordException $e) {
                echo $e->getMessage();
                $this->messageManager->addError($e->getMessage());
            } catch (UserLockedException $e) {
                echo $e->getMessage();
                $message = __(
                        'The account is locked. Please wait and try again or contact %1.', $this->getScopeConfig()->getValue('contact/email/recipient_email')
                );
                $this->session->logout();
                $this->session->start();
                $this->messageManager->addError($message);
                return $resultRedirect->setPath('customer/account/login');
            } catch (InputException $e) {
                echo $e->getMessage();
                $this->messageManager->addError($e->getMessage());
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addError($error->getMessage());
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                echo $e->getMessage();
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                echo $e->getMessage();
                //$this->messageManager->addException($e, __('We can\'t save the customer.'));
            }

            //$this->session->setCustomerFormData($this->getRequest()->getPostValue());
        }
        die();
        return $resultRedirect->setPath('artwork/account/shop');
    }

    public function processMediaGalleryEntry($filePath, $sku, $filename, $mediaGalleryEntryFactory, $imageContentFactory, $mediaGalleryManagement) {
        $entry = $mediaGalleryEntryFactory->create();

        $entry->setFile($filePath);
        $entry->setMediaType('image');
        $entry->setDisabled(false);
        $entry->setTypes(['thumbnail', 'image', 'small_image']);

        $imageContent = $imageContentFactory->create();
        $imageContent
                ->setType(mime_content_type($filePath))
                ->setName($filename . ".png")
                ->setBase64EncodedData(base64_encode(file_get_contents($filePath)));

        $entry->setContent($imageContent);

        $mediaGalleryManagement->create($sku, $entry);
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     */
    private function getScopeConfig() {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return ObjectManager::getInstance()->get(
                            \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function getCustomerDataObject($customerId) {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Create Data Transfer Object of customer candidate
     *
     * @param \Magento\Framework\App\RequestInterface $inputData
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function populateNewCustomerDataObject(
    \Magento\Framework\App\RequestInterface $inputData, \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $customerDto = $this->customerExtractor->extract(
                self::FORM_DATA_EXTRACTOR_CODE, $inputData, $attributeValues
        );
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }
        if (!$inputData->getParam('change_email')) {
            $customerDto->setEmail($currentCustomerData->getEmail());
        }

        return $customerDto;
    }

    /**
     * Get Customer Mapper instance
     *
     * @return Mapper
     *
     * @deprecated
     */
    private function getCustomerMapper() {
        if ($this->customerMapper === null) {
            $this->customerMapper = ObjectManager::getInstance()->get('Magento\Customer\Model\Customer\Mapper');
        }
        return $this->customerMapper;
    }

}
