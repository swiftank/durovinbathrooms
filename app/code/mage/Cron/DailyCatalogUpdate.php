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

namespace Magezon\TabsPro\Cron;

class DailyCatalogUpdate
{
    /**
     * @var \Magezon\TabsPro\Model\ResourceModel\Tab\CollectionFactory
     */
    protected $_tabCollectionFactory;

    /**
     * @var \Magezon\TabsPro\Model\Processor
     */
    protected $_processor;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magezon\TabsPro\Model\ResourceModel\Tab\CollectionFactory $tabCollectionFactory 
     * @param \Magezon\TabsPro\Model\Processor                           $processor            
     * @param \Magento\Store\Model\App\Emulation                         $appEmulation         
     * @param \Magento\Store\Model\System\Store                          $systemStore          
     * @param \Magento\Store\Model\StoreManagerInterface                 $storeManager         
     */
	public function __construct(
		\Magezon\TabsPro\Model\ResourceModel\Tab\CollectionFactory $tabCollectionFactory,
		\Magezon\TabsPro\Model\Processor $processor,
		\Magento\Store\Model\App\Emulation $appEmulation,
    	\Magento\Store\Model\System\Store $systemStore,
    	\Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		$this->_tabCollectionFactory = $tabCollectionFactory;
		$this->_processor            = $processor;
		$this->_appEmulation         = $appEmulation;
		$this->_systemStore          = $systemStore;
		$this->_storeManager         = $storeManager;
	}

    /**
     * Run process send product alerts
     *s
     * @return $this
     */
    public function process()
    {
    	$collection = $this->_tabCollectionFactory->create();
    	foreach ($collection as $tab) {
    		$storeIds = (array) $tab->getStoreId();
    		if (!empty($storeIds)) {
    			if (in_array(0, $storeIds)) {
    				$stores = $this->_systemStore->getStoreValuesForForm();
    				foreach ($stores as $store) {
    					if (is_array($store['value']) && !empty($store['value'])) {
    						foreach ($store['value'] as $_store) {
    							$store = $this->_storeManager->getStore($_store['value']);
    							$this->_appEmulation->startEnvironmentEmulation($_store['value']);
    							$this->_processor->__processBystore($tab, $store);
    							$this->_appEmulation->stopEnvironmentEmulation();
    						}						
    					}
    				}
    			} else {
    				foreach ($storeIds as $storeId) {
    					$store = $this->_storeManager->getStore($storeId);
    					$this->_processor->__processBystore($tab, $store);
    				}
    			}		
    		}
    	}
    }
}