<?php
namespace Artist\Artwork\Model\ResourceModel\Arts; 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct(){
        $this->_init(
            'Artist\Artwork\Model\Arts','Artist\Artwork\Model\ResourceModel\Arts'
        );
    }
}
