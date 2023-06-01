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
namespace Maghos\Gdpr\Controller\Delete;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Maghos\Gdpr\Model\AccountRemover;
use Psr\Log\LoggerInterface;

class Delete extends \Magento\Framework\App\Action\Action
{

    /** @var \Maghos\Gdpr\Helper\Data */
    private $helper;

    /** @var \Maghos\Gdpr\Model\AccountRemover */
    private $accountRemover;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * Delete constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Maghos\Gdpr\Helper\Data $helper
     * @param \Maghos\Gdpr\Model\AccountRemover $accountRemover
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Maghos\Gdpr\Helper\Data $helper,
        AccountRemover $accountRemover,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->helper         = $helper;
        $this->accountRemover = $accountRemover;
        $this->logger         = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->helper->isEnabled()) {
            return $redirect->setPath('/');
        }

        $email = $this->getRequest()->getParam('email');
        $hash  = $this->getRequest()->getParam('hash');

        try {
            $this->accountRemover->removePersonalInformation($email, $hash);
            $this->messageManager->addSuccessMessage(__('All your personal information was deleted.'));
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete account GDPR', [$e]);
            $this->messageManager->addErrorMessage(
                __('Error occurred. Please try again later or contact site administrator.')
            );
        }

        return $redirect->setPath('*/*/');
    }
}
