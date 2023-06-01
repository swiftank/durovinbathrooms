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

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;

class Mailer
{

    /** @var \Magento\Framework\Mail\Template\TransportBuilder */
    private $transportBuilder;

    /** @var \Magento\Framework\Escaper */
    private $escaper;

    /** @var \Maghos\Gdpr\Model\Validation */
    private $validation;

    /** @var \Maghos\Gdpr\Helper\Data */
    private $helper;

    /** @var \Magento\Customer\Model\Session */
    private $customerSession;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    private $customerRepository;

    /** @var \Magento\Framework\UrlInterface */
    private $urlBuilder;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    /**
     * Mailer constructor.
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Escaper $escaper
     * @param \Maghos\Gdpr\Model\Validation $validation
     * @param \Maghos\Gdpr\Helper\Data $helper
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        UrlInterface $urlBuilder,
        \Magento\Framework\Escaper $escaper,
        \Maghos\Gdpr\Model\Validation $validation,
        \Maghos\Gdpr\Helper\Data $helper,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->transportBuilder   = $transportBuilder;
        $this->escaper            = $escaper;
        $this->validation         = $validation;
        $this->helper             = $helper;
        $this->customerSession    = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->urlBuilder         = $urlBuilder;
        $this->storeManager       = $storeManager;
    }

    /**
     * Send email confirmation email to user with valid customer account.
     *
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendAccountEmail()
    {
        $customer  = $this->customerSession->getCustomer();
        $hash      = $this->validation->getHash($customer->getId());
        $link      = $this->urlBuilder->getUrl(
            'gdpr/account/confirm',
            ['customer_id' => $customer->getId(), 'hash' => $hash]
        );
        $data      = [
            'customer_id' => $customer->getId(),
            'link' => $link
        ];
        $sender    = [
            'name' => $this->helper->getStoreName(),
            'email' => $this->helper->getStoreEmail(),
        ];
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($this->helper->getEmailTemplate())
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($data)
            ->setFrom($sender)
            ->addTo($customer->getEmail())
            ->getTransport();

        $transport->sendMessage();
    }

    /**
     * Send email with confirmation.
     *
     * @param $email
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendPersonalEmail($email)
    {
        if ($this->checkIfAccountExists($email)) {
            throw new LocalizedException(__('There is active account connected to this email. Please login first.'));
        }

        $hash = $this->validation->getHash($email);
        $link = $this->urlBuilder->getUrl('gdpr/delete/confirm', ['email' => $email, 'hash' => $hash]);
        $data = [
            'email' => $this->escaper->escapeHtml($email),
            'link' => $link
        ];

        $sender = [
            'name' => $this->helper->getStoreName(),
            'email' => $this->helper->getStoreEmail(),
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($this->helper->getGuestEmailTemplate())
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($data)
            ->setFrom($sender)
            ->addTo($email)
            ->getTransport();

        $transport->sendMessage();
    }

    /**
     * Check if account with this email exists.
     *
     * @param $email
     * @return bool
     */
    private function checkIfAccountExists($email)
    {
        try {
            $this->customerRepository->get($email);

            return true;
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
