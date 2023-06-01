<?php

namespace Etatvasoft\ProductLabel\Helper;

/**
 * Class ProductLabelHelper
 * @package Etatvasoft\ProductLabel\Helper
 */
class ProductLabelHelper extends \Magento\Framework\Url\Helper\Data
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductLabelHelper constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductLabel($product)
    {
        if (!empty($product->getData('product_label'))) {
            return $product->getData('product_label');
        } else {
            return "";
        }
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductLabelColor($product)
    {
        if (!empty($product->getData('product_label_color'))) {
            return $product->getData('product_label_color');
        } else {
            return "";
        }
    }

    public function getProductLabelBackgroundColor($product)
    {
        if (!empty($product->getData('product_label_background_color'))) {
            return $product->getData('product_label_background_color');
        } else {
            return "";
        }
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductLabelShape($product)
    {
        if (!empty($product->getData('product_label_shape'))) {
            return $product->getData('product_label_shape');
        } else {
            return "";
        }
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductLabelImage($product)
    {
        if (!empty($product->getData('product_label_image'))) {
            return $product->getData('product_label_image');
        } else {
            return "";
        }
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductMediaUrl()
    {
        $mediaUrl = $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl."catalog/product/file/";
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductLabelType($product)
    {
        if (!empty($product->getData('product_label_select'))) {
            return $product->getData('product_label_select');
        } else {
            return "";
        }
    }
}
