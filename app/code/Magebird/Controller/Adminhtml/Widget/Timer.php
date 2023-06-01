<?php

namespace Magebird\Popup\Controller\Adminhtml\Widget;

class Timer extends \Magento\Backend\App\Action
{

    protected $resultRawFactory;

    protected $layoutFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    public function execute()
    {
        $layout = $this->layoutFactory->create();
        $html = $layout->createBlock('Magebird\Popup\Block\Adminhtml\Widget\Timers')
          ->setTemplate('Magebird_Popup::widget/timer.phtml')
          ->toHtml();
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($html);
    }
}
