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

class Confirm extends \Magento\Framework\App\Action\Action
{

    /** @var \Maghos\Gdpr\Model\Validation */
    private $validation;

    /** @var \Maghos\Gdpr\Helper\Data */
    private $helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Maghos\Gdpr\Model\Validation $validation
     * @param \Maghos\Gdpr\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Maghos\Gdpr\Model\Validation $validation,
        \Maghos\Gdpr\Helper\Data $helper
    ) {
        parent::__construct($context);

        $this->validation = $validation;
        $this->helper     = $helper;
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

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Confirm account and data deletion'));

        $customerId = $this->getRequest()->getParam('customer_id');
        $hash       = $this->getRequest()->getParam('hash');
        try {
            if (!$customerId || !$hash || !$this->validation->verify($customerId, $hash)) {
                $this->messageManager->addErrorMessage(__('Missing or wrong parameters, please try again.'));
                return $redirect;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Missing or wrong parameters, please try again.'));
        }

        return $resultPage;
    }
}
