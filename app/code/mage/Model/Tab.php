<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_TabsPro
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */

namespace Magezon\TabsPro\Model;

class Tab extends \Magento\Rule\Model\AbstractModel
{
    /**#@+
     * Tab's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * POSITIONS
     */
    const BLOCK_PAGE_TOP              = 'page.top';
    const BLOCK_TOP_CONTAINER         = 'top.container';
    const BLOCK_COLUMNS_TOP           = 'columns.top';
    const BLOCK_PAGE_BOTTOM           = 'page.bottom';
    const BLOCK_PAGE_BOTTOM_CONTAINER = 'page.bottom.container';
    const BLOCK_CONTENT               = 'content';
    const BLOCK_CONTENT_ASIDE         = 'content.aside';
    const BLOCK_CONTENT_BOTTOM        = 'content.bottom';
    const BLOCK_MAIN                  = 'main';
    const BLOCK_CONTENT_TOP           = 'content.top';
    const BLOCK_BEFORE_BODY_END       = 'before.body.end';
    const BLOCK_FOOTER                = 'footer';
    const BLOCK_FOOTER_CONTAINER      = 'footer-container';
    const BLOCK_HEADER_WRAPPER        = 'header-wrapper';
    const BLOCK_HEADER_CONTAINER      = 'header.container';
    const BLOCK_HEADER_PANEL          = 'header.panel';
    const BLOCK_AFTER_BODY_START      = 'after.body.start';
    const BLOCK_SIDEBAR_ADDITIONAL    = 'sidebar.additional';
    const BLOCK_SIDEBAR_MAIN          = 'sidebar.main';

    /**
     * TabsPro tab cache tag
     */
    const CACHE_TAG = 'tabspro_tab';

    /**
     * @var string
     */
    protected $_cacheTag = 'tabspro_tab';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'tabspro_tab';

    /**
     * @var array
     */
    protected $_productIds;

    protected $_collection;

    protected $_tabProducts;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = []
        ) {
        $this->_combineFactory          = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
        $this->jsonDecoder              = $jsonDecoder;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->_catalogConfig           = $catalogConfig;
        $this->_storeManager            = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
            );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magezon\TabsPro\Model\ResourceModel\Tab');
    }

    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    public function getBlockPositions()
    {
        return [
            self::BLOCK_PAGE_TOP              => __('After Page Header'),
            self::BLOCK_TOP_CONTAINER         => __('After Page Header Top'),
            self::BLOCK_COLUMNS_TOP           => __('Before Main Columns'),
            self::BLOCK_PAGE_BOTTOM           => __('Before Page Footer'),
            self::BLOCK_PAGE_BOTTOM_CONTAINER => __('Before Page Footer Container'),
            self::BLOCK_CONTENT               => __('Main Content Area'),
            self::BLOCK_CONTENT_ASIDE         => __('Main Content Aside'),
            self::BLOCK_CONTENT_BOTTOM        => __('Main Content Bottom'),
            self::BLOCK_MAIN                  => __('Main Content Container'),
            self::BLOCK_CONTENT_TOP           => __('Main Content Top'),
            self::BLOCK_BEFORE_BODY_END       => __('Page Bottom'),
            self::BLOCK_FOOTER                => __('Page Footer'),
            self::BLOCK_FOOTER_CONTAINER      => __('Page Footer Container'),
            self::BLOCK_HEADER_WRAPPER        => __('Page Header'),
            self::BLOCK_HEADER_CONTAINER      => __('Page Header Container'),
            self::BLOCK_HEADER_PANEL          => __('Page Header Panel'),
            self::BLOCK_AFTER_BODY_START      => __('Page Top'),
            self::BLOCK_SIDEBAR_ADDITIONAL    => __('Sidebar Additional'),
            self::BLOCK_SIDEBAR_MAIN          => __('Sidebar Main')
        ];
    }

    /**
     * Getter for rule conditions collection
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    public function getTabs()
    {
        $tabs = [];
        if ($this->getData('tabs')) {
            $tabs = base64_decode($this->getData('tabs'));
            if ($tabs) {
                $tabs = $this->jsonDecoder->decode($tabs);
            }
        }
        return $tabs;
    }

    public function getRelatedProducts()
    {
        if ($this->_tabProducts === null) {
            $store = $this->_storeManager->getStore();
            $connection = $this->_getResource()->getConnection();
            $select     = $connection->select()->from($this->_getResource()
            ->getTable('mgz_tabspro_tab_product'))
            ->where('tab_id = ' . $this->getId())
            ->where('store_id = ' . $store->getId());
            $result     = (array) $connection->fetchAll($select);
            $this->_tabProducts = $result;
        }
        return $this->_tabProducts;
    }

    public function getProductIds($type = '')
    {
        if ($this->_productIds === null) {
            $result = $this->getRelatedProducts();
            $productIds = [];
            foreach ($result as $row) {
                $productIds[] = $row['product_id'];
            }
            $this->_productIds = $productIds;
        }
        return $this->_productIds;
    }

    public function getProductCollection()
    {
        if ($this->_collection === null) {
            $store = $this->_storeManager->getStore();
            $connection = $this->_getResource()->getConnection();
            $select     = $connection->select()->from($this->_getResource()
            ->getTable('mgz_tabspro_tab_product'))
            ->where('tab_id = ' . $this->getId())
            ->where('store_id = ' . $store->getId());
            $result     = (array) $connection->fetchAll($select);
            $productIds = [];
            foreach ($result as $row) {
                if (!in_array($row['product_id'], $productIds)) {
                    $productIds[] = $row['product_id'];
                }
            }

            $collection = $this->productCollectionFactory->create();
            $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
            $collection = $this->_addProductAttributesAndPrices($collection);
            $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * Add all attributes and apply pricing logic to products collection
     * to get correct values in different products lists.
     * E.g. crosssells, upsells, new products, recently viewed
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _addProductAttributesAndPrices(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
        ) {
        return $collection
        ->addMinimalPrice()
        ->addFinalPrice()
        ->addTaxPercents()
        ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
        ->addUrlRewrite();
    }
}