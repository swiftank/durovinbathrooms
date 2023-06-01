<?php
/**
 * Copyright © 2015 Codazon . All rights reserved.
 */
namespace Product\Dropdown\Helper;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;

class ProductHelper extends \Magento\ConfigurableProduct\Helper\Data
{
    public function __construct(
        ImageHelper $imageHelper,
        UrlBuilder $urlBuilder = null,
        ScopeConfigInterface $scopeConfig = null
    ) {
        $this->scopeConfig = $scopeConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        parent::__construct($imageHelper, $urlBuilder, $scopeConfig);
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                if ($this->canDisplayShowOutOfStockStatus()) {
                    if ($product->isSalable()) {
                        $options['salable'][$productAttributeId][$attributeValue][] = $productId;
                    }
                    $options[$productAttributeId][$attributeValue][] = $productId;
                } else {
                    if ($product->isSalable()) {
                        $options[$productAttributeId][$attributeValue][] = $productId;
                    }
                }
                $options['index'][$productId][$productAttributeId] = $attributeValue;
            }
        }
        $options['canDisplayShowOutOfStockStatus'] = $this->canDisplayShowOutOfStockStatus();
        return $options;
    }

    /**
     * Returns if display out of stock status set or not in catalog inventory
     *
     * @return bool
     */
    private function canDisplayShowOutOfStockStatus(): bool
    {
        return (bool) false && $this->scopeConfig->getValue('cataloginventory/options/show_out_of_stock');
    }
}