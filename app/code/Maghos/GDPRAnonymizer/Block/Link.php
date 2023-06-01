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
namespace Maghos\Gdpr\Block;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{

    /** @var \Maghos\Gdpr\Helper\Data */
    private $helper;

    /**
     * Link constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Maghos\Gdpr\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Maghos\Gdpr\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->helper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
