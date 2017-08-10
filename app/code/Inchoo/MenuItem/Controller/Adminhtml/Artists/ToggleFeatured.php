<?php

namespace Inchoo\MenuItem\Controller\Adminhtml\Artists;

use Magento\Framework\UrlInterface;
use Magento\Framework\Controller\ResultFactory;

class ToggleFeatured extends \Magento\Backend\App\Action {

    protected $resultPageFactory;    

    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {        
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute() {
        $customerId = $this->getRequest()->getParam("id");
        $newstatus = $this->getRequest()->getParam("newstatus");
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resourceManager = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resourceManager->getConnection();
        $artistRow = $connection->fetchAll("SELECT * FROM artist_list WHERE artist_id = " . $customerId);
        if (count($artistRow)) {
            $statusUpdateQuery = "UPDATE artist_list SET featured_status = " . $newstatus . " WHERE artist_id=" . $customerId;
        } else {
            $statusUpdateQuery = "INSERT INTO artist_list(artist_id,featured_status) VALUES($customerId,$newstatus)";
        }
        $connection->query($statusUpdateQuery);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->urlBuilder->getUrl(
                        'menuitem/artists/index'
        ));
        return $resultRedirect;
    }

}

?>