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
use Maghos\Gdpr\Model\Mailer;
use Psr\Log\LoggerInterface;

class RequestEmail extends \Magento\Framework\App\Action\Action
{

    /** @var \Maghos\Gdpr\Helper\Data */
    private $helper;

    /** @var \Maghos\Gdpr\Model\Mailer */
    private $mailer;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * RequestEmail constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Maghos\Gdpr\Helper\Data $helper
     * @param \Maghos\Gdpr\Model\Mailer $mailer
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Maghos\Gdpr\Helper\Data $helper,
        Mailer $mailer,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->helper = $helper;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function execute()
    {
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->helper->isEnabled()) {
            return $redirect->setPath('/');
        }

        $email = $this->getRequest()->getParam('email');

        try {
            $this->mailer->sendPersonalEmail($email);
            $this->messageManager->addSuccessMessage(
                __('Verification link was sent to email address you just provided.')
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Sorry, we can\'t process your request right now. Please try again later')
            );
            $this->logger->error('Failed to delete account GDPR', [$e]);
        }

        return $redirect->setPath('*/*/');
    }
}
