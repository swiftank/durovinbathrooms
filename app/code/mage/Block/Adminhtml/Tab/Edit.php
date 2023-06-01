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

namespace Magezon\TabsPro\Block\Adminhtml\Tab;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context  
     * @param \Magento\Framework\Registry           $registry 
     * @param array                                 $data     
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId   = 'tab_id';
        $this->_blockGroup = 'Magezon_TabsPro';
        $this->_controller = 'adminhtml_Tab';

        parent::_construct();

        if ($this->_isAllowedAction('Magezon_TabsPro::tab_save')) {

            $this->buttonList->add(
                'saveandapply',
                [
                    'label' => __('Save and Apply'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['target' => '#edit_form'],
                        ],
                    ]
                ],
                1000
            );
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                1000
            );
        }
        if ($this->_isAllowedAction('Magezon_TabsPro::tab_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Tab'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $tab = $this->_coreRegistry->registry('tabspro_tab');
        if ($tab->getId()) {
            return __("Edit Tab '%1'", $this->escapeHtml($tab->getName()));
        } else {
            return __('New Tab');
        }
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

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }
        /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {

        $this->_formScripts[] = "
        require([
        'jquery',
        'mage/backend/form'
        ], function($){
            $('#saveandapply').click(function(){
                var url = $('#edit_form').attr('action');
                $('#edit_form').attr('action',url + 'apply/1');
                $('#edit_form').submit();
            });
        });";
        return parent::_prepareLayout();
    }
}