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

namespace Maghos\Gdpr\Controller\Account;

use Magento\Framework\Controller\ResultFactory;
use Maghos\Gdpr\Model\AccountRemover;
use Psr\Log\LoggerInterface;

class Delete extends \Magento\Framework\App\Action\Action
{

    /** @var \Maghos\Gdpr\Model\AccountRemover */
    private $accountRemover;

    /** @var \Maghos\Gdpr\Helper\Data */
    private $helper;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \Magento\Customer\Model\Session */
    private $customerSession;

    /**
     * Delete constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Maghos\Gdpr\Helper\Data $helper
     * @param \Maghos\Gdpr\Model\AccountRemover $accountRemover
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Maghos\Gdpr\Helper\Data $helper,
        AccountRemover $accountRemover,
        LoggerInterface $logger,
        \Magento\Customer\Model\Session\Proxy $customerSession
    ) {
        $this->helper         = $helper;
        $this->accountRemover = $accountRemover;

        parent::__construct($context);
        $this->logger = $logger;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('/');

        if (!$this->helper->isEnabled()) {
            return $redirect;
        }

        try {
            $customerId = $this->getRequest()->getParam('customer_id');
            $hash       = $this->getRequest()->getParam('hash');

            $this->customerSession->logout()
                                  ->setBeforeAuthUrl($this->_redirect->getRefererUrl())
                                  ->setLastCustomerId($customerId);

            $this->accountRemover->removeCustomerAccount($customerId, $hash);

            $this->messageManager->addSuccessMessage(__('All your personal information was deleted.'));
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete account GDPR', [$e]);
            $this->messageManager->addErrorMessage(
                __('Error occurred. Please try again later or contact site administrator.')
            );
        }

        return $redirect;
    }
}
