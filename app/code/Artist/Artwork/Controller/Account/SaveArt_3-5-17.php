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
class SaveArt extends \Magento\Customer\Controller\Account\EditPost
{
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
    
    


    private function getAuthentication()
    {

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
    private function getEmailNotification()
    {
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
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
      
        $resultRedirect = $this->resultRedirectFactory->create();
       
        if (isset($_POST['add_art'])) {
            $currentCustomerDataObject = $this->getCustomerDataObject($this->session->getCustomerId());
            $customerCandidateDataObject = $this->populateNewCustomerDataObject(
                $this->_request,
                $currentCustomerDataObject
            );
            
            $cid=$this->session->getCustomerId();
            
            try {
              
              
              $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
              
              $connection= $this->_resources->getConnection();
              
              //Get the table data  
              $themeTable = $this->_resources->getTableName('art_table');
              $themeTable1 = $this->_resources->getTableName('art_enable_prouct');
              
              $art_image=$_POST['art_work'];
              //$art_attr=$_POST['art_attr'];
              $art_title=$_POST['title'];
              $art_tag=$_POST['tag'];
              $art_cat=$_POST['cat'];
              $art_who=$_POST['who'];
              $art_mature=$_POST['mature'];
              $art_producs=$_POST['art_enb'];
              $apr=json_decode($art_producs[0]);
              
              
              $sql = "INSERT INTO " . $themeTable . "(art_user,art_image,art_title,art_tag,art_cat,art_who,art_mature) VALUES ($cid, '$art_image', '$art_title', '$art_tag',$art_cat,$art_who,$art_mature)";
              //echo $sql;die;
//              $sql = "INSERT INTO " . $themeTable . "(art_user,art_attr,art_image,art_title,art_tag,art_cat,art_who,art_mature) VALUES ($cid, $art_attr, '$art_image', '$art_title','$art_title', '$art_tag',$art_cat,$art_who,$art_mature)";
              $connection->query($sql);
              
              if($_POST['art_enb']!='' || $_POST['art_enb']==NULL){
                
              
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $product = $objectManager->create('Magento\Catalog\Model\Product');
                foreach ($apr as $ap) {
                  $sql1 = "INSERT INTO " . $themeTable1 . "(art_id ,art_product) VALUES ($cid,$ap)";
                  $connection->query($sql1);
                  $product_id=$ap;
                  $product1=$product->load($product_id);
                  //echo $product1->getShortDescription();die;
                  //Create new product
    //              $product = $objectManager->create('\Magento\Catalog\Model\Product');

                  //$product->setAttributeSetId(9);// #4 is for default
                  $product->setTypeId('simple');
                  $product->setName($product1->getName());//name
                  $product->setDescription($product1->getDescription());//desc
                  $product->setShortDescription($product1->getShortDescription());//short desc
                  $product->setSku(time());
                  $product->setStatus(1);
                  $product->setVisibility(4);//4

                  $product->setPrice($product1->getPrice());// # Set some price

                  $product->setTaxClassId($product1->getTaxClassId());// # default tax class

                  //Default Magento attribute
                  $product->setCreatedAt(strtotime('now'));
                  $product->save();
                  echo "Product Created";

                }
              }
              $this->messageManager->addSuccess(__('Your work has been saved.'));
              return $resultRedirect->setPath('artwork/account/usersart');
            } catch (InvalidEmailOrPasswordException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (UserLockedException $e) {
                $message = __(
                    'The account is locked. Please wait and try again or contact %1.',
                    $this->getScopeConfig()->getValue('contact/email/recipient_email')
                );
                $this->session->logout();
                $this->session->start();
                $this->messageManager->addError($message);
                return $resultRedirect->setPath('customer/account/login');
            } catch (InputException $e) {
                $this->messageManager->addError($e->getMessage());
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addError($error->getMessage());
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the customer.'));
            }

            $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        } 

        return $resultRedirect->setPath('artwork/account/usersart');
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     */
    private function getScopeConfig()
    {
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
    private function getCustomerDataObject($customerId)
    {
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
        \Magento\Framework\App\RequestInterface $inputData,
        \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $customerDto = $this->customerExtractor->extract(
            self::FORM_DATA_EXTRACTOR_CODE,
            $inputData,
            $attributeValues
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
    private function getCustomerMapper()
    {
        if ($this->customerMapper === null) {
            $this->customerMapper = ObjectManager::getInstance()->get('Magento\Customer\Model\Customer\Mapper');
        }
        return $this->customerMapper;
    }
}
