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

namespace Magezon\TabsPro\Block\Adminhtml\Tab\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mgz-tabs');
        $this->setDestElementId('edit_form');
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label'   => __('General Information'),
                'title'   => __('General Information'),
                'content' => $this->getLayout()->createBlock('Magezon\TabsPro\Block\Adminhtml\Tab\Edit\Tab\Main')->toHtml(),
                'active'  => true,
            ]
        );

        $this->addTab(
            'tab_items',
            [
                'label'   => __('Manage Tabs'),
                'title'   => __('Manage Tabs'),
                'content' => $this->getLayout()->createBlock('Magezon\TabsPro\Block\Adminhtml\Tab\Edit\Tab\Items')->toHtml()
            ]
        );

        $this->addTab(
            'conditions',
            [
                'label'   => __('Display Conditions'),
                'title'   => __('Display Conditions'),
                'content' => $this->getLayout()->createBlock('Magezon\TabsPro\Block\Adminhtml\Tab\Edit\Tab\Conditions')->toHtml()
            ]
        );

        return parent::_beforeToHtml();
    }
}
