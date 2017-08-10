<?php
namespace Inchoo\MenuItem\Model\ResourceModel;
class Artist extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('art_table', 'art_id');
    }
}

?>