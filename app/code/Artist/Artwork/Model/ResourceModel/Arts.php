<?php
namespace Artist\Artwork\Model\ResourceModel;
class Arts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('art_table', 'art_id');
    }
}

?>