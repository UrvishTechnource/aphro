<?php

/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Artist\Artwork\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\Redirect;

class RemoveArtwork extends \Magento\Customer\Controller\AbstractAccount {

    /** @var CustomerRepositoryInterface  */
    protected $resultPageFactory;
    protected $_filesystem;
    protected $_fileUploaderFactory;
    protected $uploader;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
    \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
    //PageFactory $resultPageFactory,
            Action\Context $context
    ) {

        $this->_fileUploaderFactory = $fileUploaderFactory;
        //$this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Forgot customer account information page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
        //echo $_POST['ext'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        if ($customerSession->isLoggedIn()) {
            $cid = $customerSession->getCustomer()->getId();
        }

        $connection = $this->_resources->getConnection();
        //Get the table data  
        $themeTable = $this->_resources->getTableName('art_table');

        //delete un usual artwork
        $sql_del = "DELETE FROM " . $themeTable . " WHERE art_title IS NULL AND art_tag IS NULL AND art_cat IS NULL AND art_user=" . $cid;
        $connection->query($sql_del);
        //$this->messageManager->addSuccess(__('Your art has been uploaded.'));
        //$resultRedirect = $this->resultRedirectFactory->create();
        //return $resultRedirect->setPath('artwork/account/editart');
        //return('artwork/account/editart');
        echo "sucess";
    }

}
