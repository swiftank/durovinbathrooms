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

namespace Magezon\TabsPro\Controller\Ajax;

class Tab extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magezon\TabsPro\Model\TabFactory
     */
    protected $tabFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @param \Magento\Framework\App\Action\Context            $context           
     * @param \Magento\Framework\View\Result\PageFactory       $resultPageFactory 
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory 
     * @param \Magezon\TabsPro\Model\TabFactory                $tabFactory        
     */
    public function __construct(
    	\Magento\Framework\App\Action\Context $context,
    	\Magento\Framework\View\Result\PageFactory $resultPageFactory,
    	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    	\Magezon\TabsPro\Model\TabFactory $tabFactory,
        \Magento\Framework\Data\Form\FormKey $formKey
    	) {
    	parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->tabFactory        = $tabFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKey           = $formKey;
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
    	$response['status'] = false;
    	$response = [];
    	$post = $this->getRequest()->getPostValue();
    	if ($post && isset($post['tab_id']) && $post['tab_id'] && isset($post['block_id']) && $post['block_id']) {
    		try {
                $resultPage           = $this->resultPageFactory->create();
                $blockId              = $post['block_id'];
                $response['block_id'] = '#' . $blockId;
                $tabId                = $post['tab_id'];

                $formKey = $this->formKey->getFormKey();
                $block = $resultPage->getLayout()->createBlock('Magezon\TabsPro\Block\Tab')
                ->setProductFormKey($formKey)
                ->setTabProId($tabId)
                ->setTabBlockId($blockId);
                $html = $block->toHtml();
                $response['status']   = true;
                $response['html']     = $html;
    		} catch (\Exception $e) {
    			$response['status'] = false;
    		}
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
    	} else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl('/');
            return $resultRedirect;
        }
    	
    }
}