<?php
namespace Artist\Artwork\Block;
 
class Product extends \Magento\Customer\Block\Form\Edit
{
    public function getHelloWorldTxt()
    {
        return 'Hello world!';
    }

    /*public function getAjaxUrl()
    {
    return $this->getUrl("artwork/account/user"); // Controller Url
    }*/
    public function getProductUrl($id)
    {
        return $this->getUrl('artwork/account/product', ['id' => $id]);
    }
}
