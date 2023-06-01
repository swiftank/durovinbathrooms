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

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    /**
     * @param \Magento\Backend\Block\Template\Context        $context               
     * @param \Magento\Framework\Registry                    $registry              
     * @param \Magento\Store\Model\System\Store              $systemStore           
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository       
     * @param \Magento\Framework\Data\FormFactory            $formFactory           
     * @param \Magento\Framework\Api\SearchCriteriaBuilder   $searchCriteriaBuilder 
     * @param \Magento\Framework\Convert\DataObject          $objectConverter       
     * @param array                                          $data                  
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        array $data = []
    ) {
        $this->_systemStore           = $systemStore;
        $this->groupRepository        = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter       = $objectConverter;
        parent::__construct($context, $registry, $formFactory, $data);
    }

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

        $fieldset->addField(
            'name',
            'text',
            [
                'name'     => 'name',
                'label'    => __('Name'),
                'title'    => __('Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'custom_class',
            'text',
            [
                'name'     => 'custom_class',
                'label'    => __('Custom Class'),
                'title'    => __('Custom Class'),
                'disabled' => $isElementDisabled
            ]
        );

        $field = $fieldset->addField(
            'store_id',
            'multiselect',
            [
                'name'     => 'store_id',
                'label'    => __('Store View'),
                'title'    => __('Store View'),
                'required' => true,
                'values'   => $this->_systemStore->getStoreValuesForForm(false, true),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
        );
        $field->setRenderer($renderer);

        $groups = $this->groupRepository->getList($this->_searchCriteriaBuilder->create())
            ->getItems();
        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            [
                'name'     => 'customer_group_ids',
                'label'    => __('Customer Groups'),
                'title'    => __('Customer Groups'),
                'style'    => 'width: 198px;',
                'required' => true,
                'disabled' => $isElementDisabled,
                'values'   =>  $this->_objectConverter->toOptionArray($groups, 'id', 'code')
            ]
        );

        $fieldset->addField(
            'priority',
            'text',
            [
                'name'     => 'priority',
                'label'    => __('Priority'),
                'title'    => __('Priority'),
                'style'    => 'width: 198px;',
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label'    => __('Status'),
                'title'    => __('Status'),
                'name'     => 'is_active',
                'style'    => 'width: 198.75px;',
                'options'  => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'custom_template',
            'text',
            [
                'name'     => 'custom_template',
                'label'    => __('Custom Template'),
                'title'    => __('Custom Template'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'product_template',
            'text',
            [
                'name'     => 'product_template',
                'label'    => __('Product Template'),
                'title'    => __('Product Template'),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

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
