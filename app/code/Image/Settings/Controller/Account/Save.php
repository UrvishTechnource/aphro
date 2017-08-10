<?php

/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Image\Settings\Controller\Account;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Customer\Controller\AbstractAccount {

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var Validator
     */
    protected $_formKeyValidator;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Mapper
     */
    private $_customerMapper;
    protected $_fileUploaderFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Validator $formKeyValidator
     * @param CustomerExtractor $customerExtractor
     */
    public function __construct(
    \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory, Context $context, Session $customerSession, FormKeyValidator $formKeyValidator, CustomerRepositoryInterface $customerRepository, CustomerInterfaceFactory $customerDataFactory, DataObjectHelper $dataObjectHelper, \Magento\Customer\Model\Customer\Mapper $customerMapper, PageFactory $resultPageFactory
    ) {
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_customerSession = $customerSession;
        $this->_customerRepository = $customerRepository;
        $this->_customerMapper = $customerMapper;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct(
                $context, $customerSession, $formKeyValidator, $resultPageFactory
        );
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession() {
        return $this->_customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request) {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $con = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $table = $con->getTableName('image_settings');
        try {
            $fileSystem = $objectManager->get('Magento\Framework\Filesystem');
            $media_url = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $customerSession = $objectManager->create('Magento\Customer\Model\Session');
            if ($customerSession->isLoggedIn()) {
                $cid = $customerSession->getCustomer()->getId();
            }
            $result1 = $con->fetchAll("SELECT artist_id FROM image_settings WHERE artist_id =" . $cid);
            $count = count($result1);

            /* if(count($result1)>=1)
              { echo "hello"; exit();
              } */
            /* foreach ($result1 as $key)
              { */
//$image = $key['watermark_image'];
//$id = $key['id'];
//}

            $attr_id = $_POST['attr_id'];            
            if ($_FILES['watermark_image']['name']) {
                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'watermark_image']);

                $uploader->setAllowedExtensions(['png']);

                $uploader->setAllowRenameFiles(false);

                $uploader->setFilesDispersion(false);

                $path = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('watermark/');
                $response = $uploader->save($path);

//$id = $_POST['value_id'];
                $watermark_image = $response['file'];
            }
            echo "here";
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            if ($this->getRequest()->isPost()) {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory
                                    ->create()
                                    ->setPath('*/*/index', ['_secure' => $this->getRequest()->isSecure()]);
                }
                $customerData = $this->getRequest()->getParams(); //get attributes value from form 
//print_r($customerData); exit();           
                $customerId = $this->_getSession()->getCustomerId();
// get customer saved data 
                $savedCustomerData = $this->_customerRepository->getById($customerId);
                $customer = $this->_customerDataFactory->create();
//merge saved customer data with new values
                $customerData = array_merge(
                        $this->_customerMapper->toFlatArray($savedCustomerData), $customerData
                );
                $customerData['id'] = $customerId;
                $this->_dataObjectHelper->populateWithArray(
                        $customer, $customerData, '\Magento\Customer\Api\Data\CustomerInterface'
                );
//save customer
                $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
                $connection = $this->_resources->getConnection();
//Get the table data                                  
                $themeTable = $this->_resources->getTableName('image_settings');
                if ($count == 1) {
                    if (isset($_POST['upload_watermark']) && $_POST['upload_watermark']) {
                        if ($_FILES['watermark_image']['name']) {
                            $sql = "UPDATE " . $themeTable . " SET artist_id = $cid, attr_id = $attr_id, watermark_image = '$watermark_image' WHERE artist_id = " . $cid;
                        }
                    } else {
                        $sql = "DELETE FROM " . $themeTable . " WHERE artist_id = " . $cid;
                    }
                } else if (isset($_POST['upload_watermark']) && $_POST['upload_watermark'] && $_FILES['watermark_image']['name']) {
                    $sql = "INSERT INTO " . $themeTable . "(artist_id,attr_id,watermark_image) VALUES ($cid,$attr_id, '$watermark_image')";
                }
//echo $sql; exit();
//}
                /* if($value_id =='')
                  {

                  } */
                $connection->query($sql);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            die();
        }

        $this->_customerRepository->save($customer);
        $this->messageManager->addSuccess(__('Changes has been saved successfully'));
        return $this->resultRedirectFactory->create()->setPath('settings/account/set', ['_secure' => $this->getRequest()->isSecure()]);
    }

}
