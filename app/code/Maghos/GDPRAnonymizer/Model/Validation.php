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

use Maghos\Gdpr\Helper\Data;

class Validation
{

    /** @var \Magento\Framework\Encryption\EncryptorInterface */
    private $encryptor;

    /** @var \Maghos\Gdpr\Helper\Data */
    private $helper;

    /**
     * Validation constructor.
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Maghos\Gdpr\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        Data $helper
    ) {
        $this->encryptor = $encryptor;
        $this->helper    = $helper;
    }

    /**
     * Generate hash.
     *
     * @param $text
     * @return string
     */
    public function getHash($text)
    {
        return $this->encryptor->hash($text . $this->helper->getCryptKey());
    }

    /**
     * Validate hash.
     *
     * @param $text
     * @param $hash
     * @return bool
     * @throws \Exception
     */
    public function verify($text, $hash)
    {
        return $this->encryptor->validateHash($text . $this->helper->getCryptKey(), $hash);
    }
}
