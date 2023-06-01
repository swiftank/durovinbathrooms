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

class Processor
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    protected $sqlBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Reports\Model\Event\TypeFactory
     */
    protected $_eventTypeFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager             
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory 
     * @param \Magento\Catalog\Model\Product\Visibility                      $catalogProductVisibility 
     * @param \Magento\Catalog\Model\Config                                  $catalogConfig            
     * @param \Magento\CatalogWidget\Model\Rule                              $rule                     
     * @param \Magento\Rule\Model\Condition\Sql\Builder                      $sqlBuilder               
     * @param \Magento\Framework\App\ResourceConnection                      $resource                 
     * @param \Magento\Reports\Model\Event\TypeFactory                       $eventTypeFactory         
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface           $localeDate               
     * @param \Magento\Catalog\Model\ResourceModel\Product                   $product                  
     * @param \Magento\Store\Model\System\Store                              $systemStore              
     */
    public function __construct(
    	\Magento\Store\Model\StoreManagerInterface $storeManager,
    	\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    	\Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
    	\Magento\Catalog\Model\Config $catalogConfig,
    	\Magento\CatalogWidget\Model\Rule $rule,
    	\Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
    	\Magento\Framework\App\ResourceConnection $resource,
    	\Magento\Reports\Model\Event\TypeFactory $eventTypeFactory,
    	\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
    	\Magento\Catalog\Model\ResourceModel\Product $product,
    	\Magento\Store\Model\System\Store $systemStore
    	) {
    	$this->_storeManager            = $storeManager;
    	$this->_catalogConfig           = $catalogConfig;
    	$this->sqlBuilder               = $sqlBuilder;
    	$this->productCollectionFactory = $productCollectionFactory;
    	$this->catalogProductVisibility = $catalogProductVisibility;
    	$this->rule                     = $rule;
    	$this->_resource                = $resource;
    	$this->_localeDate              = $localeDate;
    	$this->_eventTypeFactory        = $eventTypeFactory;
    	$this->_systemStore             = $systemStore;
    }

    public function process(\Magezon\TabsPro\Model\Tab $tab)
    {
    	$storeIds = (array) $tab->getStoreId();
    	if (!empty($storeIds)) {
    		if (in_array(0, $storeIds)) {
    			$stores = $this->_systemStore->getStoreValuesForForm();
    			foreach ($stores as $store) {
    				if (is_array($store['value']) && !empty($store['value'])) {
    					foreach ($store['value'] as $_store) {
    						$store = $this->_storeManager->getStore($_store['value']);
    						$this->__processBystore($tab, $store);
    					}						
    				}
    			}
    		} else {
    			foreach ($storeIds as $storeId) {
    				$store = $this->_storeManager->getStore($storeId);
    				$this->__processBystore($tab, $store);
    			}
    		}		
    	}
    }

    protected function getProductByConditions($conditions, $store, $numberItems = '', $order = 'newestfirst', $source = 'latest')
    {
    	$collection = $this->productCollectionFactory->create();
    	$collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
    	$collection = $this->_addProductAttributesAndPrices($collection)->addStoreFilter($store);
    	$this->rule->loadPost(['conditions' => $conditions]);
    	$conditions = $this->rule->getConditions();
    	$conditions->collectValidatedAttributes($collection);
    	$this->sqlBuilder->attachConditionToCollection($collection, $conditions);
    	if ((int) $numberItems) {
    		$collection->setPageSize($numberItems);
    	}	
    	$collection->addAttributeToSelect('*');

    	switch ($source) {
    		case 'latest':
    		$collection->getSelect()->order('created_at DESC');
            break;

    		case 'new':
    		$this->_getNewProductCollection($collection);
    		break;

    		case 'bestseller':
    		$this->_getBestSellerProductCollection($collection, $store->getId());
    		break;

    		case 'onsale':
    		$this->_getOnsaleProductCollection($collection, $store->getId());
    		break;

    		case 'mostviewed':
    		$this->_getMostViewedProductCollection($collection, $store->getId());
    		break;

    		case 'wishlisttop':
    		$this->_getWishlisttopProductCollection($collection, $store->getId());
    		break;

    		case 'free':
    		$collection->addAttributeToFilter('price', ['eq' => 0]);
    		break;

    		case 'featured':
    		$collection->addAttributeToFilter('featured', ['eq' => 1]);
    		break;

    		case 'toprated':
    		$this->_getTopRatedProductCollection($collection, $store->getId());
    		break;
    	}

    	return $collection;
    }

    protected function _getTopRatedProductCollection($collection, $storeId)
    {
    	$collection->joinField(
    		'review',
    		$this->_resource->getTableName('review_entity_summary'),
    		'reviews_count',
    		'entity_pk_value=entity_id',
    		'at_review.store_id=' . (int) $storeId,
    		'review > 0',
    		'left'
    		);
    	$collection->getSelect()->order(['review DESC', 'e.created_at']);
    }

    protected function _getNewProductCollection($collection)
    {
    	$todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
    	$todayEndOfDayDate   = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

    	$collection->addAttributeToFilter(
    		'news_from_date',
    		[
    		'or' => [
	    		0 => ['date' => true, 'to' => $todayEndOfDayDate],
	    		1 => ['is' => new \Zend_Db_Expr('null')],
    		]
    		],
    		'left'
    		)->addAttributeToFilter(
    		'news_to_date',
    		[
	    		'or' => [
		    		0 => ['date' => true, 'from' => $todayStartOfDayDate],
		    		1 => ['is' => new \Zend_Db_Expr('null')],
	    		]
    		],
    		'left'
    		)->addAttributeToFilter(
    		[
	    		['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
	    		['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
    		]
    		)->addAttributeToSort(
    		'news_from_date',
    		'desc'
    		);
    	}

    	protected function _getBestSellerProductCollection($collection, $storeId)
    	{
    		$collection->getSelect()
    		->join(
    			[
    			'aggregation' => $this->_resource->getTableName('sales_bestsellers_aggregated_monthly'),
    			],
    			"e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND qty_ordered >0",
    			[	
    			'sold_quantity' => 'SUM(aggregation.qty_ordered)'
    			]
    			)
    		->group('e.entity_id')
    		->order(['sold_quantity DESC', 'e.created_at']);
    	}

    	protected function _getWishlisttopProductCollection($collection, $storeId)
    	{
    		$eventTypes = $this->_eventTypeFactory->create()->getCollection();
    		foreach ($eventTypes as $eventType) {
    			if ($eventType->getEventName() == 'wishlist_add_product') {
    				$wishlistEvent = (int)$eventType->getId();
    				break;
    			}
    		}

    		$collection->getSelect()
    		->join(
    			[
    			'report_table_views' => $this->_resource->getTableName('report_event'),
    			],
    			"e.entity_id = report_table_views.object_id AND report_table_views.event_type_id = " . $wishlistEvent,
    			[
    			'views' => 'COUNT(report_table_views.event_id)'
    			]
    		)
    		->group('e.entity_id')
    		->order(['views DESC'])
    		->having(
    			'COUNT(report_table_views.event_id) > ?',
    			0
    			);
    	}

    	protected function _getMostViewedProductCollection($collection, $storeId)
    	{
    		$eventTypes = $this->_eventTypeFactory->create()->getCollection();
    		foreach ($eventTypes as $eventType) {
    			if ($eventType->getEventName() == 'catalog_product_view') {
    				$productViewEvent = (int)$eventType->getId();
    				break;
    			}
    		}

    		$collection->getSelect()
    		->join(
    			[
    			'report_table_views' => $this->_resource->getTableName('report_event'),
    			],
    			"e.entity_id = report_table_views.object_id AND report_table_views.event_type_id = " . $productViewEvent,
    			[
    			'views' => 'COUNT(report_table_views.event_id)'
    			]
    			)
    		->group('e.entity_id')
    		->order(['views DESC', 'e.created_at'])
    		->having(
    			'COUNT(report_table_views.event_id) > ?',
    			0
    			);
    	}

    	protected function _getOnsaleProductCollection($collection, $storeId)
    	{
    		$todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
    		$todayEndOfDayDate   = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

    		$collection->addAttributeToFilter(
    			'special_from_date',
    			[
	    			'or' => [
		    			0 => ['date' => true, 'to' => $todayEndOfDayDate],
		    			1 => ['is' => new \Zend_Db_Expr('null')],
	    			]
    			],
    			'left'
    			)->addAttributeToFilter(
    			'special_to_date',
    			[
	    			'or' => [
		    			0 => ['date' => true, 'from' => $todayStartOfDayDate],
		    			1 => ['is' => new \Zend_Db_Expr('null')],
	    			]
    			],
    			'left'
    			)->addAttributeToFilter(
    			[
	    			['attribute' => 'special_from_date', 'is' => new \Zend_Db_Expr('not null')],
	    			['attribute' => 'special_to_date', 'is' => new \Zend_Db_Expr('not null')],
    			]
    			)->addAttributeToSort(
	    			'special_from_date',
	    			'desc'
    			);

    			$collection->getSelect()->where('final_price < price');
    		}

    		public function __processBystore($tab, $store)
    		{
    			$tabs = (array) $tab->getTabs();
    			if (!empty($tabs)) {
    				foreach ($tabs as $tabId => $_tab) {
    					if (!isset($_tab['conditions'])) {
    						continue;
    					}
    					$conditions = (array) $_tab['conditions'];
    					if (isset($conditions[$tabId . '_top' ]) && isset($_tab['top_enable']) && $_tab['top_enable'] == 'on' && $_tab['top_type'] == 'condition') {
    						$collection = $this->getProductByConditions($conditions[$tabId . '_top'], $store, $_tab['top_number_products'], $_tab['top_order_by'], $_tab['top_source']);
    						$this->_saveTabRuleProduct($tab, 'top', $collection, $tabId, $store);
    					}
    					if (isset($conditions[$tabId . '_left' ]) && isset($_tab['left_enable']) && $_tab['left_enable'] == 'on' && $_tab['left_type'] == 'condition') {
    						$collection = $this->getProductByConditions($conditions[$tabId . '_left'], $store, $_tab['left_number_products'], $_tab['left_order_by'], $_tab['left_source']);
    						$this->_saveTabRuleProduct($tab, 'left', $collection, $tabId, $store);
    					}
    					if (isset($conditions[$tabId . '_maincontent' ]) && isset($_tab['maincontent_enable']) && $_tab['maincontent_enable'] == 'on' && $_tab['maincontent_type'] == 'condition') {
    						$collection = $this->getProductByConditions($conditions[$tabId . '_maincontent'], $store, $_tab['maincontent_number_products'], $_tab['maincontent_order_by'], $_tab['maincontent_source']);
    						$this->_saveTabRuleProduct($tab, 'maincontent', $collection, $tabId, $store);
    					}
    					if (isset($conditions[$tabId . '_right' ]) && isset($_tab['right_enable']) && $_tab['right_enable'] == 'on' && $_tab['right_type'] == 'condition') {
    						$collection = $this->getProductByConditions($conditions[$tabId . '_right'], $store, $_tab['right_number_products'], $_tab['right_order_by'], $_tab['right_source']);
    						$this->_saveTabRuleProduct($tab, 'right', $collection, $tabId, $store);
    					}
    					if (isset($conditions[$tabId . '_bottom' ]) && isset($_tab['bottom_enable']) && $_tab['bottom_enable'] == 'on' && $_tab['bottom_type'] == 'condition') {
    						$collection = $this->getProductByConditions($conditions[$tabId . '_bottom'], $store, $_tab['bottom_number_products'], $_tab['bottom_order_by'], $_tab['bottom_source']);
    						$this->_saveTabRuleProduct($tab, 'bottom', $collection, $tabId, $store);
    					}
    				} 

                    if ($tab->getData('show_productpage')) {
    				    $collection = $this->_getMatchingProductCollection($tab, $store);
    				    $this->_saveTabRuleProduct($tab, 'display', $collection, $tab->getId(), $store);
                    } else {
                        $table      = $this->_resource->getTableName('mgz_tabspro_tab_product');
                        $connection = $this->_resource->getConnection();
                        $where = ['tab_id = ?' => $tab->getId(), 'type = ?' => 'display'];
                        $connection->delete($table, $where);
                    }
    			}
    		}

    		public function _getMatchingProductCollection($tab, $store)
    		{
    			$collection = $this->productCollectionFactory->create();
    			$collection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
    			$collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
    			$collection = $this->_addProductAttributesAndPrices($collection)->addStoreFilter($store); 
    			$conditions = $tab->getConditions();
    			$conditions->collectValidatedAttributes($collection);
    			$this->sqlBuilder->attachConditionToCollection($collection, $conditions); 
    			return $collection;
    		}

    		protected function _saveTabRuleProduct($tab, $type, $collection, $blockId, $store)
    		{
				$tabId      = $tab->getId();
				$table      = $this->_resource->getTableName('mgz_tabspro_tab_product');
				$connection = $this->_resource->getConnection();
				$newRecords = [];
				$items      = $collection->getItems();
    			foreach ($items as $_item) {
    				$newRecords[] = $_item->getId();
    			}

    			$where = ['tab_id = ?' => $tabId, 'type = ?' => $type, 'block_id = ?' => $blockId, 'store_id = ?' => $store->getId()];
    			$connection->delete($table, $where);

    			if ($newRecords) {
    				$data = [];
    				$x = 0;
					foreach ($newRecords as $productId) {
						$data[] = [
    						'tab_id'     => $tab->getId(),
    						'block_id'   => $blockId,
    						'type'       => $type,
    						'product_id' => $productId,
    						'position'   => $x,
    						'store_id'   => $store->getId()
						];
						$x++;
					}
    				$connection->insertMultiple($table, $data);
    			}
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