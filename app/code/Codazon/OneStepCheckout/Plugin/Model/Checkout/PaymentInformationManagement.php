<?php
/**
 * Copyright © 2017 RohitKundale. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\OneStepCheckout\Plugin\Model\Checkout;

use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class PaymentInformationManagement
 *
 * @package RohitKundale_OrderComment
 */
class PaymentInformationManagement
{
    /**
     * @var \Magento\Sales\Model\Order\Status\HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     * PaymentInformationManagement constructor.
     *
     * @param \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory
    )
    {
        $this->_jsonHelper = $jsonHelper;
        $this->_filterManager = $filterManager;
        $this->historyFactory = $historyFactory;
        $this->orderFactory = $orderFactory;
        $this->quoteRepository = $quoteRepository;
        $this->quoteAddressFactory = $quoteAddressFactory;
    }

    public function aroundPlaceOrder(
        \Magento\Quote\Model\QuoteManagement $subject,
        \Closure $proceed,
        $cartId,
        PaymentInterface $paymentMethod = null
    )
    {
        $comment = '';
        $requestBody = file_get_contents('php://input');
        // decode JSON post data into array
        $data = $this->_jsonHelper->jsonDecode($requestBody);

        if (isset ($data['comments'])) {
            // make sure there is a comment to save
            if ($data['comments']) {
                // remove any HTML tags
                $comment = $this->_filterManager->stripTags($data['comments']);
                $comment = __('Order Comment: ') . $comment;
            }
        }

        if (isset ($data['shippingAddress'])) {
            $address  = $this->quoteAddressFactory->create();
            $address->setData($data['shippingAddress']);
            $quote = $this->quoteRepository->getActive($cartId);
            $quote->setShippingAddress($address);
            try {
                //$quote->getShippingAddress()->setCollectShippingRates(true);
                $this->quoteRepository->save($quote);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__("The shippingaddress can't be save."));
            }
        }

        $orderId = $proceed($cartId, $paymentMethod);
        if (isset($comment)) {
            /** @param \Magento\Sales\Model\OrderFactory $order */
            $order = $this->orderFactory->create()->load($orderId);
            // make sure $order is exists
            if ($order->getEntityId()) {
                /** @param string $status */
                $status = $order->getStatus();

                /** @param \Magento\Sales\Model\Order\Status\HistoryFactory $history */
                $history = $this->historyFactory->create();
                // set comment history data
                $history->setComment($comment);
                $history->setParentId($orderId);
                $history->setIsVisibleOnFront(1);
                $history->setIsCustomerNotified(0);
                $history->setEntityName('order');
                $history->setStatus($status);
                $history->save();

                /*if (isset ($data['shippingAddress'])) {
                    $order->setShippingAddress($data['shippingAddress']);
                    $order->save();
                }*/
            }
        }
        
        return $orderId;
    }
}
