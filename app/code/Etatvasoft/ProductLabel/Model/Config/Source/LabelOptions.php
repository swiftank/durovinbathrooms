<?php

namespace Etatvasoft\ProductLabel\Model\Config\Source;

/**
 * Class LabelOptions
 * @package Etatvasoft\ProductLabel\Model\Config\Source
 */
class LabelOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label' => __('Please Select'), 'value'=>'none'],
            ['label' => __('Add Label By Image'), 'value'=>'image'],
            ['label' => __('Add Label By Text'), 'value'=>'text']
        ];
        return $this->_options;
    }
}
