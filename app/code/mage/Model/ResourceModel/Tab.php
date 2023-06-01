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

namespace Magezon\TabsPro\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Tab extends AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context 
     * @param \Magento\Framework\Stdlib\DateTime\DateTime       $date    
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
		\Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_date = $date;
        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
    	$this->_init('mgz_tabspro_tab', 'tab_id');
    }

    /**
     * Perform actions after object load
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
    	// STORE
    	$stores = $this->lookupStoreIds($object->getId());
        $object->setData('store_id', $stores);

    	// CUSTOMER GROUPS
        $groups = $this->lookupCustomerGroupIds($object->getId());
        $object->setData('customer_group_ids', $groups);
    	return parent::_afterLoad($object);
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
    	parent::_beforeSave($object);
    	$object->setUpdateTime($this->_date->gmtDate());
    	return $this;
    }
    /**
     * Perform operations after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
    	$this->_saveStore($object);
    	$this->_saveCustomerGroup($object);
    	return parent::_afterSave($object);
    }

    protected function _saveStore(\Magento\Framework\Model\AbstractModel $object)
    {
    	if ($object->getData('store_id')) {
            $oldStores = $this->lookupStoreIds($object->getId());
            $newStores = (array)$object->getStoreId();
            $table     = $this->getTable('mgz_tabspro_tab_store');
            $insert    = array_diff($newStores, $oldStores);
            $delete    = array_diff($oldStores, $newStores);
            if ($delete) {
                $where = ['tab_id = ?' => $object->getId(), 'store_id IN (?)' => $delete];
                $this->getConnection()->delete($table, $where);
            }
            if ($insert) {
                $data = [];
                foreach ($insert as $storeId) {
                    $data[] = ['tab_id' => $object->getId(), 'store_id' => (int)$storeId];
                }
                $this->getConnection()->insertMultiple($table, $data);
            }
        }
    	return $this;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('mgz_tabspro_tab_store'),
            'store_id'
        )->where(
            'tab_id = :tab_id'
        );

        $binds = [':tab_id' => (int)$id];

        return $connection->fetchCol($select, $binds);
    }

    protected function _saveCustomerGroup(\Magento\Framework\Model\AbstractModel $object)
    {
    	if ($object->getData('customer_group_ids')) {
			$oldCustomerGroups = $this->lookupCustomerGroupIds($object->getId());
			$newCustomerGroups = (array) $object->getData('customer_group_ids');
			$table             = $this->getTable('mgz_tabspro_tab_customergroup');
			$insert            = array_diff($newCustomerGroups, $oldCustomerGroups);
			$delete            = array_diff($oldCustomerGroups, $newCustomerGroups);
            if ($delete) {
                $where = ['tab_id = ?' => (int)$object->getId(), 'customer_group_id IN (?)' => $delete];
                $this->getConnection()->delete($table, $where);
            }
            if ($insert) {
                $data = [];
                foreach ($insert as $storeId) {
                    $data[] = ['tab_id' => (int)$object->getId(), 'customer_group_id' => (int)$storeId];
                }
                $this->getConnection()->insertMultiple($table, $data);
            }
    	}
    	return $this;
    }

    /**
     * Get customer group ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupCustomerGroupIds($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('mgz_tabspro_tab_customergroup'),
            'customer_group_id'
        )->where(
            'tab_id = :tab_id'
        );
        $binds = [':tab_id' => (int)$id];

        return $connection->fetchCol($select, $binds);
    }
}