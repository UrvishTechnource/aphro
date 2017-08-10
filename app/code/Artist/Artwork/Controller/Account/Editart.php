<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Artist\Artwork\Controller\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Editart extends \Magento\Customer\Controller\AbstractAccount {

    /** @var CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var DataObjectHelper */
    protected $dataObjectHelper;
    protected $eavConfig;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
    Context $context, Session $customerSession, PageFactory $resultPageFactory, CustomerRepositoryInterface $customerRepository, DataObjectHelper $dataObjectHelper, \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->eavConfig = $eavConfig;
        parent::__construct($context);
    }

    /**
     * Forgot customer account information page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute() {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()->getBlock('customer_edit');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        $data = $this->session->getCustomerFormData(true);
        $customerId = $this->session->getCustomerId();
        $customerDataObject = $this->customerRepository->getById($customerId);
        if (!empty($data)) {
            $this->dataObjectHelper->populateWithArray(
                    $customerDataObject, $data, '\Magento\Customer\Api\Data\CustomerInterface'
            );
        }
        $this->session->setCustomerData($customerDataObject);
        $this->session->setChangePassword($this->getRequest()->getParam('changepass') == 1);

        $resultPage->getConfig()->getTitle()->set(__('Artist Artwork'));
        $resultPage->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        //echo $resultPage;


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        //echo 
        $media_url = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $stat_img = $storeManager->getStore()->getBaseUrl() . "pub/static/frontend/Custom/theme/en_US/images/upload1.png";
        $bu = $storeManager->getStore()->getBaseUrl() . "pub/media/artwork/";
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        if ($customerSession->isLoggedIn()) {
            $cid = $customerSession->getCustomer()->getId();
        }
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
        $result1 = $connection->fetchAll("SELECT * FROM art_table WHERE art_user =" . $cid);

        foreach ($result1 as $key) {
            $art_id = $key['art_id'];
            $art_image = $key['art_image'];
        }

        $add_work = "";

        if ($add_work == "") {
            $av = 1;
        } else {
            $av = 2;
        }

        $loadig_gif = $storeManager->getStore()->getBaseUrl() . "pub/static/frontend/Custom/theme/en_US/images/loader-1.gif";

        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        //$media_url=$objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        //$collection = $productCollection->create()->addAttributeToSelect('*')->addAttributeToFilter('status', array('eq' => 1))->setPageSize(3)->load(); //'eq' => 2 Disable 
        $collection = $productCollection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('status', array('eq' => 1))
                ->addAttributeToFilter('art_product_type', array('eq' => 'base'))
                ->addAttributeToFilter('visibility', array('neq' => 1))
                ->load(); //'eq' => 2 Disable 
        $base_url = $storeManager->getStore()->getBaseUrl();
        list($width, $height) = getimagesize($bu . $art_image);
        ob_start();
        try {
            ?>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12 text-center">
                        <h4>Product Previews</h4>
                    </div>
                </div>
                <div class="row product-list">
                    <?php
                    $productDetailArray = array();
                    $baseMarkup = 10;
                    $fileSystem = $objectManager->get('Magento\Framework\Filesystem');
                    $basePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product');
                    $counter = 0;
                    $customizationAreaSectionHTML = array();
                    $productPerRow = 3;
                    foreach ($collection as $product) {

                        if ($counter % $productPerRow == 0) {
                            if ($counter) {
                                echo "</div>";
                            }
                            $tempIndex = 0;
                            echo '<div class="customization-area-container">';
//                        var_dump($customizationAreaSectionHTML);
                            foreach ($customizationAreaSectionHTML as $HTML) {
                                echo $HTML;
//                            echo $customizationAreaSectionHTML[$tempIndex];
//                            unset($customizationAreaSectionHTML[$tempIndex]);
                                $tempIndex++;
                            }
                            $customizationAreaSectionHTML = array();
                            echo '</div>';
                            echo "<div class='product-row'>";
                        }

                        $product_id = intval(trim($product->getId()));
                        $productDetailArray[$product_id] = array();
                        $productDetailArray[$product_id]['price'] = ($product->getPrice()) ? $product->getPrice() : 50;
                        $productDetailArray[$product_id]['markup'] = $baseMarkup;
                        $productDetailArray[$product_id]['new-price'] = ($product->getPrice()) ? ($product->getPrice() + 10) : (50 + 10);

                        $productDetailArray[$product_id]['images'] = array();
                        $productCategoryIds = $product->getCategoryIds();
                        $productCategoryHandle = "";
                        if (count($productCategoryIds)) {
                            $productCategory = $objectManager->create('Magento\Catalog\Model\Category')->load($productCategoryIds[0]);

                            $productParentCategory = $productCategory->getParentCategory();
                            $productCategoryHandle = str_replace(" ", "", preg_replace('/[^A-Za-z0-9 ]+/', ' ', strtolower($productCategory->getName())));
                            if ($productParentCategory)
                                $productCategoryHandle.="_" . str_replace(" ", "", preg_replace('/[^A-Za-z0-9 ]+/', ' ', strtolower($productParentCategory->getName())));
                        }
                        ?>
                        <div class="col-lg-4 col-md-4 product-div slide" data-artcategory="<?php echo $product->getData('art_product_category') ?>" artwork-category="<?php echo $productCategoryHandle; ?>" product-id="<?php echo $product->getId() ?>" >
                            <div class="slide-overlay disabled-preview">
                                <div class="img-cell">
                                    <div class="img-middle">
                                        <img src="<?php echo $bu . $art_image ?>" class="canvas-export artwork-image-preview before-modifications" style="display: none">
                                        <img src="<?php echo $media_url . 'catalog/product' . $product->getData('thumbnail') ?>"  style="height: 100%;width: 100%" class="product-original-image-preview " alt="<?php echo $product->getName() ?>">
                                        <div class="product-preview-loader" style="">
                                            <img src="<?php echo $loadig_gif; ?>" height="150" width="250">
                                        </div>
                                    </div>
                                </div>
                            </div>                        
                            <div class="preview-info" style="text-align: center">
                                <strong><?php echo $product->getName() ?></strong> <span class="product-category-span">(<?php echo $productCategory->getName(); ?>:<?php echo $productParentCategory->getName(); ?>)</span>
                            </div>
                            <div class="action-area">
                                <button type="button" disabled="" onclick="customize_product(this, true);" artwork-category="<?php echo $productCategoryHandle; ?>" data-artcategory='<?php echo $product->getData('art_product_category') ?>' data-productimage='<?php echo $media_url . 'catalog/product' . $product->getData('thumbnail') ?>' data-artimage='<?php echo $bu . $art_image ?>' class="btn-edit btn-default canvas-edit-button customize-product-<?php echo $product->getId() ?>" data-product-id="<?php echo $product->getId() ?>" edited-canvas="">Edit</button>
                                <button type="button" class="btn-end en-bt btn-success" id="en-bt<?php echo $product->getId() ?>" onclick="disable_art(this.id)" value="<?php echo $product->getId() ?>" style="display: none;">Enabled</button>
                                <button type="button" class="btn-end btn-danger btn-round ds-bt"  id="ds-bt<?php echo $product->getId() ?>" onclick="enable_art(this.id)" value="<?php echo $product->getId() ?>" style="float:right;">Disabled</button>
                            </div>
                            <div class="clearfix"></div>
                            <?php
                            ob_start();
                            ?>
                            <div class="row customization_area" customizing-product-id="<?php echo $product->getId() ?>" style="clear:both;display:none;">
                                <div class="col-lg-8 col-md-8 left-section">
                                    <div class="artwork-changed-image" id="<?php echo $product->getId() ?>-product-artwork" style="display: none">
                                        <img src="" />
                                    </div>
                                    <div class="product-artwork-original-image" id="<?php echo $product->getId() ?>-product-original-image" artwork-product-id="<?php echo $product->getId() ?>" style="display: none">
                                        <img src="<?php echo $media_url . 'catalog/product' . $product->getData('thumbnail') ?>" />
                                    </div>
                                    <div class="drawingArea product-original-canvas">
                                        <canvas style="border:2px dotted #000000;" id="canvas-<?php echo $product->getId() ?>" width="500" height="600">
                                        </canvas>
                                    </div>
                                    <div class="drawingArea_sub artwork-image-canvas" artwork-category="<?php echo $productCategoryHandle; ?>">
                                        <canvas style="border:2px dotted #000000;" id="canvas_sub-<?php echo $product->getId() ?>" width="500" height="600"></canvas>
                                    </div>

                                    <div class="artwork-product-creator-container" style="display: none">
                                        <canvas style="" id="canvas-creator-<?php echo $product->getId() ?>" width="500" height="600">

                                        </canvas>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 product-customization-section-container" product-id="<?php echo $product->getId(); ?>">

                                    <div class="tab" product-id="<?php echo $product->getId(); ?>">
                                        <button class="tablinks" opentabid="product-<?php echo $product->getId(); ?>-canvas-customization-section" onclick="openTab(this)"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
                                        <button class="tablinks" opentabid="product-<?php echo $product->getId(); ?>-availability-section" onclick="openTab(this)"><i class="fa fa-check-square" aria-hidden="true"></i></button>
                                        <button class="tablinks" opentabid="product-<?php echo $product->getId(); ?>-price-section" onclick="openTab(this)"><i class="fa fa-money" aria-hidden="true"></i></button>
                                    </div>

                                    <div id="product-<?php echo $product->getId(); ?>-canvas-customization-section" class="tabcontent" tabid="">
                                        <h3>Edit</h3>
                                        <div class="add-text-input-container">
                                            <div id="product-<?php echo $product->getId(); ?>-slider" class="custom-ui-slider" product-id="<?php echo $product->getId(); ?>">
                                                <div id="product-<?php echo $product->getId(); ?>-custom-handle" product-id="<?php echo $product->getId(); ?>" class="ui-slider-handle"></div>
                                            </div>
                                            <div class="input-text-container">
                                                <input name="artwork-text-input" class="artwork-add-text-input" type="text" placeholder="Add text here.."/>
                                                <span class="text-error error" style="display: none">Please select text.</span>
                                            </div>
                                            <button class="add-text-btn" onclick="addTextToActiveArtwork(this); return false;">Add Text</button>
                                            <button class="remove-text-btn" onclick="removeActiveElement(this); return false;">Remove Text</button>
                                        </div>
                                    </div>

                                    <div id="product-<?php echo $product->getId(); ?>-availability-section" class="tabcontent" tabid="">
                                        <h3>Availability</h3>
                                        <?php
                                        $hasOptions = false;
                                        if ($product->getTypeId() == 'configurable') {
                                            $productDetailArray[$product_id]['type'] = 'configurable';
                                            ?>
                                            <div class="configurable-options-container">
                                                <?php
                                                // Collect options applicable to the configurable product
                                                $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                                                $colorArray = array();
                                                $colorImageArray = array();
                                                $sizeArray = array();
                                                $childProductsArray = array();
                                                $attributeOptions = array();
                                                $productSizeArray = array();
                                                $productColorArray = array();
                                                $swatchHelper = $objectManager->get("Magento\Swatches\Helper\Media");

                                                foreach ($productAttributeOptions as $productAttribute) {
                                                    foreach ($productAttribute['values'] as $attribute) {
                                                        $attributeOptions[$productAttribute['label']][$attribute['value_index']] = $attribute['store_label'];
                                                        if ($productAttribute['label'] == "Colour") {
//                                                    $colorArray[$attribute['value_index']] = $attribute['store_label'];

                                                            $swatchCollection = $objectManager->create('Magento\Swatches\Model\ResourceModel\Swatch\Collection');
                                                            $swatchCollection->addFieldtoFilter('option_id', $attribute['value_index']);
                                                            $item = $swatchCollection->getFirstItem();
                                                            $colorArray[$attribute['value_index']] = $item->getValue();
                                                        } else if ($productAttribute['label'] == "Size")
                                                            $sizeArray[$attribute['value_index']] = $attribute['store_label'];
                                                    }
                                                }

                                                $childProducts = $product->getTypeInstance()->getUsedProducts($product);
//                                        echo "<pre>";
                                                if (count($childProducts)) {
                                                    $hasOptions = true;
                                                    foreach ($childProducts as $child) {
                                                        $childProductsArray[] = $child;
                                                        $key = "";
                                                        if ($child->getData('colour') && $child->getData('size')) {
                                                            $color = $child->getData('colour');
                                                            $size = $child->getData('size');

                                                            $colorImageArray[$child->getData('colour')] = $child->getData('thumbnail');
                                                            $productColorArray[$child->getData('colour')][] = $child->getId();

                                                            $productSizeArray[$child->getData('size')][] = $child->getId();

                                                            $key = $size . "~" . $color;
                                                        } else if ($child->getData('colour')) {
                                                            $colorImageArray[$child->getData('colour')] = $child->getData('thumbnail');
                                                            $productColorArray[$child->getData('colour')][] = $child->getId();

                                                            $key = $child->getData('colour');
                                                        } else if ($child->getData('size')) {
                                                            $productSizeArray[$child->getData('size')][] = $child->getId();
                                                            $key = $child->getData('size');
                                                        }

                                                        if ($key) {
                                                            $productDetailArray[$product_id]['options'][$key] = 0;

                                                            if ($child->getData('colour')) {
                                                                $path = $basePath . $child->getData('thumbnail');
                                                                $type = pathinfo($path, PATHINFO_EXTENSION);
                                                                $data = file_get_contents($path);
                                                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

//                                                                $productDetailArray[$product_id]['images'][$child->getData('colour')] = $base64;
                                                            }
                                                        }
                                                        $productDetailArray[$product_id]['price'] = $child->getPrice();
                                                        $productDetailArray[$product_id]['markup'] = $baseMarkup;
                                                        $productDetailArray[$product_id]['new-price'] = $child->getPrice() + 10;
//                                            print_r($child->getTypeInstance(true)->getAttributes($child)->toArray());
                                                    }
                                                } else {
                                                    $productDetailArray[$product_id]['options'] = 0;
                                                    $path = $basePath . $product->getData('thumbnail');
                                                    $type = pathinfo($path, PATHINFO_EXTENSION);
                                                    $data = file_get_contents($path);
                                                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                                    $hasOptions = false;
                                                    $productDetailArray[$product_id]['type'] = 'simple';
                                                }
//                                        print_r($productAttributeOptions);
//                                        $colorArray = array_values($colorArray);
//                                        $colorImageArray = array_values($colorImageArray);
//                                        $i = 0;

                                                if (count($colorArray)) {
                                                    ?>
                                                    <div class="color-options-container color-options-for-<?php echo $product->getId(); ?>">
                                                        <h3>Colors</h3>
                                                        <?php
                                                        foreach ($colorArray as $colorKey => $color) {
                                                            ?>
                                                            <div class="color-selected-option-input-container applicable" id="product-<?php echo $product->getId(); ?>-swatch-<?php echo $colorKey; ?>-show" colorid='<?php echo $colorKey; ?>'>
                                                                <span class="color-option-element" style="background-color: <?php echo $color; ?>;height:20px;width:20px; display: inline-block;border:1px solid"></span>
                                    <!--                                                <span style="background-color: <?php echo strtolower(str_replace(" ", "-", $color)); ?>;height: 20px;width:20px" class="color-option" ></span>-->
                                    <!--                                                <img style="height: 50px;width: 50px;display: inline-block" src="<?php echo $media_url . 'catalog/product' . $colorImageArray[$colorKey]; ?>" class="color-option-image" onclick="changeProductPreviewImage(this);"/>-->
                                                            </div>
                                                            <div class="product-artwork-original-image" id="<?php echo $product->getId() ?>-product-<?php echo $colorKey; ?>-original-image" artwork-product-id="<?php echo $product->getId() ?>" color-id="<?php echo $colorKey; ?>" style="display: none">
                                                                <img src="<?php echo $media_url . 'catalog/product' . $colorImageArray[$colorKey]; ?>" />
                                                            </div>
                                                            <?php
//                                                    $i++;
                                                        }
                                                        ?>
                                                        <a class="color-availability-link" data-toggle="modal" data-target="#color-availability-modal-<?php echo $product->getId(); ?>" href="javascript:void(0);" onclick="setSelectedColors(<?php echo $product->getId(); ?>); jQuery('#color-availability-modal-<?php echo $product->getId(); ?>').show();">Color Availibility</a>

                                                        <!-- Modal -->
                                                        <div style="display: none" id="color-availability-modal-<?php echo $product->getId(); ?>" class="modal fade" role="dialog">
                                                            <div class="modal-table">
                                                                <div class="modal-cell">
                                                                    <div class="modal-dialog">

                                                                        <!-- Modal content-->
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <button type="button" class="close" data-dismiss="modal" onclick="jQuery('#color-availability-modal-<?php echo $product->getId(); ?>').hide();">&times;</button>
                                                                                <h4 class="modal-title">Colors Available For Sale</h4>
                                                                            </div>
                                                                            <div class="modal-body" product-id='<?php echo $product->getId(); ?>'>
                                                                                <?php
                                                                                foreach ($colorArray as $colorKey => $color) {
                                                                                    ?>
                                                                                    <div class="color-option-input-container ">

                                                                                        <input type="checkbox" name="product-<?php echo $product->getId(); ?>-swatch[]" class='product-<?php echo $product->getId(); ?>-swatches' checked="" value="<?php echo $colorKey; ?>" id="product-<?php echo $product->getId(); ?>-swatch-<?php echo $colorKey; ?>" />
                                                                                        <label for="product-<?php echo $product->getId(); ?>-swatch-<?php echo $colorKey; ?>" style="background-color: <?php echo $color; ?>;height:20px;width:20px; display: inline-block;border:1px solid"><span></span></label>                                            
                                                                                    </div>
                                                                                    <?php
                                                                                }
                                                                                ?>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="applyAvailableColors(<?php echo $product->getId(); ?>)">Apply</button>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
//                                        print_r($colorArray);
//                                        print_r($colorImageArray);
//                                        echo "</pre>";

                                                if (array($sizeArray)) {
                                                    ?>
                                                    <div class="size-options-container size-options-for-<?php echo $product->getId(); ?>" >
                                                        <h3>Size</h3>
                                                        <?php
                                                        foreach ($sizeArray as $sizeKey => $size) {
                                                            ?>
                                                            <div class="size-selected-option-input-container applicable" id="product-<?php echo $product->getId(); ?>-size-<?php echo $sizeKey; ?>-show" sizeid='<?php echo $sizeKey; ?>'>
                                                                <span class="size-option-element" style="display: inline-block;"><?php echo $size; ?></span>                                                        
                                                            </div>
                                                            <?php
                                                        }
                                                        ?>
                                                        <a class="size-availability-link" href="javascript:void(0);" data-toggle="modal" data-target="#size-availability-modal-<?php echo $product->getId(); ?>" onclick="setSelectedSizes(<?php echo $product->getId(); ?>); jQuery('#size-availability-modal-<?php echo $product->getId(); ?>').show();">Size Availibility</a>

                                                        <!-- Modal -->
                                                        <div style="display: none" id="size-availability-modal-<?php echo $product->getId(); ?>" class="modal fade" role="dialog">
                                                            <div  class="modal-table">
                                                                <div class="modal-cell">
                                                                    <div class="modal-dialog">

                                                                        <!-- Modal content-->
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <button type="button" class="close" data-dismiss="modal" onclick="jQuery('#size-availability-modal-<?php echo $product->getId(); ?>').hide();">&times;</button>
                                                                                <h4 class="modal-title">Sizes Available For Sale</h4>
                                                                            </div>
                                                                            <div class="modal-body" product-id='<?php echo $product->getId(); ?>'>
                                                                                <?php
                                                                                foreach ($sizeArray as $sizeKey => $size) {
                                                                                    ?>
                                                                                    <div class="size-option-input-container" >

                                                                                        <input type="checkbox" name="product-<?php echo $product->getId(); ?>-size[]" class="product-<?php echo $product->getId(); ?>-sizes" checked="" value="<?php echo $sizeKey; ?>" id="product-<?php echo $product->getId(); ?>-size-<?php echo $sizeKey; ?>" />
                                                                                        <label for="product-<?php echo $product->getId(); ?>-size-<?php echo $sizeKey; ?>" style="display: inline-block;"><span></span><?php echo $size; ?></label>                            
                                                                                    </div>
                                                                                    <?php
                                                                                }
                                                                                ?>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="applyAvailableSizes(<?php echo $product->getId(); ?>)">Apply</button>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>                                            
                                                    </div>
                                                    <?php
                                                }
                                                ?>                                        
                                            </div>
                                            <?php
                                        } else {
                                            $path = $basePath . $product->getData('thumbnail');
                                            $type = pathinfo($path, PATHINFO_EXTENSION);
                                            $data = file_get_contents($path);
                                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
//                                            $productDetailArray[$product_id]['images'] = $base64;
                                            $productDetailArray[$product_id]['options'] = 0;
                                            $productDetailArray[$product_id]['type'] = 'simple';
                                            $hasOptions = false;
                                        }

                                        if (!$hasOptions) {
                                            ?>
                                            <div class="no-configurable-options">
                                                No configurable options
                                            </div>
                                            <?php
                                        }
                                        ?>                                    
                                    </div>

                                    <div id="product-<?php echo $product->getId(); ?>-price-section" class="tabcontent" tabid="">
                                        <h3>Price</h3>
                                        <div class="pricing-tab-section-container">
                                            <div class="pricing-section-container" id="product-<?php echo $product_id; ?>-markup-table-container">

                                                <div class="price-section">
                                                    <div class="base-price-div">
                                                        <span class="price-title">Base Price</span>
                                                        $<span class="artwork-base-price-price"><?php echo number_format($productDetailArray[$product_id]['price'], 2); ?></span>
                                                    </div>
                                                    <div class="your-margin-div">
                                                        <span class="price-title">Your Margin</span>
                                                        $<input type='text' name="product-<?php echo $product_id; ?>-markup" id="product-<?php echo $product_id; ?>-markup" class="artwork-markup validate-number" oldval='<?php echo $productDetailArray[$product_id]['markup']; ?>' onchange="changeProductPrice(<?php echo $product_id; ?>);" value='<?php echo ($productDetailArray[$product_id]['markup']); ?>'/>
                                                    </div>
                                                    <div class="retail-price-div">
                                                        <span class="price-title">Retail Price</span>
                                                        $<span class="artwork-retail-price"><?php echo number_format($productDetailArray[$product_id]['new-price'], 2); ?></span>
                                                    </div>
                                                </div>
                                                <input type="hidden" id="product-<?php echo $product_id; ?>-base-price" class="product-base-price" value="<?php echo ($productDetailArray[$product_id]['price']); ?>" />
                                            </div>
                                        </div>   
                                    </div> 

                                </div>

                                <div class="apply-changes-container">
                                    <button onclick="applyChangesForProduct(this); return false;" class="apply-changes-button">Apply</button>
                                </div>                            
                            </div>
                            <div class="clearfix"></div>
                            <?php
                            $customizationAreaSectionHTML[] = ob_get_contents();
                            ob_end_clean();
                            ?>
                            <div class="clearfix"></div>
                        </div>
                        <?php
                        $counter++;
                    }
                    echo "</div>";
                    $tempIndex = 0;
//                        var_dump($customizationAreaSectionHTML);
                    echo '<div class="customization-area-container">';
                    foreach ($customizationAreaSectionHTML as $HTML) {
                        echo $HTML;
//                            echo $customizationAreaSectionHTML[$tempIndex];
//                            unset($customizationAreaSectionHTML[$tempIndex]);
                        $tempIndex++;
                    }
                    echo '</div>';
                    ?>
                </div>
                <!--            <div class="row" id="customization_area" style="clear:both;display:none;">
                                <div class="col-lg-12 col-md-12">
                                    <div class="drawingArea">
                                        <canvas style="border:2px dotted #000000;" id="canvas" width="500" height="500">
                                        </canvas>
                                    </div>
                                    <div class="drawingArea_sub">
                                        <canvas style="border:2px dotted #000000;" id="canvas_sub" width="180" height="150"></canvas>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12">
                                    <div class="add-text-input-container">
                                        <input name="artwork-text-input" id="artwork-text-input" class="artwork-add-text-input" type="text" />
                                        <button onclick="addTextToActiveArtwork();return false;">Add Text</button>
                                    </div>
                                </div>
                            </div>-->
                <div class="row art-detail-section">
                    <form class="form form-save-art" action="<?php echo $base_url; ?>artwork/account/saveArt" method="post" id="form-validate" enctype="multipart/form-data" data-hasrequired="<?php /* @escapeNotVerified */ echo __(' Required Fields'); ?>" autocomplete="off"> 
                        <div class="col-lg-4 col-md-4 form-details">
                            <div class="art-title-container">
                                <input type="text" name="title" id="title_art" class="title_art" placeholder="Title"> 
                                <span class="error art-title-error" style="display: none"></span>
                            </div>
                            <input type="hidden" name="art_work" id="art_work" class="add_work" value="<?php echo $art_id; ?>">
                            <input type="hidden" name="art_enb" id="art_enb" class="art_enb" value='<?php echo json_encode($productDetailArray); ?>'> 
                            <input type="hidden" name="art_dsb" id="art_dsb" class="art_dsb" value=""> 
                            <div class="art-tags-container">
                                <textarea name="tag" id="tag_art" class="tag_art" placeholder="Tag"></textarea>
                                <input type="hidden" id="tags-list" class="tags-list" name="tags-list" value="" />
                                <div class="show-tags-container"></div>
                                <div class="tag-element-container" style="display: none"><div class="tag-element"><span class="tag-element-text"></span><span class="remove-tags"><a><i class="fa fa-times" aria-hidden="true"></i></a></span></div></div>
                                <span class="tag-hint">Please enter your tags seperated by comma.</span>
                                <span class="error art-tags-error" style="display: none"></span>
                            </div>
                            <input type="hidden" id="artwork-image-width" value="<?php echo $width; ?>" />
                            <input type="hidden" id="artwork-image-height" value="<?php echo $height; ?>" />
                            <input type="hidden" id="artwork-remove-id" value="" name="artwork-remove-id"/>
                        </div>
                        <div class="col-lg-4 col-md-4 default-section">

                            <div class="default-block">
                                <label><strong>Default view</strong></label>
                                <select name="cat" id="cat_art" class="cat_art">
                                    <option value="">Select a default view</option>
                                    <?php foreach ($collection as $product1) { ?>
                                        <option value="<?php echo $product1->getID() ?>" disabled=""><?php echo $product1->getName() ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <span class="error art-default-view-error" style="display: none"></span>
                            </div>
                            <div class="who-block">
                                <label><strong>Who can view this work?</strong></label>
                                <input type="radio" name="who" id="who" value="1" class="who first-radio"  checked="true"> Anybody(public)
                                <input type="radio" name="who" id="who" value="0" class="who sec-radio"> Only you (private)
                            </div>
                            <div class="mature-block">
                                <label><strong>Is this mature content?</strong></label>
                                <ul>
                                    <li>Nudity or Lingerie</li> 
                                    <li>Adult Language</li> 
                                    <li>Alcohol or Drugs</li> 
                                    <li>Blood, Guns or Violence</li> 
                                </ul>
                                <div class="mature-input">
                                    <input type="radio" name="mature" id="mature" value="1" class="mature" required="true"> yes
                                    <input type="radio" name="mature" id="mature" value="0" class="mature"> No
                                </div>
                            </div>
                        </div>   
                        <div class="media-section">
                            <p>Media - </p>
                            <div class="inner-content">
                                <?php
                                $media_attribute = $this->eavConfig->getAttribute('catalog_product', 'crtwork_category');
                                $media_options = $media_attribute->getSource()->getAllOptions();
                                /* echo "<pre>";
                                  print_r($media_options);
                                  echo "</pre>"; */
                                foreach ($media_options as $mo) {
                                    if ($mo['value'] != "") {
                                        ?>
                                        <p><input type="checkbox" name="art_media_categories[]" value="<?php echo $mo['value'] ?>" /><label><?php echo $mo['label'] ?></label></p>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>                 
                        <div class="col-lg-4 col-md-4 publish-btn">
                            <input onclick="return processArtwork();" id="publish-artwork" type="submit" class="btn btn-warning btn-round" name="add_art" value="Publish">
                        </div>
                        <div class="artwork-creation-canvas-container" style="display:none">
                            <canvas id="artwork-creation-canvas" width="500" height="600"></canvas>
                        </div>
                        <div class="temp-image-container" >
                        </div>
                    </form>                    
                </div>
            </div>

            <script type="text/javascript">
                var elems_arr = [];
                var base_url = "<?php echo $base_url ?>";
                var canvasArray = [];
                var canvasSubArray = [];
                var canvasCreationArray = [];
                var productIdArray = [];
                var scaleFactor = 1.0;
                var productDetailArray = jQuery("#art_enb").val();
                productDetailArray = JSON.parse(productDetailArray);
                console.log(productDetailArray);
                var canvas = null;
                var canvas_sub = null;
                var canvasCreation = null;
                var currentProductId = null;
                var processed = false, processing = false;
                var productArtworkArray = [], productOriginalImagesArray = [], processedProducts = 0;
                var artworkHeight = parseInt(jQuery("#artwork-image-height").val()), artworkWidth = parseInt(jQuery("#artwork-image-width").val());
                var fixedWidth = 150;
                var canvasSubHeight = fixedWidth * artworkHeight / artworkWidth;
                if (fixedWidth > artworkWidth) {
                    fixedWidth = artworkWidth;
                    canvasSubHeight = artworkHeight;
                }

                //            jQuery.each(jQuery(".canvas-edit-button"), function (index, element) {
                //                customize_product(element, false);
                //            });            

                function initializeCanvases(index) {
                    if (index < Object.keys(productDetailArray).length) {
                        customize_product(jQuery(".canvas-edit-button").slice(index, (index + 1)), false);
                    } else {
                        jQuery(".canvas-export").show();
                        jQuery(".product-preview-loader").hide();
                        jQuery(".canvas-edit-button").prop("disabled", false);
                    }
                }

                function customize_product(obj, showCustomization) {
                    var pi = jQuery(obj).data("productimage");
                    var ai = jQuery(obj).data("artimage");
                    var cl = jQuery(obj).data("artcategory");
                    var productId = jQuery(obj).data("product-id");
                    var productDiv = jQuery(obj).closest(".product-div");
                    var position = jQuery.inArray(productId, productIdArray);
                    var customizingDiv = jQuery(".customization_area[customizing-product-id=" + productId + "]");
                    var currentCustomization = true;
                    if (showCustomization && jQuery(".customization_area:visible").length && jQuery(".customization_area:visible").attr("customizing-product-id") == customizingDiv.attr("customizing-product-id")) {
                        currentCustomization = false;
                    }
                    canvasCreationArray[jQuery(".customization_area:visible").attr("customizing-product-id")] = canvasCreation;
                    jQuery(".customization_area:visible").hide();
                    if (currentCustomization) {
                        console.log(productId);
                        currentProductId = productId;
                        if (position == -1) {
                            productIdArray.push(productId);
                            canvas = new fabric.Canvas('canvas-' + productId);
                            canvasArray.push(canvas);
                            canvas_sub = new fabric.Canvas('canvas_sub-' + productId);
                            canvasSubArray.push(canvas_sub);
                            canvasCreation = new fabric.Canvas("canvas-creator-" + productId);
                            canvasCreationArray.push(canvasCreation);
                            customizingDiv.find(".drawingArea_sub").addClass(cl);
                            fabric.Image.fromURL(pi, function (myImg) {
                                var img1 = myImg.scale(scaleFactor).set({height: canvas.height, width: canvas.width, selectable: false});
                                canvas.clear();
                                //                            img1.setCoords();
                                canvas.add(img1);
                                canvas.centerObject(img1);
                                //                            canvas.calcOffset();
                                canvas.renderAll();
                                canvasCreation.clear();
                                canvasCreation.add(img1);
                                canvasCreation.centerObject(img1);
                                canvasCreation.renderAll();
                            });
                            fabric.Image.fromURL(ai, function (myImg) {

                                var img1 = myImg.scale(scaleFactor).set({height: canvasSubHeight, width: fixedWidth, selectable: true, hasControls: false, left: (canvas_sub.width - fixedWidth) / 2, top: (canvas_sub.height - canvasSubHeight) / 2});
                                canvas_sub.clear();
                                canvas_sub.add(img1);
                                //                            img1.setCoords();                                                        
                                //                            canvas_sub.centerObject(img1);
                                canvas_sub.setActiveObject(img1);
                                canvas_sub.renderAll();
                                canvas_sub.calcOffset();
                                canvas_sub.item(0).lockRotation = true;
                                canvas_sub.item(0).lockScalingX = canvas_sub.item(0).lockScalingY = true;

                                var data = canvas_sub.toDataURL("image/png");
                                jQuery("#" + currentProductId + "-product-artwork img").attr("src", data);
                                productDiv.find(".canvas-export").removeClass("before-modifications").attr("src", data);
                                canvas_sub.renderAll();
                                if (!showCustomization) {
                                    initializeCanvases(productIdArray.length);
                                }

                                //                            var data = canvas_sub.toDataURL("image/png");
                                //                            fabric.Image.fromURL(data, function (myImg) {
                                //                                var img2 = myImg.scale(scaleFactor).set({height: canvas_sub.height, width: canvas_sub.width, selectable: false, top: 150, left: 150});
                                //                                canvasCreation.add(img2);
                                //                                canvasCreation.renderAll();
                                //                            });
                            });
                            canvas_sub.on({
                                "object:modified": function (e) {
                                    if (e.target) {
                                        var data = canvas_sub.toDataURL("image/png");
                                        jQuery("#" + currentProductId + "-product-artwork img").attr("src", data);
                                        productDiv.find(".canvas-export").removeClass("before-modifications").attr("src", data);
                                        canvas_sub.renderAll();
                                        //                                    var canvas_objects = canvasCreation._objects;
                                        //                                    var last = canvas_objects[canvas_objects.length - 1]; //Get last object   
                                        //                                    canvasCreation.remove(last);                                    
                                        //                                    fabric.Image.fromURL(data, function (myImg) {
                                        //                                        var img2 = myImg.scale(scaleFactor).set({height: canvas_sub.height, width: canvas_sub.width, selectable: false, top: 150, left: 150});
                                        //
                                        //                                        canvasCreation.add(img2);
                                        //                                        canvasCreation.renderAll();
                                        //
                                        //                                        var productDetail = productDetailArray[currentProductId];
                                        //        //                                        if (productDetail['type'] == 'simple') {
                                        //                                        productDetail['images'] = canvasCreation.toDataURL("image/png");
                                        //
                                        //                                        saveProductArray(productDetail, currentProductId);
                                        //        //                                        }
                                        //                                    });
                                    }
                                }
                            });

                            jQuery("#product-" + productId + "-slider").slider({
                                value: 100,
                                create: function () {
                                    jQuery("#product-" + productId + "-custom-handle").text(jQuery(this).slider("value"));
                                },
                                slide: function (event, ui) {
                                    jQuery("#product-" + productId + "-custom-handle").text(ui.value);
                                    canvas_sub.item(0).scaleX = ui.value / 100;
                                    canvas_sub.item(0).scaleY = ui.value / 100;
                                    canvas_sub.renderAll();
                                    canvas_sub.calcOffset();
                                    canvas_sub.renderAll();

                                    var data = canvas_sub.toDataURL("image/png");
                                    jQuery("#" + currentProductId + "-product-artwork img").attr("src", data);
                                    productDiv.find(".canvas-export").removeClass("before-modifications").attr("src", data);
                                    canvas_sub.renderAll();
                                }
                            });
                        } else {
                            canvas = canvasArray[position];
                            canvas_sub = canvasSubArray[position];
                            canvasCreation = canvasCreationArray[position];
                        }
                        //window.location.href = base_url + "customize-product/index.php?pi=" + pi + "&ai=" + ai + "&type=" + type;
                        //                    productDiv.find(".customization_area").css("display", "block");
                        if (showCustomization)
                        {
                            customizingDiv.css("display", "block");
                            openTab(jQuery(".product-customization-section-container[product-id=" + productId + "] .tablinks")[0]);
                        }
                    } else {

                    }
                    //                jQuery("#canvas_sub").css("height", "250");
                    //                jQuery("#canvas_sub").css("width", "160");                
                }

                function enable_art(a) {
                    jQuery("#" + a).hide();
                    jQuery("#" + a).closest('.action-area').children('.en-bt').show();
                    var elems_val = jQuery("#" + a).val();
                    elems_arr.push(elems_val);
                    var product_id = jQuery("#" + a).val(), productDetail = productDetailArray[product_id];
                    if (productDetail['type'] == 'configurable') {
                        jQuery.each(jQuery(".size-options-for-" + product_id + " .size-selected-option-input-container.applicable"), function (index, element) {
                            var sizeid = jQuery(element).attr("sizeid");
                            jQuery.each(jQuery(".color-options-for-" + product_id + " .color-selected-option-input-container.applicable"), function (ind, elm) {
                                var colorid = jQuery(elm).attr("colorid");
                                productDetail['options'][sizeid + "~" + colorid] = 1;
                                jQuery("#cat_art option[value=" + product_id + "]").prop("disabled", false);
                            });
                        });
                        jQuery.each(jQuery(".size-options-for-" + product_id + " .size-selected-option-input-container:not(.applicable)"), function (index, element) {
                            var sizeid = jQuery(element).attr("sizeid");
                            jQuery.each(jQuery(".color-options-for-" + product_id + " .color-selected-option-input-container"), function (ind, elm) {
                                var colorid = jQuery(elm).attr("colorid");
                                productDetail['options'][sizeid + "~" + colorid] = 0;
                            });
                        });
                    } else {
                        productDetail['options'] = 1;
                        jQuery("#cat_art option[value=" + product_id + "]").prop("disabled", false);
                    }

                    saveProductArray(productDetail, product_id);
                    jQuery(".product-div[product-id=" + product_id + "] .slide-overlay").removeClass("disabled-preview").addClass("enabled-preview");
                    //                if (productDetail['type'] == 'simple')
                    //                    jQuery("#art_enb").val(JSON.stringify(elems_arr));
                }
                function disable_art(b) {

                    jQuery("#" + b).hide();
                    jQuery("#" + b).closest('.action-area').children('.ds-bt').show();
                    var removeItem = jQuery("#" + b).val();
                    var value1 = jQuery('#art_enb').val(); //retrieve array
                    value1 = JSON.parse(value1);
                    var product_id = jQuery("#" + b).val(), productDetail = productDetailArray[product_id];
                    if (productDetail['type'] == 'configurable') {
                        jQuery.each(productDetail['options'], function (index, element) {
                            productDetail['options'][index] = 0;
                        });
                    } else {
                        productDetail['options'] = 0;
                    }
                    jQuery("#cat_art option[value=" + product_id + "]").prop("disabled", true);

                    saveProductArray(productDetail, product_id);
                    y = jQuery.grep(elems_arr, function (value) {
                        return value != removeItem;
                    });
                    //                jQuery("#art_enb").val(JSON.stringify(y));
                    jQuery(".product-div[product-id=" + product_id + "] .slide-overlay").removeClass("enabled-preview").addClass("disabled-preview");
                    elems_arr = y;
                }

                function addTextToActiveArtwork(currentElement) {

                    var textInput = jQuery(currentElement).closest(".add-text-input-container").find(".artwork-add-text-input"), textValue = textInput.val();
                    if (textValue) {
                        textInput.next(".text-error").hide();
                        var text = new fabric.Text(textValue, {left: canvas_sub.width / 2, top: canvas_sub.height / 2, fontSize: 20, fontFamily: 'Comic Sans', lockRotation: true, lockScalingX: true, lockScalingY: true});
                        textInput.val('');
                        canvas_sub.add(text);
                        //                    canvas_sub.centerObject(text);
                        canvas_sub.setActiveObject(text);
                        canvas_sub.renderAll();
                        canvas_sub.calcOffset();
                    } else {
                        textInput.next(".text-error").show();
                    }
                }

                function removeActiveElement(buttonElement) {
                    var activeElement = canvas_sub.getActiveObject();
                    if (activeElement != canvas_sub.item(0)) {
                        activeElement.remove();
                    }
                }

                function changeProductPreviewImage(colorImage) {
                    var colorImageSrc = jQuery(colorImage).attr('src'), productId = jQuery(colorImage).closest(".customization_area").attr("customizing-product-id"),
                            productDiv = jQuery(".product-div[product-id=" + productId + "]");
                    fabric.Image.fromURL(colorImageSrc, function (myImg) {
                        var img1 = myImg.scale(scaleFactor).set({height: canvas.height, width: canvas.width, selectable: false, hasControls: false});
                        canvas.clear();
                        //                            img1.setCoords();
                        canvas.add(img1);
                        canvas.centerObject(img1);
                        //                            canvas.calcOffset();
                        canvas.renderAll();
                    });
                    productDiv.find(".product-original-image-preview").attr("src", colorImageSrc);
                }

                function applyChangesForProduct(buttonElement) {
                    var customizingDiv = jQuery(buttonElement).closest(".customization_area"), productId = customizingDiv.attr("customizing-product-id");
                    var productDetail = productDetailArray[productId];
                    var imagePreviewData = canvasCreation.toDataURL("image/png");
                    jQuery(".customize-product-" + productId).click();
                }

                function applyAvailableColors(productId) {
                    var modalBody = jQuery("#color-availability-modal-" + productId + " .modal-body"),
                            isEnabled = (jQuery("#en-bt" + productId).is(":visible")),
                            productDetail = productDetailArray[productId];
                    jQuery.each(modalBody.find("input[type=checkbox]"), function (index, element) {
                        if (jQuery(element).is(":checked")) {
                            jQuery("#" + jQuery(element).attr("id") + "-show").show().addClass('applicable');
                            if (isEnabled) {
                                jQuery.each(jQuery(".size-options-for-" + productId + " .size-selected-option-input-container:visible"), function (ind, elm) {
                                    productDetail['options'][jQuery(elm).attr("sizeid") + "~" + jQuery(element).val() ] = 1;
                                });
                                jQuery.each(jQuery(".size-options-for-" + productId + " .size-selected-option-input-container:hidden"), function (ind, elm) {
                                    productDetail['options'][jQuery(elm).attr("sizeid") + "~" + jQuery(element).val() ] = 0;
                                });
                            }
                        } else {
                            jQuery("#" + jQuery(element).attr("id") + "-show").hide().removeClass('applicable');
                            if (isEnabled) {
                                jQuery.each(jQuery(".size-options-for-" + productId + " .size-selected-option-input-container"), function (ind, elm) {
                                    productDetail['options'][jQuery(elm).attr("sizeid") + "~" + jQuery(element).val() ] = 0;
                                });
                            }
                        }
                    });
                    saveProductArray(productDetail, productId);
                    jQuery("#color-availability-modal-" + productId).hide();
                }

                function applyAvailableSizes(productId) {

                    var modalBody = jQuery("#size-availability-modal-" + productId + " .modal-body"),
                            isEnabled = (jQuery("#en-bt" + productId).is(":visible")),
                            productDetail = productDetailArray[productId];
                    ;
                    jQuery.each(modalBody.find("input[type=checkbox]"), function (index, element) {
                        if (jQuery(element).is(":checked")) {
                            jQuery("#" + jQuery(element).attr("id") + "-show").show().addClass('applicable');
                            if (isEnabled) {
                                jQuery.each(jQuery(".color-options-for-" + productId + " .color-selected-option-input-container:visible"), function (ind, elm) {
                                    productDetail['options'][jQuery(element).val() + "~" + jQuery(elm).attr("colorid")] = 1;
                                });
                                jQuery.each(jQuery(".color-options-for-" + productId + " .color-selected-option-input-container:hidden"), function (ind, elm) {
                                    productDetail['options'][jQuery(element).val() + "~" + jQuery(elm).attr("colorid")] = 0;
                                });
                            }
                        } else {
                            jQuery("#" + jQuery(element).attr("id") + "-show").hide().addClass('applicable');
                            if (isEnabled) {
                                jQuery.each(jQuery(".color-options-for-" + productId + " .color-selected-option-input-container"), function (ind, elm) {
                                    productDetail['options'][jQuery(element).val() + "~" + jQuery(elm).attr("colorid")] = 0;
                                });
                            }
                        }
                    });
                    saveProductArray(productDetail, productId);
                    jQuery("#size-availability-modal-" + productId).hide();
                }



                function changeProductPrice(productId) {
                    var priceTable = jQuery("#product-" + productId + "-markup-table-container"),
                            productDetail = productDetailArray[productId],
                            productBasePrice = priceTable.find(".product-base-price").val(),
                            artworkMarkup = priceTable.find(".artwork-markup").val(),
                            artworkRetailPrice = parseFloat(productBasePrice) + parseFloat(artworkMarkup);
                    //                        artworkMargin = artworkRetailPrice - parseFloat(productBasePrice);
                    priceTable.find(".artwork-retail-price").text(artworkRetailPrice.toFixed(2));
                    //                priceTable.find(".artwork-margin").text(parseFloat(artworkMargin).toFixed(2));
                    productDetail['new-price'] = parseFloat(parseFloat(artworkRetailPrice).toFixed(2));
                    productDetail['markup'] = parseFloat(parseFloat(artworkMarkup).toFixed(2));
                    saveProductArray(productDetail, productId);
                }

                //            jQuery(document).on('keyup', '.validate-number', function () {
                //                var pattern = /[^0-9]/;
                //                var response = true;                
                //                if (pattern.test(this.value) || this.value > 100) {                    
                //                    response = false;
                //                    jQuery(this).val(jQuery(this).attr('oldval'));
                //                } else {
                //                    jQuery(this).attr("oldval", jQuery(this).val());
                //                }
                //                return response;
                //            });
                jQuery(document).on("keyup", ".validate-number", function (e) {
                    if (isNaN(jQuery(this).val())) {
                        jQuery(this).val(jQuery(this).attr('oldval'));
                    } else {
                        jQuery(this).attr("oldval", jQuery(this).val());
                    }
                });
                function setSelectedColors(productId) {
                    var colorContainer = jQuery(".color-options-for-" + productId);
                    jQuery(".product-" + productId + "-swatches").prop("checked", false);
                    jQuery.each(colorContainer.find(".color-selected-option-input-container:visible"), function (index, element) {
                        jQuery("#product-" + productId + "-swatch-" + jQuery(element).attr("colorid")).prop("checked", true);
                    });
                }

                function setSelectedSizes(productId) {
                    var sizeContainer = jQuery(".size-options-for-" + productId);
                    jQuery(".product-" + productId + "-sizes").prop("checked", false);
                    jQuery.each(sizeContainer.find(".size-selected-option-input-container:visible"), function (index, element) {
                        jQuery("#product-" + productId + "-size-" + jQuery(element).attr("sizeid")).prop("checked", true);
                    });
                }

                function saveProductArray(productDetail, product_id) {
                    productDetailArray[product_id] = productDetail;
                    jQuery("#art_enb").val(JSON.stringify(productDetailArray));
                    console.log(JSON.parse(jQuery("#art_enb").val()));
                }

                function processArtwork() {
                    if (!jQuery("#cat_art").val())
                    {
                        jQuery(".art-default-view-error").show().text("Please select a default view.");
                        return false;
                    }
                    jQuery(".art-default-view-error").hide();
                    if (!jQuery("#title_art").val())
                    {
                        jQuery(".art-title-error").show().text("Please select a title for this artwork.");
                        return false;
                    }
                    jQuery(".art-title-error").hide();
                    jQuery(".fullscreen-loader-container").show();
                    jQuery("body").css("overflow", "hidden");
                    if (!processed && !processing) {
                        jQuery("#publish-artwork").val("Publishing");
                        var productImagesArray = jQuery(".product-artwork-original-image"), productLength = productImagesArray.length;
                        processing = true;
                        jQuery.each(productImagesArray, function (index, element) {

                            var orignalImageDiv = jQuery(element), originalImage = orignalImageDiv.find("img").attr("src"), productId = orignalImageDiv.attr("artwork-product-id"), artworkImage = jQuery("#" + productId + "-product-artwork img").attr("src"), colorId = orignalImageDiv.attr('color-id');
                            //                        artworkCanvas.clear();                        
                            fabric.Image.fromURL(originalImage, function (myImg) {
                                var img1 = myImg.scale(scaleFactor).set({height: 600, width: 500, selectable: false});
                                productOriginalImagesArray[index] = [];
                                productOriginalImagesArray[index]['image'] = img1;
                                productOriginalImagesArray[index]['product-id'] = productId;
                                productOriginalImagesArray[index]['color-id'] = colorId;
                                //                                    artworkCanvas.clear();
                                //                                    artworkCanvas.add(img1);
                                //                                    artworkCanvas.centerObject(img1);
                                //                                    artworkCanvas.renderAll();                            
                                fabric.Image.fromURL(artworkImage, function (myImg) {
                                    var img2 = myImg.scale(scaleFactor).set({height: 600, width: 500, selectable: false});
                                    productArtworkArray[productId] = img2;
                                    //                                        artworkCanvas.add(img2);
                                    //                                        artworkCanvas.renderAll();
                                    createArtworkProductImage(productLength);
                                });
                            });
                            //                      return false;
                        });
                    }
                    return processed;
                }

                function createArtworkProductImage(length) {
                    if (++processedProducts == length) {
                        var artworkCanvas = new fabric.Canvas("artwork-creation-canvas");
                        jQuery.each(productOriginalImagesArray, function (index, element) {
                            var canvasImage = "", productDetail = productDetailArray[element['product-id']];
                            artworkCanvas.clear();
                            artworkCanvas.add(element['image']);
                            artworkCanvas.centerObject(element['image']);
                            artworkCanvas.renderAll();
                            artworkCanvas.add(productArtworkArray[element['product-id']]);
                            artworkCanvas.renderAll();
                            canvasImage = artworkCanvas.toDataURL("image/png");
                            if (productDetail['type'] == 'simple') {
                                productDetail['images'] = canvasImage;
                                //                                jQuery(".temp-image-container").append("<img src='" + canvasImage + "' />");
                            } else {
                                if (element['color-id']) {
                                    productDetail['images'][element['color-id']] = canvasImage;
                                } else {
                                    productDetail['images'][0] = canvasImage;
                                }
                                //                            jQuery(".temp-image-container").append("<img src='" + canvasImage + "' colorid='" + element['color-id'] + "'/>");
                            }
                            saveProductArray(productDetail, element['product-id']);
                        });
                        processed = true;
                        jQuery("#publish-artwork").click();
                    }
                }

                function openTab(element) {
                    var currentElement = jQuery(element), productId = currentElement.closest(".tab").attr("product-id"),
                            opentabid = currentElement.attr("opentabid");
                    jQuery(".tabcontent").hide();
                    jQuery("#" + opentabid).show();
                    jQuery(".tablinks.active-tab").removeClass("active-tab");
                    currentElement.addClass("active-tab");
                }

                initializeCanvases(0);
            </script>

            <style>


                /* Modal Content */
                .modal-content {
                    position: relative;
                    background-color: #fefefe;
                    margin: auto;
                    padding: 0;
                    border: 1px solid #888;
                    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
                    -webkit-animation-name: animatetop;
                    -webkit-animation-duration: 0.4s;
                    animation-name: animatetop;
                    animation-duration: 0.4s
                }

                /* Add Animation */
                @-webkit-keyframes animatetop {
                    from {top: -300px; opacity: 0}
                    to {top: 0; opacity: 1}
                }

                @keyframes animatetop {
                    from {top: -300px; opacity: 0}
                    to {top: 0; opacity: 1}
                } 

                /* Style the tab */
                div.tab {
                    float: left;
                    border: 1px solid #ccc;
                    background-color: #f1f1f1;
                    width: 30%;
                    height: 300px;
                }

                /* Style the buttons inside the tab */
                div.tab button {
                    display: block;
                    background-color: inherit;
                    color: black;
                    padding: 22px 16px;
                    width: 100%;
                    border: none;
                    outline: none;
                    text-align: left;
                    cursor: pointer;
                    transition: 0.3s;
                }

                /* Change background color of buttons on hover */
                div.tab button:hover {
                    background-color: #ddd;
                }

                /* Create an active/current "tab button" class */
                div.tab button.active {
                    background-color: #ccc;
                }

                /* Style the tab content */
                .tabcontent {
                    float: left;
                    padding: 0px 12px;
                    border: 1px solid #ccc;
                    width: 70%;
                    border-left: none;
                    height: 300px;
                } 

                .ui-slider-handle{
                    width: 1em;
                    height: 1.6em;
                    top: 50%;
                    margin-top: -.8em;
                    text-align: center;
                    line-height: 1.6em;
                }
            </style>
            <?php
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        ob_flush();
        die();
    }

}
