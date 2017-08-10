<?php
namespace Artist\Artwork\Block;
 
class Example extends \Magento\Customer\Block\Form\Edit
{
    public function getHelloWorldTxt()
    {
        return 'Hello world!';
    }
    public function getAjaxUrl()
    {
    return $this->getUrl("artwork/index/view"); // Controller Url
    }
}
