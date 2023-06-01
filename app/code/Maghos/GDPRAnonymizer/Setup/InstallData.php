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
namespace Maghos\Gdpr\Setup;

use Magento\Store\Model\Store;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{

    /** @var \Magento\Cms\Model\BlockFactory */
    private $blockFactory;

    /** @var \Magento\Cms\Api\BlockRepositoryInterface */
    private $blockRepository;

    /**
     * InstallData constructor.
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
    ) {
        $this->blockFactory    = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $this->addBlocks();
        $setup->endSetup();
    }

    /**
     * Add CMS blocks.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addBlocks()
    {
        $personalBlock = $this->blockFactory->create();
        $personalBlock->setData([
            'title' => 'Request personal information deletion',
            'identifier' => 'gdpr-info',
            'content' => '<p>Here you can request the deletion of all your personal information, even if you are not registered. Simply fill in your email address and we will send you a link by which you can delete all your personal information (name, email, address, etc.) that we have stored on our site.</p>
<p>If you do have an account, please log in and request an account deletion from your account menu instead.</p>
<p>You will still be able to make an RMA claim, but make sure to keep a copy of your invoices, since that will be the only way to verify your purchase.</p>
<p>For more information on customer\'s data protection, please visit the official <a href="https://www.eugdpr.org/" 
target="_blank">GDPR site</a>.</p>',
            'stores' => [Store::DEFAULT_STORE_ID],
            'is_active' => 1,
        ]);

        $this->blockRepository->save($personalBlock);

        $accountBlock = $this->blockFactory->create();
        $accountBlock->setData([
            'title' => 'Request account deletion',
            'identifier' => 'gdpr-info-customer',
            'content' => '<p>Here you can request the deletion of your account, including all of your personal information. For security reasons, we will send you a link by which you can delete your account and all related information.</p>
<p>This will delete your account and anonymize your personal information in orders and invoices.</p>
<p>You will still be able to make an RMA claim, but make sure to keen a copy of your invoices, since that will be the only way to verify your purchase.</p>
<p>For more information on customer\'s data protection, please visit the official <a href="https://www.eugdpr.org/" 
target="_blank">GDPR site</a>.</p>',
            'stores' => [Store::DEFAULT_STORE_ID],
            'is_active' => 1,
        ]);
        $this->blockRepository->save($accountBlock);
    }
}
