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

namespace Magezon\TabsPro\Model\ResourceModel\Tab;

use Magento\Store\Model\Store;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'tab_id';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory 
     * @param \Psr\Log\LoggerInterface                                     $logger        
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy 
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager  
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager  
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection    
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null    $resource      
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magezon\TabsPro\Model\Tab', 'Magezon\TabsPro\Model\ResourceModel\Tab');
        $this->_map['fields']['store']         = 'store_table.store_id';
        $this->_map['fields']['customergroup'] = 'customergroup_table.customer_group_id';
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('mgz_tabspro_tab_store', 'tab_id');
        return parent::_afterLoad();
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function joinCustomerGroupRelationTable($alias, $tableName, $linkField)
    {
        if ($this->getFilter('customergroup')) {
            $this->getSelect()->join(
                [$alias => $this->getTable($tableName)],
                'main_table.' . $linkField . ' = ' . $alias . '.' . $linkField,
                []
            )->group(
                'main_table.' . $linkField
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function joinStoreRelationTable($alias, $tableName, $linkField)
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                [$alias => $this->getTable($tableName)],
                'main_table.' . $linkField . ' = ' . $alias . '.' . $linkField,
                []
            )->group(
                'main_table.' . $linkField
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('store_table', 'mgz_tabspro_tab_store', 'tab_id');
        $this->joinCustomerGroupRelationTable('customergroup_table', 'mgz_tabspro_tab_customergroup', 'tab_id');
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function performAfterLoad($tableName, $linkField)
    {
        $linkedIds = $this->getColumnValues($linkField);
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['mgz_tabspro_tab_store' => $this->getTable($tableName)])
                ->where('mgz_tabspro_tab_store.' . $linkField . ' IN (?)', $linkedIds);
            $result = $connection->fetchAll($select);
            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[$linkField]][] = $storeData['store_id'];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }
                    $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $storesData[$linkedId], true);
                    if ($storeIdKey !== false) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = current($storesData[$linkedId]);
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store_id', $storesData[$linkedId]);
                }
            }
        }
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->performAddStoreFilter($store, $withAdmin);
        return $this;
    }

    public function addCustomerGroupFilter($customerGroupId, $withAdmin = true)
    {
        $this->performAddCustomerGroupAfterLoad($customerGroupId, $withAdmin);
        return $this;
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store', ['in' => $store], 'public');
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddCustomerGroupAfterLoad($customerGroup)
    {
        if (!is_array($customerGroup)) {
            $customerGroup = [$customerGroup];
        }

        $this->addFilter('customergroup', ['in' => $customerGroup], 'public');
    }

    /**
     * Returns pairs identifier - title for unique identifiers
     * and pairs identifier|page_id - title for non-unique after first
     *
     * @return array
     */
    public function toOptionIdArray()
    {
        $res = [];
        $existingIdentifiers = [];
        foreach ($this as $item) {
            $data['value'] = $item->getData('tab_id');
            $data['label'] = $item->getData('name');
            $res[] = $data;
        }
        return $res;
    }
}