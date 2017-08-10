<?php
namespace Inchoo\MenuItem\Model\ResourceModel\Artist; 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct(){
        $this->_init(
            'Inchoo\MenuItem\Model\Artist','Inchoo\MenuItem\Model\ResourceModel\Artist'
        );
    }
}
