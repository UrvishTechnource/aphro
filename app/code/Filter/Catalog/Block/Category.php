<?php
namespace Filter\Catalog\Block;

class Category extends \Magento\Framework\View\Element\Template
{
    public function getProductCollection($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $collection = $productCollection->create()->addAttributeToSelect('*')->addAttributeToFilter('entity_id',$id)->load();
        return $collection ;
    }
}
