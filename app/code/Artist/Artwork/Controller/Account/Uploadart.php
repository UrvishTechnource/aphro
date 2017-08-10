<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;

class Uploadart{
  protected $_fileUploaderFactory;
    function __construct(\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,Action\Context $context) {
      $this->_fileUploaderFactory = $fileUploaderFactory;
      parent::__construct($context);
    }
    public function execute(){
        $uploader = $this->_fileUploaderFactory->create(['fileId' => 'add_work']);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('images/');
        $uploader->save($path);

    }
} 
