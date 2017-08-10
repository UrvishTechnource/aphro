<?php
namespace Inchoo\MenuItem\Model;
class Artist extends \Magento\Framework\Model\AbstractModel
{
  
    protected $_customerRepositoryInterface;
  	public function __construct(
	        \Magento\Framework\Model\Context $context,
	        \Magento\Framework\Registry $registry,
	        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
	        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
            \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
	        array $data = []
	) 
	{
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
	    parent::__construct($context, $registry, $resource, $resourceCollection, $customerRepositoryInterface, $data);  
	}
    
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Inchoo\MenuItem\Model\ResourceModel\Artist');
    }
    
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $customerID = $item['art_user'];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                if($customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($customerID))
                {
                    $item['user_name']=$customerObj->getData("firstname");
                }
//                $dataSource['data']['items']=array("first_name"=>"aaa");
            }
        }
      return $dataSource;
    }  
}
?>