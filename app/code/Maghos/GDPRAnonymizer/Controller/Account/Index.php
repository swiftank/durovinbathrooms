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

class Index extends \Magento\Framework\App\Action\Action
{

    /** @var \Maghos\Gdpr\Helper\Data */
    private $helper;

    /** @var \Magento\Customer\Model\Session */
    private $customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Maghos\Gdpr\Helper\Data $helper
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Maghos\Gdpr\Helper\Data $helper,
        \Magento\Customer\Model\Session\Proxy $customerSession
    ) {
        parent::__construct($context);

        $this->helper          = $helper;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->helper->isEnabled() || !$this->customerSession->isLoggedIn()) {
            return $this->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT)
                ->setPath('/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Request account deletion'));

        return $resultPage;
    }
}
