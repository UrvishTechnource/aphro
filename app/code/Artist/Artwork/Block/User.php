<?php
namespace Artist\Artwork\Block;
 
class User extends \Magento\Customer\Block\Form\Edit
{
    public function getHelloWorldTxt()
    {
        return 'Hello world!';
    }

    /*public function getAjaxUrl()
    {
    return $this->getUrl("artwork/account/user"); // Controller Url
    }*/
    public function getUserUrl($id)
    {
        return $this->getUrl('artwork/account/user', ['id' => $id]);
    }
}
