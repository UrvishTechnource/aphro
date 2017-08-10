<?php

namespace Artist\Artwork\Block;

class Edit extends \Magento\Customer\Block\Form\Edit {

    public function getHelloWorldTxt() {
        return 'Hello world!';
    }

    public function getMediaAttributes() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $eavConfig = $objectManager->create("Magento\Eav\Model\Config");
        return $eavConfig->getAttribute('catalog_product', 'crtwork_category');
    }

}
