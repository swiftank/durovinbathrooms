<?php
declare(strict_types=1);

namespace Codazon\OneStepCheckout\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout as Layout;
use Magento\Framework\View\Layout\ProcessorInterface as LayoutProcessor;

/**
 *  AddCategoryLayoutUpdateHandleObserver
 */
class AddCustomCheckoutHandleObserver implements ObserverInterface
{

    public function __construct(\Codazon\OneStepCheckout\Helper\Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var Event $event */
        $event = $observer->getEvent();
        $actionName = $event->getData('full_action_name');

        if (
            $this->config->isEnabledOneStep() &&
            $actionName === 'checkout_index_index'
        ) {
            /** @var Layout $layout */
            $layout = $event->getData('layout');

            /** @var LayoutProcessor $layoutUpdate */
            $layoutUpdate = $layout->getUpdate();

            $layoutUpdate->addPageHandles(['opccodazon']);
        }
    }
}