<?php
/**
 *
 * Maghos_Gdpr Magento 2 extension
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @category Maghos
 * @package Maghos_Gdpr
 * @copyright Copyright (c) 2018 Maghos s.r.o.
 * @license http://www.maghos.com/business-license
 * @author Magento dev team <support@maghos.com>
 */
namespace Maghos\Gdpr\Model;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Api\FilterBuilder;

class DataRemover
{

    /** @var \Magento\Quote\Model\QuoteRepository */
    private $quoteRepository;

    /** @var \Magento\Framework\Api\Search\SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var \Maghos\Gdpr\Helper\Data */
    private $helper;

    /** @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory */
    private $orderCollectionFactory;

    /** @var \Magento\Framework\Api\FilterBuilder */
    private $filterBuilder;

    /** @var \Magento\Framework\DB\TransactionFactory */
    private $transactionFactory;

    /** @var \Magento\Framework\App\ResourceConnection */
    private $resourceConnection;

    /** @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory */
    private $subscriberCollectionFactory;

    /**
     * DataRemover constructor.
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Maghos\Gdpr\Helper\Data $helper
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollectionFactory
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Maghos\Gdpr\Helper\Data $helper,
        FilterBuilder $filterBuilder,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollectionFactory
    ) {
        $this->quoteRepository        = $quoteRepository;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->helper                 = $helper;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->filterBuilder          = $filterBuilder;
        $this->transactionFactory     = $transactionFactory;
        $this->resourceConnection     = $resourceConnection;
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
    }

    /**
     * Remove data for specific customer.
     *
     * @param $customerId
     * @throws \Exception
     */
    public function removeDataForCustomer($customerId)
    {
        $this->removeData('customer_id', $customerId, 'customer_id', $customerId);
    }

    /**
     * Remove data for email address.
     *
     * @param $email
     * @throws \Exception
     */
    public function removeDataForEmail($email)
    {
        $this->removeData('customer_email', $email, 'customer_email', $email);
        $this->removeNewsletterData($email);
    }

    /**
     * Remove newsletter data.
     *
     * @param $email
     */
    private function removeNewsletterData($email)
    {
        $subscribers = $this->subscriberCollectionFactory
            ->create()
            ->addFieldToFilter('subscriber_email', ['eq' => $email]);

        foreach ($subscribers as $subscriber) {
            $subscriber->delete();
        }
    }

    /**
     * @param $quoteFilterName
     * @param $quoteFilterValue
     * @param $orderFilterName
     * @param $orderFilterValue
     * @throws \Exception
     */
    private function removeData($quoteFilterName, $quoteFilterValue, $orderFilterName, $orderFilterValue)
    {
        $deleteTransaction = $this->transactionFactory->create();
        $saveTransaction   = $this->transactionFactory->create();

        $filter = $this->filterBuilder
            ->setField($quoteFilterName)
            ->setConditionType('eq')
            ->setValue($quoteFilterValue)
            ->create();

        $this->searchCriteriaBuilder->addSortOrder('entity_id', AbstractCollection::SORT_ORDER_ASC);
        $this->searchCriteriaBuilder->addFilter($filter);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $quotes = $this->quoteRepository->getList($searchCriteria)->getItems();

        //quotes and quote addresses
        foreach ($quotes as $quote) {
            $deleteTransaction->addObject($quote);
        }

        //sales orders
        $orders = $this->orderCollectionFactory
            ->create()
            ->addFieldToFilter($orderFilterName, $orderFilterValue);

        $anonimizedValue = $this->helper->getReplaceText();
        $anonimizedEmail = 'anon@ymized.com';

        $orderIds = [];
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        foreach ($orders as $order) {
            $orderIds[] = $order->getId();

            $billing  = $order->getBillingAddress();
            $shipping = $order->getShippingAddress();

            $this->anonymizeFields($order, 'customer_email', $anonimizedEmail);
            $this->anonymizeFields($order, ['customer_firstname', 'customer_lastname'], $anonimizedValue);
            $this->anonymizeFields($order, ['customer_middlename', 'customer_prefix', 'customer_suffix', 'remote_ip'], null);

            $addressEntities = [$billing];

            if (!$order->getIsVirtual()) {
                $addressEntities[] = $shipping;
            }

            $this->anonymizeFields(
                $addressEntities,
                ['postcode', 'lastname', 'street', 'city', 'telephone', 'firstname'],
                $anonimizedValue
            );
            $this->anonymizeFields(
                $addressEntities,
                'email',
                $anonimizedEmail
            );
            $this->anonymizeFields(
                $addressEntities,
                ['region', 'region_id', 'fax', 'country_id', 'prefix', 'middlename', 'suffix', 'company', 'vat_id'],
                null
            );

            foreach ($addressEntities as $entity) {
                $saveTransaction->addObject($entity);
            }

            $saveTransaction->addObject($order);
        }

        $saveTransaction->save();
        $deleteTransaction->delete();

        //anonymize data in grids
        $this->updateDataInTable('sales_order_grid', 'entity_id', $orderIds, [
            'shipping_name' => $anonimizedValue,
            'billing_name' => $anonimizedValue,
            'billing_address' => $anonimizedValue,
            'shipping_address' => $anonimizedValue,
            'customer_email' => $anonimizedEmail,
            'customer_name' => $anonimizedValue,
        ]);

        $this->updateDataInTable('sales_invoice_grid', 'order_id', $orderIds, [
            'billing_name' => $anonimizedValue,
            'billing_address' => $anonimizedValue,
            'shipping_address' => $anonimizedValue,
            'customer_email' => $anonimizedEmail,
            'customer_name' => $anonimizedValue,
        ]);

        $this->updateDataInTable('sales_shipment_grid', 'order_id', $orderIds, [
            'shipping_name' => $anonimizedValue,
            'billing_name' => $anonimizedValue,
            'billing_address' => $anonimizedValue,
            'shipping_address' => $anonimizedValue,
            'customer_email' => $anonimizedEmail,
            'customer_name' => $anonimizedValue,
        ]);

        $this->updateDataInTable('sales_creditmemo_grid', 'order_id', $orderIds, [
            'billing_name' => $anonimizedValue,
            'billing_address' => $anonimizedValue,
            'shipping_address' => $anonimizedValue,
            'customer_email' => $anonimizedEmail,
            'customer_name' => $anonimizedValue,
        ]);

        $this->updateDataInTable('sales_order_payment', 'parent_id', $orderIds, [
            'cc_exp_month' => null,
            'echeck_bank_name' => null,
            'cc_last_4' => null,
            'cc_owner' => null,
            'po_number' => null,
            'cc_exp_year' => null,
            'echeck_routing_number' => null,
            'cc_debug_response_body' => null,
            'echeck_account_name' => null,
            'cc_number_enc' => null,
            'additional_information' => null
        ]);
    }

    /**
     * Update any table.
     *
     * @param $table
     * @param $idField
     * @param $ids
     * @param $data
     */
    public function updateDataInTable($table, $idField, $ids, $data)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName  = $this->resourceConnection->getTableName($table);

        $connection->update(
            $tableName,
            $data,
            [$idField . ' IN (?)' => $ids]
        );
    }

    /**
     * Change fields in entity.
     *
     * @param array|string $entities
     * @param array|string $fields
     * @param string $value
     */
    private function anonymizeFields($entities, $fields, $value)
    {
        if (!is_array($entities)) {
            $entities = [$entities];
        }
        if (!is_array($fields)) {
            $fields = [$fields];
        }

        foreach ($entities as $entity) {
            foreach ($fields as $field) {
                $entity->setData($field, $value);
            }
        }
    }
}
