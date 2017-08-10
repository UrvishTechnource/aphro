<?php
namespace Artist\Artwork\Model;
class Arts extends \Magento\Framework\Model\AbstractModel
{
  
  
  	public function __construct(
	        \Magento\Framework\Model\Context $context,
	        \Magento\Framework\Registry $registry,
	        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
	        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
	        array $data = []
	) 
	{
	    parent::__construct($context, $registry, $resource, $resourceCollection, $data);
	}
    
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Artist\Artwork\Model\ResourceModel\Arts');
    }
}
?>