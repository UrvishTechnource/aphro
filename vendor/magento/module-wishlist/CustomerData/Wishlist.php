<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Wishlist section
 */
class Wishlist implements SectionSourceInterface {

    /**
     * @var string
     */
    const SIDEBAR_ITEMS_NUMBER = 3;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var \Magento\Wishlist\Block\Customer\Sidebar
     */
    protected $block;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Sidebar $block
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \Magento\Framework\App\ViewInterface $view
     */
    public function __construct(
    \Magento\Wishlist\Helper\Data $wishlistHelper, \Magento\Wishlist\Block\Customer\Sidebar $block, \Magento\Catalog\Helper\ImageFactory $imageHelperFactory, \Magento\Framework\App\ViewInterface $view, \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->block = $block;
        $this->view = $view;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData() {
        $counter = $this->getCounter();
        return [
            'counter' => $counter,
            'items' => $counter ? $this->getItems() : [],
        ];
    }

    /**
     * @return string
     */
    protected function getCounter() {
        return $this->createCounter($this->wishlistHelper->getItemCount());
    }

    /**
     * Create button label based on wishlist item quantity
     *
     * @param int $count
     * @return \Magento\Framework\Phrase|null
     */
    protected function createCounter($count) {
        if ($count > 1) {
            return __('%1 items', $count);
        } elseif ($count == 1) {
            return __('1 item');
        }
        return null;
    }

    /**
     * Get wishlist items
     *
     * @return array
     */
    protected function getItems() {
        $this->view->loadLayout();

        $collection = $this->wishlistHelper->getWishlistItemCollection();
        $collection->clear()->setPageSize(self::SIDEBAR_ITEMS_NUMBER)
                ->setInStockFilter(true)->setOrder('added_at');
        $items = [];
        foreach ($collection as $wishlistItem) {
            $items[] = $this->getItemData($wishlistItem);
        }
//        print_r($items);die();
        return $items;
    }

    /**
     * Retrieve wishlist item data
     *
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @return array
     */
    protected function getItemData(\Magento\Wishlist\Model\Item $wishlistItem) {
        $product = $wishlistItem->getProduct();
        $productName = $product->getName();
        $artworkName = explode(":", $productName);

        $artworkName = trim($artworkName[0]);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productObj = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
        $artistId = $productObj->getData("yourcustomattribute_id");
        $artist = $objectManager->get('Magento\Customer\Model\Customer')->load($artistId);
//        var_dump($artistId);
//        die();
        $fromArtist = $artist->getData('firstname') . " " . $artist->getData('lastname');
        return [
            'image' => $this->getImageData($product),
            'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
            'artist_name' => $fromArtist,
            'product_name' => $artworkName,
            'product_price' => $this->block->getProductPriceHtml(
                    $product, 'wishlist_configured_price', \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST, ['item' => $wishlistItem]
            ),
            'product_is_saleable_and_visible' => $product->isSaleable() && $product->isVisibleInSiteVisibility(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem, true),
            'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem, true),
        ];
    }

    /**
     * Retrieve product image data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Block\Product\Image
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getImageData($product) {
        /** @var \Magento\Catalog\Helper\Image $helper */
        $helper = $this->imageHelperFactory->create()
                ->init($product, 'wishlist_sidebar_block');

        $template = $helper->getFrame() ? 'Magento_Catalog/product/image' : 'Magento_Catalog/product/image_with_borders';

        $imagesize = $helper->getResizedImageInfo();

        $width = $helper->getFrame() ? $helper->getWidth() : (!empty($imagesize[0]) ? $imagesize[0] : $helper->getWidth());

        $height = $helper->getFrame() ? $helper->getHeight() : (!empty($imagesize[1]) ? $imagesize[1] : $helper->getHeight());

        return [
            'template' => $template,
            'src' => $helper->getUrl(),
            'width' => $width,
            'height' => $height,
            'alt' => $helper->getLabel(),
        ];
    }

}
