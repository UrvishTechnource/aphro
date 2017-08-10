<?php

namespace Purchase\History\Block;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;

/**
 * Sales order history block
 */
class Sales extends \Magento\Framework\View\Element\Template {

    /**
     * @var string
     */
    protected $_template = 'sales.phtml';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    protected $orders;

    /**
     * @var CollectionFactoryInterface
     */
    private $orderCollectionFactory;
    protected $_reviewFactory;
    protected $_productloader;
    protected $_resourceConnection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param array $data
     */
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory, \Magento\Customer\Model\Session $customerSession, \Magento\Sales\Model\Order\Config $orderConfig, \Magento\Review\Model\ReviewFactory $reviewFactory, \Magento\Catalog\Model\ProductFactory $_productloader, \Magento\Framework\App\ResourceConnection $resourceConnection, array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->_reviewFactory = $reviewFactory;
        $this->_productloader = $_productloader;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Sales'));
    }

    /**
     * @return CollectionFactoryInterface
     *
     * @deprecated
     */
    private function getOrderCollectionFactory() {
        if ($this->orderCollectionFactory === null) {
            $this->orderCollectionFactory = ObjectManager::getInstance()->get(CollectionFactoryInterface::class);
        }
        return $this->orderCollectionFactory;
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders() {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 1;

        $connection = $this->_resourceConnection->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $artists_enabled_products = $connection->fetchAll("SELECT `art_product` FROM art_enable_prouct WHERE art_status = 1 and artist_id =" . $customerId);

        foreach ($artists_enabled_products as $pro) {
            $my_artwork_product_ids[] = $pro['art_product'];
        }
        //$my_artwork_product_ids = array(1645);
        if (!$this->orders) {
            $this->orders = $this->getOrderCollectionFactory()->create($customerId)
                            ->addFieldToSelect('*')
                            ->join(['sales_order_item'], "main_table.entity_id = sales_order_item.order_id", ['product_id'])
                            //->group("main_table.order_id")
                            ->addFieldToFilter(
                                    'status', ['in' => array('complete')]
                            )
                            ->addFieldToFilter(
                                    'product_id', ['in' => $my_artwork_product_ids]
                            )
                            ->setOrder(
                                    'created_at', 'desc'
                            )->setPageSize($pageSize)->setCurPage($page);
        }
//        echo $this->orders->getSelect();
//        die();
        return $this->orders;
    }

    public function getRatingSummary($id) {
        $product = $this->_productloader->create()->load($id); // follow the link for this
        $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
        $ratingSummary = $product->getRatingSummary();

        return $ratingSummary;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if ($this->getOrders()) {
            $pager = $this->getLayout()->createBlock(
                            'Magento\Theme\Block\Html\Pager', 'purchase.history.order.pager'
                    )->setAvailableLimit(array(5 => 5, 10 => 10, 15 => 15))->setShowPerPage(true)->setCollection(
                    $this->getOrders()
            );
            $this->setChild('pager', $pager);
            $this->getOrders()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order) {
        return $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getTrackUrl($order) {
        return $this->getUrl('sales/order/track', ['order_id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getReorderUrl($order) {
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    /**
     * @return string
     */
    public function getBackUrl() {
        return $this->getUrl('customer/account/');
    }

}
