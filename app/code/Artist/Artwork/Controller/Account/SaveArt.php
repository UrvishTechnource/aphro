<?php

/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Artist\Artwork\Controller\Account;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveArt extends \Magento\Customer\Controller\Account\EditPost {

    /**
     * Form code for data extractor
     */
    const FORM_DATA_EXTRACTOR_CODE = 'customer_account_edit';

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /** @var EmailNotificationInterface */
    private $emailNotification;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var Mapper
     */
    private $customerMapper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param Validator $formKeyValidator
     * @param CustomerExtractor $customerExtractor
     */

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication() {

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return ObjectManager::getInstance()->get(
                            \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated
     */
    private function getEmailNotification() {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(
                            EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute() {
        //print_r($_POST); exit();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (isset($_POST['add_art'])) {
            /* $currentCustomerDataObject = $this->getCustomerDataObject($this->session->getCustomerId());
              $customerCandidateDataObject = $this->populateNewCustomerDataObject(
              $this->_request,
              $currentCustomerDataObject
              ); */
            $customerSession = $objectManager->create('Magento\Customer\Model\Session');
            if ($customerSession->isLoggedIn()) {
                $cid = $customerSession->getCustomer()->getId();
            }

            //$cid=$this->session->getCustomerId();

            try {


                $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');

                $connection = $this->_resources->getConnection();

                //Get the table data  
                $themeTable = $this->_resources->getTableName('art_table');
                $themeTable1 = $this->_resources->getTableName('art_enable_prouct');

                //$art_image=$_POST['art_work'];
                //$path = $art_image;
                //$file = basename($path);         // $file is set to "index.php"
                //echo $art_image;
                //exit();
                //define('DIRECTORY', '$media_url."artwork"');
                //$content = file_get_contents($art_image);
                //echo $content;
                //exit();


                /* $ch = curl_init($art_image);
                  $fp = fopen($media_url."artwork", 'wb');
                  curl_setopt($ch, CURLOPT_FILE, $fp);
                  curl_setopt($ch, CURLOPT_HEADER, 0);
                  curl_exec($ch);
                  curl_close($ch);
                  fclose($fp); */
                //file_put_contents(DIRECTORY . '/image.jpg', $content);
                //$art_image=$_POST['art_work'];
                //$path = $art_image;
                //$file = basename($path);
                //$art_attr=$_POST['art_attr'];
                $art_id = $_POST['art_work'];
                $art_title = $_POST['title'];
                $art_tag = $_POST['tags-list'];
                $art_cat = $_POST['cat'];
                $art_who = $_POST['who'];
                $artwork_media_categories = $_POST['art_media_categories'];
                $implodedMediaCategories = implode(",", $artwork_media_categories);
                $art_mature = $_POST['mature'];
                $art_producs = $_POST['art_enb'];
                $apr = json_decode($art_producs, true);
//                echo "<pre>";
//                var_dump($apr);
//                //$sql = "INSERT INTO " . $themeTable . "(art_user,art_title,art_tag,art_cat,art_who,art_mature) VALUES ($cid,'$art_title', '$art_tag',$art_cat,$art_who,$art_mature)";
//                $sql = "UPDATE " . $themeTable . " SET art_title = '$art_title', art_tag = '$art_tag', art_cat = $art_cat,art_who=$art_who,art_mature=$art_mature WHERE art_id = " . $art_id;
//                //echo $sql; exit();
////              $sql = "INSERT INTO " . $themeTable . "(art_user,art_attr,art_image,art_title,art_tag,art_cat,art_who,art_mature) VALUES ($cid, $art_attr, '$art_image', '$art_title','$art_title', '$art_tag',$art_cat,$art_who,$art_mature)";
//                $connection->query($sql);
//echo "<pre>";
                if ($_POST['art_enb'] != '' || $_POST['art_enb'] == NULL) {
                    $fileSystem = $objectManager->get('Magento\Framework\Filesystem');
                    $basePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product');

                    $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                    $defautProduct = -1;
                    $matureContentAttributeValue = 43;
                    if ($art_mature) {
                        $matureContentAttributeValue = 42;
                    }
                    $productArtworkVisibilityAttributeValue = 45;
                    if ($art_who) {
                        $productArtworkVisibilityAttributeValue = 44;
                    }
                    foreach ($apr as $ap => $productObj) {
                        $configurable = ($productObj['type'] == 'configurable') ? true : false;
                        $enabledProduct = false;
                        $categoryLinkManagement = $objectManager->create('\Magento\Catalog\Api\CategoryLinkManagementInterface');

//                        if (!$configurable)
//                            continue;
                        $product_id = $ap;
                        $product = $objectManager->create('Magento\Catalog\Model\Product');
                        $product1 = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
                        //echo $product1->getShortDescription();die;
                        //Create new product
                        //              $product = $objectManager->create('\Magento\Catalog\Model\Product');                        
                        if ($product1->getTypeId() == 'configurable') {
                            $productAttributeOptions = $product1->getTypeInstance(true)->getConfigurableAttributesAsArray($product1);
                            $configurableProductStatus = 2;
                            $baseProductName = $art_title . " : " . $product1->getName();
                            $product->setAttributeSetId($product1->getAttributeSetId()); // #4 is for default
                            $product->setTypeId("configurable");
                            $product->setName($baseProductName); //name
//                        $product->setName($art_title. ""); //name                        
                            $product->setDescription($product1->getDescription()); //desc
                            $product->setShortDescription($product1->getShortDescription()); //short desc
                            $product->setSku($baseProductName);
//                            $product->setStatus((isset($productObj['options']))?$productObj['options']:0);
//                            $product->setStatus(1);
                            $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH); //4
                            $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
                            $product->setWebsiteIds(array($storeManager->getWebsite()->getId()));
                            $handle = str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 ]/', ' ', strtolower($baseProductName)));

                            $img = $productObj['images'][0];
                            $img = str_replace('data:image/png;base64,', '', $img);

                            $img = str_replace(' ', '+', $img);
                            $data = base64_decode($img);

                            $final_path = $basePath . $handle . ".png";
                            $baseProductImageName = $handle . ".png";
                            $baseProductPath = $final_path;
                            file_put_contents($final_path, $data);

                            $product->setData("art_product_type", "customized");
//                            $product->setMediaGallery(
//                                    [
//                                        'images' => [],
//                                        'values' => []
//                                    ]
//                            )->addImageToMediaGallery($final_path, array('image', 'small_image', 'thumbnail'), false, false);

                            $product->setPrice($productObj['new-price']); // # Set some price

                            $product->setTaxClassId($product1->getTaxClassId()); // # default tax class
                            $parentQuantity = $StockState->getStockQty($product1->getId(), $product1->getStore()->getWebsiteId());

                            $product->setData("product_visibility", $productArtworkVisibilityAttributeValue);
                            $product->setData("mature_content", $matureContentAttributeValue);

                            if ($parentQuantity)
                                $item = ['qty' => 50000000]; // For example
                            else {
                                $item = ['qty' => 0]; // For example
                            }
//                            var_dump($product1->getData("quantity_and_stock_status"));
//                            $product->setData("quantity_and_stock_status", $product1->getData("quantity_and_stock_status"));
                            $product->setData("quantity", 50000000);
                            $product->setData("quantity_attribute", 50000000);
                            $product->setStockData(['qty' => 50000000, 'is_in_stock' => 1]);
//                            $product->setStockData(['qty' => $item['qty'], 'is_in_stock' => 1]);
                            $product->setData("crtwork_category", $implodedMediaCategories);
//echo $quantity."<br/>";                        
                            //Default Magento attribute
                            $product->setCreatedAt(strtotime('now'));
//                        echo  $product1->getPrice()."<br/>";
//                            $product->save();
                            $childProducts = $product1->getTypeInstance()->getUsedProducts($product1);
//                                        echo "<pre>";
//                                        var_dump($productAttributeOptions);
                            $attributes = array();
                            $i = 0;
                            $attributeDataArray = array();
                            foreach ($productAttributeOptions as $attr) {
                                $attributes[$i] = intval($attr['attribute_id']);
                                $key = $attr['attribute_code'];
                                $attributeDataArray[$key] = array();
                                foreach ($attr['values'] as $attrValue) {
                                    $attributeDataArray[$key][$attrValue['value_index']] = array();
                                    $attributeDataArray[$key][$attrValue['value_index']]['label'] = $attrValue['label'];
                                    $attributeDataArray[$key][$attrValue['value_index']]['attribute_id'] = intval($attr['attribute_id']);
                                }
                                $i++;
                            }
//                            var_dump($productAttributeOptions);
//                            die();
                            $childProductIds = array();
                            $childProductsData = array();
                            $childProductsAttributeData = array();
                            $productRepository = $objectManager->create("Magento\Catalog\Api\ProductRepositoryInterface");
                            $optionsFactory = $objectManager->create("Magento\ConfigurableProduct\Helper\Product\Options\Factory");
                            $mediaGalleryEntryFactory = $objectManager->create("Magento\Catalog\Model\Product\Gallery\EntryFactory");
                            $mediaGalleryManagement = $objectManager->create("Magento\Catalog\Model\Product\Gallery\GalleryManagement");
                            $imageContentFactory = $objectManager->create("Magento\Framework\Api\ImageContentFactory");
                            if (count($childProducts)) {
                                foreach ($childProducts as $child) {
                                    $childProduct = $objectManager->create('Magento\Catalog\Model\Product');
                                    $colour = $child->getData("colour");
                                    $size = $child->getData("size");

                                    $childProductTitle = $art_title . " : " . $child->getName();
                                    $key = "";
                                    if ($size && $colour) {
                                        $key = $size . "~" . $colour;
                                        $childProduct->setData('colour', $colour);
                                        $childProduct->setData('size', $size);
                                    } else if ($size) {
                                        $key = $size;
                                        $childProduct->setData('size', $size);
                                    } else {
                                        $key = $colour;
                                        $childProduct->setData('colour', $colour);
                                    }

                                    $childProduct->setData("art_product_type", "customized");
                                    $childProduct->setAttributeSetId($child->getAttributeSetId()); // #4 is for default
                                    $childProduct->setTypeId("virtual");
                                    $childProduct->setName($childProductTitle); //name
//                        $childProduct->setName($art_title. ""); //name                        
                                    $childProduct->setDescription($child->getDescription()); //desc
                                    $childProduct->setShortDescription($child->getShortDescription()); //short desc
                                    $childProduct->setSku($childProductTitle);
                                    $childProduct->setStatus((isset($productObj['options'][$key]) && $productObj['options'][$key]) ? $productObj['options'][$key] : 2);
                                    if ($productObj['options'][$key]) {
                                        $enabledProduct = true;
                                    }
//                                    $childProduct->setStatus(1);
                                    $childProduct->setVisibility(1); //4
                                    $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
                                    $childProduct->setWebsiteIds(array($storeManager->getWebsite()->getId()));
                                    $handle = str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 ]/', ' ', strtolower($childProductTitle)));

                                    $img = $productObj['images'][$colour];
                                    $img = str_replace('data:image/png;base64,', '', $img);

                                    $img = str_replace(' ', '+', $img);
                                    $data = base64_decode($img);

                                    $final_path = $basePath . $handle . ".png";
                                    file_put_contents($final_path, $data);

                                    $childProduct->addImageToMediaGallery($final_path, array('image', 'small_image', 'thumbnail'), false, false);

                                    $childProduct->setPrice($productObj['new-price']); // # Set some price

                                    $childProduct->setTaxClassId($child->getTaxClassId()); // # default tax class
                                    $quantity = $StockState->getStockQty($child->getId(), $child->getStore()->getWebsiteId());

                                    $item = ['qty' => 50000000]; // For example
                                    $childProduct->setStockData(['qty' => $item['qty'], 'is_in_stock' => $item['qty'] > 0]);
//echo $quantity."<br/>";                        
                                    //Default Magento attribute
                                    $childProduct->setCreatedAt(strtotime('now'));


                                    $childProduct->setData("yourcustomattribute_id", $cid);
                                    $childProduct->setWeight($child->getWeight());
//                        echo  $product1->getPrice()."<br/>";
//                                    $childProduct->setData("product_visibility", $productArtworkVisibilityAttributeValue);
//                                    $childProduct->setData("mature_content", $matureContentAttributeValue);

                                    $childProduct->save();
                                    $childProductIds[] = intval($childProduct->getId());
                                    $childProductsData[$childProduct->getId()] = array();

                                    $stockItem = $objectManager->create('Magento\CatalogInventory\Model\Stock\Item');
                                    $stockItem->load($childProduct->getId(), 'product_id');
                                    if (!$stockItem->getProductId()) {
                                        $stockItem->setProductId($childProduct->getId());
                                    }
                                    $stockItem->setUseConfigManageStock(1);
                                    $stockItem->setQty(50000000);
                                    $stockItem->setIsQtyDecimal(0);
                                    $stockItem->setIsInStock(1);
                                    $stockItem->save();

                                    $childProduct->setData("product_artwork_id", $art_id);
                                    $childProduct->setData("artwork_product_parent", $child->getId());
                                    $childProduct->save();

                                    $tempIndex = 0;
                                    if ($colour) {
                                        $childProductsData[$childProduct->getId()][$tempIndex] = array(
                                            'label' => $attributeDataArray['colour'][$colour]['label'],
                                            'attribute_id' => $attributeDataArray['colour'][$colour]['attribute_id'],
                                            'value_index' => intval($colour),
//                                            'is_percent' => 0,
//                                            'pricing_value' => $productObj['new-price']
                                        );
                                        $tempIndex++;
                                        $childProductsAttributeData[0]['values'][] = array(
                                            'label' => $attributeDataArray['colour'][$colour]['label'],
                                            'attribute_id' => $attributeDataArray['colour'][$colour]['attribute_id'],
                                            'value_index' => intval($colour),
//                                            'is_percent' => 0,
//                                            'pricing_value' => $productObj['new-price']
                                        );
                                    }
                                    if ($size) {
                                        $childProductsData[$childProduct->getId()][$tempIndex] = array(
                                            'label' => $attributeDataArray['size'][$size]['label'],
                                            'attribute_id' => $attributeDataArray['size'][$size]['attribute_id'],
                                            'value_index' => intval($size),
//                                            'is_percent' => 0,
//                                            'pricing_value' => $productObj['new-price']
                                        );
                                        $tempIndex++;
                                        $childProductsAttributeData[1]['values'][] = array(
                                            'label' => $attributeDataArray['size'][$size]['label'],
                                            'attribute_id' => $attributeDataArray['size'][$size]['attribute_id'],
                                            'value_index' => intval($size),
//                                            'is_percent' => 0,
//                                            'pricing_value' => $productObj['new-price']
                                        );
                                    }
                                    echo $childProductTitle . " created <br/>";
                                }
                            }
                            if (isset($childProductsAttributeData[0])) {
                                $childProductsAttributeData[0]['position'] = 0;
                                $childProductsAttributeData[0]['code'] = 'colour';
                                $childProductsAttributeData[0]['label'] = 'Colour';
                                $childProductsAttributeData[0]['attribute_id'] = $childProductsAttributeData[0]['values'][0]['attribute_id'];
                            }
                            if (isset($childProductsAttributeData[1])) {
                                $childProductsAttributeData[1]['position'] = 1;
                                $childProductsAttributeData[1]['code'] = "size";
                                $childProductsAttributeData[1]['label'] = "Size";
                                $childProductsAttributeData[1]['attribute_id'] = $childProductsAttributeData[1]['values'][0]['attribute_id'];
                            }
//                            var_dump($childProductsAttributeData);
//                            $product->setTypeId("configurable"); // Setting Product Type As Configurable
//                            $productId = $product->getId();
//                            $product->setCanSaveConfigurableAttributes(true);
//                            $product->save();
//                            
//                            $product->setAffectConfigurableProductAttributes(4);
//                            $product->save();
////                            $product->getTypeInstance()->setUsedProductAttributeIds($attributes,$product);
//                            echo "here<br/>";
//                            $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->setUsedProductAttributeIds($attributes, $product);
//                            $product->setConfigurableProductsData($childProductsData);
//                            $product->save();
//                            echo "Then<br/>";
//                            $product->setConfigurableAttributesData($childProductsAttributeData);
//                            $product->setAssociatedProductIds($childProductIds); // Setting Associated Products                   
//                            $product->save();
//                            echo "Now<br/>";
                            $configurableOptions = $optionsFactory->create($childProductsAttributeData);
//                            $configurableOptions = [];
//                            foreach ($childProductsAttributeData as $itemData) {
//                                $configurableOptions[] = $objectManager->create('\Magento\ConfigurableProduct\Api\Data\OptionInterface', $itemData);
//                            }

                            $extensionConfigurableAttributes = $product->getExtensionAttributes();
                            $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
                            $extensionConfigurableAttributes->setConfigurableProductLinks($childProductIds);
                            $product->setExtensionAttributes($extensionConfigurableAttributes);
//                            $product->save();                            
                            $product = $productRepository->save($product);
//                            $this->processMediaGalleryEntry($baseProductPath, $product->getData("sku"), $baseProductImageName, $mediaGalleryEntryFactory, $imageContentFactory, $mediaGalleryManagement);
//                            $product = $productRepository->get($product->getData("sku"));
                            $product->addImageToMediaGallery($baseProductPath, array('image', 'small_image', 'thumbnail'), false, false);
                            $product->save();
//                            $productRepository->save($product);
//                            $attributeModel = $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute');
//                            $position = 0;
//                            $associatedProductIds = $childProductIds; //Product Ids Of Associated Products
//                            foreach ($attributes as $attributeId) {
//                                $data = array('attribute_id' => $attributeId, 'product_id' => $product->getId(), 'position' => $position);
//                                $position++;
//                                $attributeModel->setData($data)->save();
//                            }
//                            $product->setTypeId("configurable"); // Setting Product Type As Configurable
//                            $product->setAffectConfigurableProductAttributes(4);
//                            $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->setUsedProductAttributeIds($attributes, $product);
//                            $product->setNewVariationsAttributeSetId(4); // Setting Attribute Set Id
//                            $product->setAssociatedProductIds($associatedProductIds); // Setting Associated Products
//                            $product->setCanSaveConfigurableAttributes(true);
//                            $product->save();
//                            $product->setNewVariationsAttributeSetId(4); // Setting Attribute Set Id
//                            echo "<pre>";
//                            var_dump($product->getTypeInstance()->getConfigurableAttributesAsArray($product));
//                                                        echo "<pre>";
//                            var_dump($productAttributeOptions);
                        } else {
                            $productName = $art_title . " : " . $product1->getName();
                            $product->setData("art_product_type", "customized");
                            $product->setAttributeSetId($product1->getAttributeSetId()); // #4 is for default
                            $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                            $product->setName($productName); //name
//                        $product->setName($art_title. ""); //name                        
                            $product->setDescription($product1->getDescription()); //desc
                            $product->setShortDescription($product1->getShortDescription()); //short desc
                            $product->setSku($productName);
                            $product->setStatus((isset($productObj['options']) && $productObj['options']) ? $productObj['options'] : 2);
                            if ($productObj['options']) {
                                $enabledProduct = true;
                            }
//                            $product->setStatus(1);
                            $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH); //4
                            $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
                            $product->setWebsiteIds(array($storeManager->getWebsite()->getId()));
                            $handle = str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 ]/', ' ', strtolower($productName)));

                            $img = $productObj['images'];
                            $img = str_replace('data:image/png;base64,', '', $img);

                            $img = str_replace(' ', '+', $img);
                            $data = base64_decode($img);

                            $final_path = $basePath . $handle . ".png";
                            file_put_contents($final_path, $data);

                            $product->addImageToMediaGallery($final_path, array('image', 'small_image', 'thumbnail'), false, false);

                            $product->setPrice($productObj['new-price']); // # Set some price

                            $product->setTaxClassId($product1->getTaxClassId()); // # default tax class
                            $quantity = $StockState->getStockQty($product1->getId(), $product1->getStore()->getWebsiteId());

                            $item = ['qty' => 50000000]; // For example
                            $product->setQuantityAndStockStatus(['qty' => 50000000, 'is_in_stock' => 1]);
                            $product->setStockData(['qty' => 50000000, 'is_in_stock' => 1]);

                            $product->setData("product_visibility", $productArtworkVisibilityAttributeValue);
                            $product->setData("mature_content", $matureContentAttributeValue);

                            $product->setData("crtwork_category", $implodedMediaCategories);
//echo $quantity."<br/>";                        
                            //Default Magento attribute
                            $product->setCreatedAt(strtotime('now'));

                            $product->save();
                        }
                        $product->setData("yourcustomattribute_id", $cid);
                        $product->setData("product_artwork_id", $art_id);
                        $product->setData("artwork_product_parent", $product1->getId());
                        $product->save();

                        $stockItem = $objectManager->create('Magento\CatalogInventory\Model\Stock\Item');
                        $stockItem->load($product->getId(), 'product_id');
                        if (!$stockItem->getProductId()) {
                            $stockItem->setProductId($product->getId());
                        }
                        $stockItem->setUseConfigManageStock(1);
                        $stockItem->setQty(50000000);
                        $stockItem->setIsQtyDecimal(0);
                        $stockItem->setIsInStock(1);
                        $stockItem->save();

                        $categories = $product1->getCategoryIds();
                        $category = 0;
                        if (count($categories)) {
                            $category = $categories[0];
                            $product->setCategoryIds($categories);
                            $product->save();
                        }

//                        if (count($categories)) {
//                            $categoryId = $categories[0];
//                             echo $categoryId. "<br/>";
//
//                            $categoryLinkManagement->assignProductToCategories(
//                                    $product->getSku(), [intval($categoryId)]
//                            );
//                        }                        
                        $sql1 = "INSERT INTO " . $themeTable1 . "(artist_id,art_id,art_product,art_product_category,art_status) VALUES ($cid,$art_id," . $product->getId() . ",$category,0)";
                        if ($enabledProduct) {
                            $sql1 = "INSERT INTO " . $themeTable1 . "(artist_id,art_id,art_product,art_product_category,art_status) VALUES ($cid,$art_id," . $product->getId() . ",$category,1)";
                            $product->setStatus(1);
                        } else {
                            $product->setStatus(2);
                        }
                        $product->save();
                        echo $product->getName() . " created<br/>";
                        $connection->query($sql1);
                        if ($product1->getId() == $art_cat) {
                            $defautProduct = $product->getId();
                            $product->setData("default_view", 1);
                        } else {
                            $product->setData("default_view", 0);
                        }
                        $product->save();
//                        echo "<pre>";
//                        var_dump($product->toArray());
//                        echo "</pre>";
//                        echo "Product Created";
                    }
                }
                //$sql = "INSERT INTO " . $themeTable . "(art_user,art_title,art_tag,art_cat,art_who,art_mature) VALUES ($cid,'$art_title', '$art_tag',$art_cat,$art_who,$art_mature)";
                $sql = "UPDATE " . $themeTable . " SET art_title = '$art_title', art_tag = '$art_tag', art_cat = $defautProduct,art_who=$art_who,art_mature=$art_mature WHERE art_id = " . $art_id;
                echo "completed";


                if ($_POST['artwork-remove-id']) {
                    $repository = $objectManager->get("Magento\Catalog\Model\ProductRepository");

                    $registery = $objectManager->get("Magento\Framework\Registry");
                    $registery->register('isSecureArea', true);

                    $artwork_id = $_POST['artwork-remove-id'];
                    $result1 = $connection->fetchAll("SELECT * FROM art_table WHERE art_id =" . $artwork_id);

                    foreach ($result1 as $key) {
                        $artwork = $key;
                    }
                    if ($artwork['art_user'] != $cid) {
//                        echo "not the owner of this art";
//                    return $resultRedirect->setPath('artwork/account/shop');
                    }
                    try {
                        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
                        $productIds = $connection->fetchAll("SELECT * FROM art_enable_prouct WHERE art_id =" . $artwork_id);
//echo $av;
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                    foreach ($productIds as $productId) {
                        $product_id = $productId['art_product'];
//                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);

                        $product = $repository->getById($product_id);
                        if ($product->getTypeId() == "configurable") {
                            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                            foreach ($childProducts as $child) {
                                $childProduct = $repository->getById($child->getId());
                                $repository->delete($childProduct);
                            }
                        }
                        $repository->delete($product);

//                    $product->delete();
                    }

                    $sql1 = "DELETE FROM " . $themeTable1 . " WHERE art_id=$artwork_id";
                    $connection->query($sql1);

                    $sql1 = "DELETE FROM " . $themeTable . " WHERE art_id=$artwork_id";
                    $connection->query($sql1);
                }

                //echo $sql; exit();
//              $sql = "INSERT INTO " . $themeTable . "(art_user,art_attr,art_image,art_title,art_tag,art_cat,art_who,art_mature) VALUES ($cid, $art_attr, '$art_image', '$art_title','$art_title', '$art_tag',$art_cat,$art_who,$art_mature)";
                $connection->query($sql);
                $this->messageManager->addSuccess(__('Your work has been saved.'));
//                die();
                return $resultRedirect->setPath('artwork/account/shop');
            } catch (InvalidEmailOrPasswordException $e) {
                echo $e->getMessage();
                $this->messageManager->addError($e->getMessage());
            } catch (UserLockedException $e) {
                echo $e->getMessage();
                $message = __(
                        'The account is locked. Please wait and try again or contact %1.', $this->getScopeConfig()->getValue('contact/email/recipient_email')
                );
                $this->session->logout();
                $this->session->start();
                $this->messageManager->addError($message);
                return $resultRedirect->setPath('customer/account/login');
            } catch (InputException $e) {
                echo $e->getMessage();
                $this->messageManager->addError($e->getMessage());
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addError($error->getMessage());
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                echo $e->getMessage();
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                echo $e->getMessage();
                //$this->messageManager->addException($e, __('We can\'t save the customer.'));
            }

            //$this->session->setCustomerFormData($this->getRequest()->getPostValue());
        }
        die();
        return $resultRedirect->setPath('artwork/account/shop');
    }

    public function processMediaGalleryEntry($filePath, $sku, $filename, $mediaGalleryEntryFactory, $imageContentFactory, $mediaGalleryManagement) {
        $entry = $mediaGalleryEntryFactory->create();

        $entry->setFile($filePath);
        $entry->setMediaType('image');
        $entry->setDisabled(false);
        $entry->setTypes(['thumbnail', 'image', 'small_image']);

        $imageContent = $imageContentFactory->create();
        $imageContent
                ->setType(mime_content_type($filePath))
                ->setName($filename . ".png")
                ->setBase64EncodedData(base64_encode(file_get_contents($filePath)));

        $entry->setContent($imageContent);

        $mediaGalleryManagement->create($sku, $entry);
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     */
    private function getScopeConfig() {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return ObjectManager::getInstance()->get(
                            \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Get customer data object
     *
     * @param int $customerId
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function getCustomerDataObject($customerId) {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Create Data Transfer Object of customer candidate
     *
     * @param \Magento\Framework\App\RequestInterface $inputData
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function populateNewCustomerDataObject(
    \Magento\Framework\App\RequestInterface $inputData, \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $customerDto = $this->customerExtractor->extract(
                self::FORM_DATA_EXTRACTOR_CODE, $inputData, $attributeValues
        );
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }
        if (!$inputData->getParam('change_email')) {
            $customerDto->setEmail($currentCustomerData->getEmail());
        }

        return $customerDto;
    }

    /**
     * Get Customer Mapper instance
     *
     * @return Mapper
     *
     * @deprecated
     */
    private function getCustomerMapper() {
        if ($this->customerMapper === null) {
            $this->customerMapper = ObjectManager::getInstance()->get('Magento\Customer\Model\Customer\Mapper');
        }
        return $this->customerMapper;
    }

}
