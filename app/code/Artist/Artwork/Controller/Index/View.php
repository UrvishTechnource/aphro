<?php

namespace Artist\Artwork\Controller\Index;

//use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

//use Vendor\Module\Model\ResourceModel\Example\CollectionFactory;

class View extends \Magento\Framework\App\Action\Action {

//    protected $resultJsonFactory;

    //protected $resultPageFactory;

    public function __construct(
    Context $context, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        //$this->exampleFactory = $exampleFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {
        /* Put below your code */
        /* $id = $this->getRequest()->getParam('id', false);
          $responseData = $this->exampleFactory->create();

          $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
          $resultJson->setData($responseData);
          return $resultJson; */

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $url = $this->_redirect->getRefererUrl();
        $resultRedirect = $this->resultJsonFactory->create();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');

        $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection = $this->_resources->getConnection();
        $themeTable = $this->_resources->getTableName('artist_follow_followers');


        //$result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) {
            //$following_id = $this->getRequest()->getParam('artist', false);
            $following_id = $this->getRequest()->getParam('label');
            $current_cust_id = $this->getRequest()->getParam('var1');
            $data_attr = $this->getRequest()->getParam('var2');
            if ($customerSession->isLoggedIn()) {
                $cid = $customerSession->getCustomer()->getId();
            } else {
                echo "failed";
                die();
            }
            if ($data_attr == 0) {
                $sql = "INSERT INTO " . $themeTable . "(artist_follower_id,artist_following_id) VALUES ($cid,$following_id)";
            } else {
                $sql = "Delete FROM " . $themeTable . " WHERE artist_follower_id = " . $cid . " AND artist_following_id=" . $following_id;
            }
            $connection->query($sql);
            $test = '<h1>Hello World</h1>';
            echo "done";
            die();
            //return $result->setData($test);
            return $resultRedirect->setData($test);
        }
    }

}
