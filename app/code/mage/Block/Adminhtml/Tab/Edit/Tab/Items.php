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

namespace Magezon\TabsPro\Block\Adminhtml\Tab\Edit\Tab;

class Items extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('tabspro_tab');

        if ($this->_isAllowedAction('Magezon_TabsPro::tab_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('tab_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => '', 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('tab_id', 'hidden', ['name' => 'tab_id']);
        } 

        $field = $fieldset->addField(
            'tabs',
            'multiselect',
            [
                'name'     => 'tabs',
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $this->getLayout()->createBlock(
            'Magezon\TabsPro\Block\Adminhtml\Form\Renderer\TabManager'
        );
        $field->setRenderer($renderer);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
