<?php

/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Artist\Artwork\Controller\Account;

use Magento\Framework\App\Action\Context;

//use Magento\Customer\Api\CustomerRepositoryInterface;
//use Magento\Framework\Api\DataObjectHelper;
//use Magento\Customer\Model\Session;
//use Magento\Framework\View\Result\PageFactory;
//use Magento\Framework\App\Action\Context;

class Artistfollowers extends \Magento\Framework\App\Action\Action {
    /** @var CustomerRepositoryInterface  */
    //protected $customerRepository;

    /** @var DataObjectHelper */
    //protected $dataObjectHelper;

    /**
     * @var Session
     */
    //protected $session;

    /**
     * @var PageFactory
     */
    //protected $resultPageFactory;
    protected $_resultPageFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    /* public function __construct(
      Context $context,
      //Session $customerSession,
      PageFactory $resultPageFactory,
      CustomerRepositoryInterface $customerRepository,
      DataObjectHelper $dataObjectHelper
      ) {
      //$this->session = $customerSession;
      $this->resultPageFactory = $resultPageFactory;
      $this->customerRepository = $customerRepository;
      $this->dataObjectHelper = $dataObjectHelper;
      parent::__construct($context);
      } */
    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory) {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Forgot customer account information page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    /* public function execute()
      { */

    /** @var \Magento\Framework\View\Result\Page $resultPage */
    /* $resultPage = $this->resultPageFactory->create();

      $block = $resultPage->getLayout()->getBlock('customer_edit');
      if ($block) {
      $block->setRefererUrl($this->_redirect->getRefererUrl());
      } */

    //$data = $this->session->getCustomerFormData(true);
    //$customerId = $this->session->getCustomerId();
    //$customerDataObject = $this->customerRepository->getById($customerId);
    /* if (!empty($data)) {
      $this->dataObjectHelper->populateWithArray(
      $customerDataObject,
      $data,
      '\Magento\Customer\Api\Data\CustomerInterface'
      );
      } */
    //$this->session->setCustomerData($customerDataObject);
    //$this->session->setChangePassword($this->getRequest()->getParam('changepass') == 1);

    /* $resultPage->getConfig()->getTitle()->set(__('Artist Profile'));
      $resultPage->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
      return $resultPage; */
    public function execute() {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Artist Followers'));
        $resultPage->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        return $resultPage;
    }

    //}
}
