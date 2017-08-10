<?php
 
namespace Attrib\Custer\Model\Config\Source;
 

use Magento\Framework\DB\Ddl\Table;
 
/**
 * Custom Attribute Renderer
 *
 * @author      Webkul Core Team <support@webkul.com>
 */
class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
     protected $_customers;
    
    public function __construct(\Magento\Customer\Model\Customer $customers)
    {
        $this->_customers = $customers;
        //you can use this if you want to prepare options dynamically  
    }
    
    public function getCollection()
    {
        //Get customer collection
        return $this->_customers->getCollection();
    }
    
//    public function getAllOptions()
//    {
//        /* your Attribute options list*/
//        $this->_options=[ ['label'=>'Select Options', 'value'=>''],
//                          ['label'=>'Option1', 'value'=>'1'],
//                          ['label'=>'Option2', 'value'=>'2'],
//                          ['label'=>'Option3', 'value'=>'3']
//                         ];
//        return $this->_options;
//    }
    public function getAllOptions()
    {
        
        /* your Attribute options list*/
        $this->_options[]=['label'=>'Select Options', 'value'=>''];
        $collection = $this->getCollection();
        $id_array=array();
        $id_array1=array();
        foreach ($collection as $customer) {
            $customer_id = intval($customer->getID()); // Get Customer ID
            $artist_first_name = $customer->getData('firstname');
            $artist_last_name = $customer->getData('lastname');
            $artist_name=$artist_first_name ." ".$artist_last_name;
            if(!in_array($artist_first_name, $id_array) && !in_array($artist_last_name, $id_array1)){
                array_push($id_array, $artist_first_name);
                array_push($id_array1, $artist_last_name);
                $this->_options[]= ['label'=>$artist_name, 'value'=>$customer_id];
            }
            
        }
        return $this->_options;
    }
 
    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
 
    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Custom Attribute Options  ' . $attributeCode . ' column',
            ],
        ];
    }
}