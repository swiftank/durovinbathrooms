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

namespace Magezon\TabsPro\Block\Page;

class Tabs extends \Magento\Framework\View\Element\Template
{
    /**
     * Storage for used widgets
     *
     * @var array
     */
    protected static $_widgetUsageMap = [];

    protected $tab;

    /**
     * @var \Magezon\TabsPro\Model\ResourceModel\Tab\CollectionFactory
     */
    protected $tabCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magezon\TabsPro\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context           $context              
     * @param \Magezon\TabsPro\Model\ResourceModel\Tab\CollectionFactory $tabCollectionFactory 
     * @param \Magento\Customer\Model\Session                            $customerSession      
     * @param \Magento\Framework\Registry                                $registry             
     * @param \Magento\Framework\App\ResourceConnection                  $resource             
     * @param \Magezon\TabsPro\Helper\Data                               $dataHelper           
     * @param array                                                      $data                 
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magezon\TabsPro\Model\ResourceModel\Tab\CollectionFactory $tabCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magezon\TabsPro\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context);
        $this->tabCollectionFactory = $tabCollectionFactory;
        $this->customerSession      = $customerSession;
        $this->_coreRegistry        = $registry;
        $this->_resource            = $resource;
        $this->httpContext          = $httpContext;
        $this->dataHelper           = $dataHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags'     => [\Magezon\TabsPro\Model\Tab::CACHE_TAG]
        ]);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $product = $this->getProduct();
        $cache = [
            'TABSPRO_TAB_RELATED_PRODUCTS',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            $this->getBlockType(),
            $product->getId()
        ];
        return $cache;
    }

    /**
     * Produce and return block's html output
     *
     * This method should not be overridden. You can override _toHtml() method in descendants if needed.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->dataHelper->getConfig('general/enable')) {
            return;
        }
        $storeId    = $this->_storeManager->getStore()->getId();
        $product    = $this->getProduct();
        $groupId    = $this->customerSession->getCustomerGroupId();
        $_store     = $this->_storeManager->getStore();
        $blockType  = $this->getBlockType();
        $collection = $this->tabCollectionFactory->create();
        $collection->addFieldToFilter('main_table.position', $blockType)
        ->addFieldToFilter('is_active', \Magezon\TabsPro\Model\Tab::STATUS_ENABLED)
        ->addStoreFilter($_store)
        ->addCustomerGroupFilter($groupId)
        ->setOrder('priority', 'ASC');

        $collection->getSelect()
        ->joinLeft(
            [
            'sb' => $this->_resource->getTableName('mgz_tabspro_tab_product'),
                [
                    'displaytype'  => 'type',
                    'displaystore' => 'store_id',
                    'displayposition' => 'position'
                ]
            ],
            'sb.tab_id = main_table.tab_id'
          )
        ->where('sb.product_id = ?', $product->getId())
        ->where('sb.store_id = ?', $storeId)
        ->where('sb.type = "display"')
        ->group('main_table.tab_id');

        $html  = '';
        $block = $this->getLayout()->createBlock('\Magezon\TabsPro\Block\Widget\Tab');
        foreach ($collection as $tab) {
            $html .= $block->setTab($tab)->setTabId($tab->getId())->toHtml();
        }
        return $html;
    }

    public function getTabHtml($tabId, $_tab, $tabPro)
    {
        $block = $this->getLayout()->createBlock('Magezon\TabsPro\Block\Tab')
        ->setWidgetId($widgetId)
        ->setTabBlockId($tabId)
        ->setTabPro($tabPro)
        ->setTabProId($tabPro->getId())
        ->setTab($_tab);
        return $block->toHtml();
    }

    public function getTab()
    {
        return $this->tab;
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
        return $this;
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }
}