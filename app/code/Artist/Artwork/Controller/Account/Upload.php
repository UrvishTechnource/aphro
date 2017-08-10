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


class Upload extends \Magento\Customer\Controller\AbstractAccount
{
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
    
 
    public function execute()
    {
        //echo $_POST['ext'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileSystem = $objectManager->get('Magento\Framework\Filesystem');
        $media_url=$objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        
        $img = $_POST['img_data']; // Your data 'data:image/png;base64,AAAFBfj42Pj4';
        
        if($_POST['ext'] == 'jpg')
        {
            $img = str_replace('data:image/jpeg;base64,', '', $img);
        }
        elseif ($_POST['ext'] == 'jpeg') 
        {
            $img = str_replace('data:image/jpeg;base64,', '', $img);
        }
        elseif ($_POST['ext'] == 'gif') 
        {
            $img = str_replace('data:image/gif;base64,', '', $img);
        }
        elseif ($_POST['ext'] == 'png')
        {
            $img = str_replace('data:image/png;base64,', '', $img);
        }
        //$img = str_replace('data:image/'.$_POST['ext'].';base64,', '', $img);
        //echo $img;
        $img = str_replace(' ', '+', $img);
        //echo $img;
        $data = base64_decode($img);
        
        //print_r($data);  
        $no = rand(0, 99999999999);
        $path = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('artwork/');
        $final_path = $path.$no.".".$_POST['ext'];
        //echo $final_path;
        file_put_contents($final_path, $data);
        
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        if($customerSession->isLoggedIn()) 
        {
            $cid = $customerSession->getCustomer()->getId();
        }
        
        $newFilename = $no.".".$_POST['ext'];
               
        $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        
        $connection= $this->_resources->getConnection();
        //Get the table data  
        $themeTable = $this->_resources->getTableName('art_table');
                    
        //delete un usual artwork
        $sql_del = "DELETE FROM " . $themeTable . " WHERE art_title IS NULL AND art_tag IS NULL AND art_cat IS NULL AND art_user=".$cid;
        $connection->query($sql_del);
        
        $sql = "INSERT INTO " . $themeTable . "(art_user,art_image) VALUES ($cid, '$newFilename')";
        $connection->query($sql);
        //$this->messageManager->addSuccess(__('Your art has been uploaded.'));
        //$resultRedirect = $this->resultRedirectFactory->create();
        //return $resultRedirect->setPath('artwork/account/editart');
        //return('artwork/account/editart');
        echo "sucess";
    }
}
