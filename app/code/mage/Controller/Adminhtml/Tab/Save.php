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

namespace Magezon\TabsPro\Controller\Adminhtml\Tab;

use Magento\Backend\App\Action;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_TabsPro::tab_save';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magezon\TabsPro\Model\Processor
     */
    protected $processor;

    /**
     * @param Action\Context                           $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder      
     * @param \Magezon\TabsPro\Model\Processor         $processor        
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magezon\TabsPro\Model\Processor $processor
        ) {
        parent::__construct($context);
        $this->jsonEncoder      = $jsonEncoder;
        $this->processor        = $processor;
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $parameters = $data['parameters'];

            $tabs = [];
            if (isset($parameters['tabs'])) {
                $tabs = $parameters['tabs'];
            }

            foreach ($tabs as $k => &$_tab) {
                if (isset($parameters[$k . '_top'])) {
                    $_tab['conditions'][$k . '_top'] = $parameters[$k . '_top'];
                }
                if (isset($parameters[$k . '_left'])) {
                    $_tab['conditions'][$k . '_left'] = $parameters[$k . '_left'];
                }
                if (isset($parameters[$k . '_maincontent'])) {
                    $_tab['conditions'][$k . '_maincontent'] = $parameters[$k . '_maincontent'];
                }
                if (isset($parameters[$k . '_right'])) {
                    $_tab['conditions'][$k . '_right'] = $parameters[$k . '_right'];
                }
                if (isset($parameters[$k . '_bottom'])) {
                    $_tab['conditions'][$k . '_bottom'] = $parameters[$k . '_bottom'];
                }
            }
            $data['tabs'] = base64_encode($this->jsonEncoder->encode($tabs));

            /** @var \Magento\Cms\Model\Page $model */
            $model = $this->_objectManager->create('Magezon\TabsPro\Model\Tab');

            $id = $this->getRequest()->getParam('tab_id');
            if ($id) {
                $model->load($id);
            }

            if (isset($data['rule'])) {
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
            }
            $model->loadPost($data);

            try {
                $model->save();

                $this->messageManager->addSuccess(__('You saved the tab.'));

                if ($this->getRequest()->getParam('apply')) {
                    $this->processor->process($model);
                    return $resultRedirect->setPath('*/*/edit', ['tab_id' => $model->getId(), '_current' => true]);
                }
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['tab_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the tab.'));
            }

            return $resultRedirect->setPath('*/*/edit', ['tab_id' => $this->getRequest()->getParam('tab_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
