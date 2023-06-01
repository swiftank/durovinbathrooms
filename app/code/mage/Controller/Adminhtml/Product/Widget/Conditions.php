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

namespace Magezon\TabsPro\Controller\Adminhtml\Product\Widget;

use Magento\Rule\Model\Condition\AbstractCondition;

class Conditions extends \Magento\CatalogWidget\Controller\Adminhtml\Product\Widget
{
    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    protected $rule;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\CatalogWidget\Model\Rule $rule
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\CatalogWidget\Model\Rule $rule
    ) {
        $this->rule = $rule;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeData = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $className = $typeData[0];
        $prefix = $this->getRequest()->getParam('form');
        $model = $this->_objectManager->create($className)
            ->setId($id)
            ->setType($className)
            ->setRule($this->rule)
            ->setPrefix($prefix)
            ->setData($prefix, []); 

        if (!empty($typeData[1])) {
            $model->setAttribute($typeData[1]);
        }

        $result = '';
        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $result = $model->asHtmlRecursive();
        }
        $this->getResponse()->setBody($result);
    }
}
