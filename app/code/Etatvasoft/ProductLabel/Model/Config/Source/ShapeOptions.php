<?php

namespace Etatvasoft\ProductLabel\Model\Config\Source;

/**
 * Class ShapeOptions
 * @package Etatvasoft\ProductLabel\Model\Config\Source
 */
class ShapeOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label' => __('Select Shape'), 'value'=>''],
            ['label' => __('Rectangle'), 'value'=>'rectangle'],
            ['label' => __('Oval'), 'value'=>'oval']
        ];
        return $this->_options;
    }
}
