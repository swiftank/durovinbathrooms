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

namespace Magezon\TabsPro\Block;

class Tab extends \Magento\Catalog\Block\Product\AbstractProduct
{

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var string
     */
    protected $productFormKey;

    /**
     * @param \Magento\Catalog\Block\Product\Context                         $context 
     * @param \Magento\Framework\App\Http\Context                            $httpContext   
     * @param \Magento\Cms\Model\Template\FilterProvider                     $filterProvider           
     * @param \Magezon\TabsPro\Model\TabFactory                              $tabFactory               
     * @param array                                                          $data                     
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magezon\TabsPro\Model\TabFactory $tabFactory,
        array $data = []
    ) {
        $this->httpContext              = $httpContext;
        $this->_filterProvider          = $filterProvider;
        $this->tabFactory               = $tabFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('tab.phtml');
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
            'TABSPRO_TAB',
                $this->_storeManager->getStore()->getId(),
                $this->_design->getDesignTheme()->getId(),
                $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
                'tabid'    => $this->getTabProId(),
                'blockid'  => $this->getTabBlockId()
        ];
        return $cache;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
    	$tab = $this->getTabPro();
        if (!$tab && $this->getTabProId()) {
            $tab = $this->tabFactory->create();
            $tab->load($this->getTabProId());
            if (!$tab->getId()) {
                return [];
            }
            $this->setTabPro($tab);
        } 
    	if ($this->getData('template')) {
    		$this->setTemplate($this->getData('template'));	
    	}
        return parent::_beforeToHtml();
    }

	public function getProductCollection($blockId, $type, $_tab)
    {
        $tab = $this->getTabPro();

        $collection      = $tab->getProductCollection();
        $relatedProducts = (array) $tab->getRelatedProducts();
        $relatedProducts = array_reverse($relatedProducts);
        $items           = [];
        usort($relatedProducts, function($a, $b) {
            return $a['position'] - $b['position'];
        });
        
        $tmpItems = [];
        foreach ($relatedProducts as $k => $v) {
            if ($v['block_id'] == $blockId && $v['type'] == $type) {
                foreach ($collection as $_item) {
                    if ($v['product_id'] == $_item->getId()) {
                        $items[] = $_item;
                        $tmpItems[] = $_item->getData();
                    }
                }
            }
        }

        if (isset($_tab[$type . '_order_by'])) {
            $order = $_tab[$type . '_order_by'];
            if ($order!='default') {
                switch ($order) {
                    case 'alphabetically':
                        usort($tmpItems, function($a, $b) {
                            return $a['name'] > $b['name'];
                        });
                        break;

                    case 'price_low_to_high':
                        usort($tmpItems, function($a, $b) {
                            return $a['price'] > $b['price'];
                        });
                        break;

                    case 'price_high_to_low':
                        usort($tmpItems, function($a, $b) {
                            return $a['price'] < $b['price'];
                        });
                        break;

                    case 'random':
                        shuffle($tmpItems);
                        break;

                    case 'newestfirst':
                        usort($tmpItems, function($a, $b) {
                            return (int) $a['entity_id'] < (int) $b['entity_id'];
                        });
                        break;
                }               
            }

            $newItems = [];
            foreach ($tmpItems as $_item) {
                foreach ($items as $item) {
                    if ($item->getId() == $_item['entity_id']) {
                        $newItems[] = $item;
                    }
                }
            } 
            $items = $newItems;
        }
        return $items;
    }

    public function filter($str)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        return $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($str);
    }

    public function setProductFormKey($formKey)
    {
        $this->productFormKey = $formKey;
        return $this;
    }

    public function getProductFormKey()
    {
        return $this->productFormKey;
    }

    public function getProductListHtml($tabPro, $_tab, $data = [])
    {
        $data['product_form_key'] = $this->getProductFormKey();
    	$block = $this->getLayout()->createBlock('Magezon\TabsPro\Block\ProductsList');

        $tab = $this->getTabPro();
        if ($tab->getData('product_template')) {
            $block->setTemplate($tab->getData('product_template'));   
        }

        if ($productTemplate = $this->getData('product_template')) {
            $block->setTemplate($productTemplate);
        }

        foreach ($data as $k => $v) {
            $block->setData($k, $v);
        }
        $block->setTabContainerId($this->getTabContainerId())->setTabPro($tabPro);
    	return $block->toHtml();
    }
}
