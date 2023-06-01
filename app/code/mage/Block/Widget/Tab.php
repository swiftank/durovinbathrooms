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

namespace Magezon\TabsPro\Block\Widget;

class Tab extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Storage for used widgets
     *
     * @var array
     */
    protected static $_widgetUsageMap = [];

    /**
     * @var \Magezon\TabsPro\Model\Tab
     */
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
     * @var \Magezon\TabsPro\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context           $context           
     * @param \Magezon\TabsPro\Model\ResourceModel\Tab\CollectionFactory $tabCollectionFactory 
     * @param \Magento\Customer\Model\Session                            $customerSession      
     * @param \Magezon\TabsPro\Helper\Data                               $dataHelper           
     * @param \Magento\Framework\App\Http\Context                        $httpContext          
     * @param array                                                      $data                 
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magezon\TabsPro\Model\ResourceModel\Tab\CollectionFactory $tabCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magezon\TabsPro\Helper\Data $dataHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
        ) {
        parent::__construct($context, $data);
        $this->tabCollectionFactory = $tabCollectionFactory;
        $this->customerSession      = $customerSession;
        $this->dataHelper           = $dataHelper;
        $this->httpContext          = $httpContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $template = $this->getData('custom_template');
        if (!$template) {
            $template = 'tabs.phtml';
        }
        $this->setTemplate($template);
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags'     => [\Magento\Catalog\Model\Product::CACHE_TAG
        ], ]);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cache = [
            'TABSPRO_TAB_WIDGET',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            $this->getData('tab_id')
        ];
        return $cache;
    }
    /**
     * Prepare block text and determine whether block output enabled or not
     * Prevent blocks recursion if needed
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $tabId     = $this->getData('tab_id');
        $blockHash = get_class($this) . $tabId;
        if (isset(self::$_widgetUsageMap[$blockHash])) {
            return $this;
        }
        self::$_widgetUsageMap[$blockHash] = true;
        unset(self::$_widgetUsageMap[$blockHash]);
        return $this;
    }

    /**
     * Produce and return block's html output
     *
     * This method should not be overridden. You can override _toHtml() method in descendants if needed.
     *
     * @return string
     */
    public function toHtml()
    {
        if (!$this->dataHelper->getConfig('general/enable')) {
            return;
        }
        $tabId = $this->getData('tab_id');
        if ($tabId && !$this->getTab()) {
            $groupId    = $this->customerSession->getCustomerGroupId();
            $_store     = $this->_storeManager->getStore();
            $collection = $this->tabCollectionFactory->create();
            $collection->addFieldToFilter('is_active', \Magezon\TabsPro\Model\Tab::STATUS_ENABLED)
            ->addFieldToFilter('main_table.tab_id', $tabId)
            ->addStoreFilter($_store)
            ->addCustomerGroupFilter($groupId)
            ->setOrder('priority', 'ASC');
            $tab = $collection->getFirstItem();
            $this->setTab($tab);

            if (!$this->getTemplate() && $tab->getData('custom_template')) {
                $this->setTemplate($tab->getData('custom_template'));
            }
        }
        return parent::toHtml();
    }

    public function getTabHtml($tabId, $tabPro, $widgetId)
    {
        $block = $this->getLayout()->createBlock('Magezon\TabsPro\Block\Tab')
        ->setWidgetId($widgetId)
        ->setTabBlockId($tabId)
        ->setTabPro($tabPro)
        ->setTabProId($tabPro->getId());

        if ($productTemplate = $this->getData('product_template')) {
            $block->setData('product_template', $productTemplate);
        }

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
}
