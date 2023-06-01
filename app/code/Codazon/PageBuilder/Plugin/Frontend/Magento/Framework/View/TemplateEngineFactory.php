<?php


namespace Codazon\PageBuilder\Plugin\Frontend\Magento\Framework\View;

use Magento\Developer\Helper\Data as DevHelper;
use Codazon\PageBuilder\Model\TemplateEngine\Decorator\DebugHintsFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\TemplateEngineFactory as EngineFactory;
use Magento\Framework\View\TemplateEngineInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;

class TemplateEngineFactory
{

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DevHelper $devHelper,
        DebugHintsFactory $debugHintsFactory,
        Http $http = null,
        $debugHintsWithParam = null,
        $debugHintsParameter = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->devHelper = $devHelper;
        $this->debugHintsFactory = $debugHintsFactory;
        $this->http = $http ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\App\Request\Http::class
        );
        $this->debugHintsWithParam = $debugHintsWithParam;
        $this->debugHintsParameter = $debugHintsParameter;
    }

    /**
     * Wrap template engine instance with the debugging hints decorator, depending of the store configuration
     *
     * @param TemplateEngineFactory $subject
     * @param TemplateEngineInterface $invocationResult
     *
     * @return TemplateEngineInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreate(
        EngineFactory $subject,
        TemplateEngineInterface $invocationResult
    ) {
        $storeCode = $this->storeManager->getStore()->getCode();
        $showHints = false;
        if ($showHints) {
            $showBlockHints = false;
            echo "<h1>test</h1>";
            /*return $this->debugHintsFactory->create([
                'subject' => $invocationResult,
                'showBlockHints' => $showBlockHints,
            ]);*/
        }
        return $invocationResult;
    }
}

