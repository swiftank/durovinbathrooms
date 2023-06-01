<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_TabsPro
 * @copyright Copyright (C) 2018 Magezon (https://magezon.com)
 */

namespace Magezon\TabsPro\Model\Config\Source;

class Website implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Lof\Formbuilder\Model\Form $collectionFactory 
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    public function toOptionArray()
    {
        $result = [];
        $websites = $this->_storeManager->getWebsites();

        foreach ($websites as $website) {
            $result[] = [
                'label' => $website->getName(),
                'value' => $website->getId(),
            ];
        }
        return $result;
    }
}