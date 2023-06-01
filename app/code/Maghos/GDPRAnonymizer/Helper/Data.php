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
namespace Maghos\Gdpr\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_GDPR_ENABLE = 'maghosgdpr/general/enable';
    const XML_PATH_GDPR_REPLACE_TEXT = 'maghosgdpr/general/replace_text';


    const XML_PATH_GDPR_TEMPLATE = 'maghosgdpr/general/template';
    const XML_PATH_GDPR_GUEST_TEMPLATE = 'maghosgdpr/general/guest_template';

    const XML_PATH_EMAIL_NAME = 'trans_email/ident_sales/name';
    const XML_PATH_EMAIL_EMAIL = 'trans_email/ident_sales/email';

    const XML_PATH_CRYPT_KEY = 'crypt/key';

    /** @var \Magento\Framework\App\DeploymentConfig */
    private $deploymentConfig;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig
    ) {
        parent::__construct($context);
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Get crypt key.
     *
     * @return mixed|null
     */
    public function getCryptKey()
    {
        return $this->deploymentConfig->get(self::XML_PATH_CRYPT_KEY);
    }

    /**
     * @param null $scopeCode
     * @return bool
     */
    public function isEnabled($scopeCode = null)
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_GDPR_ENABLE, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    /**
     * @param null $scopeCode
     * @return string
     */
    public function getReplaceText($scopeCode = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GDPR_REPLACE_TEXT, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    /**
     * @param null $scopeCode
     * @return string
     */
    public function getStoreName($scopeCode = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_NAME, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    /**
     * @param null $scopeCode
     * @return string
     */
    public function getStoreEmail($scopeCode = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_EMAIL, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    /**
     * @param null $scopeCode
     * @return string
     */
    public function getEmailTemplate($scopeCode = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GDPR_TEMPLATE, ScopeInterface::SCOPE_STORE,
            $scopeCode);
    }

    /**
     * @param null $scopeCode
     * @return string
     */
    public function getGuestEmailTemplate($scopeCode = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GDPR_GUEST_TEMPLATE, ScopeInterface::SCOPE_STORE,
            $scopeCode);
    }
}
