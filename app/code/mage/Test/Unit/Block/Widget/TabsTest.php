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

namespace Magezon\TabsPro\Test\Unit\Block\Widget;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class TabTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->design       = $this->getMock('\Magento\Framework\View\DesignInterface');
        $this->httpContext  = $this->getMock('Magento\Framework\App\Http\Context');

        $this->collectionFactory = $this->getMockBuilder('Magezon\TabsPro\Model\ResourceModel\Tab\CollectionFactory')
        ->setMethods(['create'])
        ->disableOriginalConstructor()->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            'Magento\CatalogWidget\Block\Product\ProductsList',
            [
                'tabCollectionFactory' => $this->collectionFactory,
                'httpContext'          => $this->httpContext,
                'storeManager'         => $this->storeManager,
                'design'               => $this->design
            ]
        );

        $this->request = $arguments['context']->getRequest();
        $this->layout  = $arguments['context']->getLayout();

        $this->productsList = $objectManagerHelper->getObject(
            'Magezon\TabsPro\Block\Widget\Tab',
            $arguments
        );
        $this->productsList->setData('tab_id', 1);
    }

    public function testGetCacheKeyInfo()
    {
        $store = $this->getMockBuilder('\Magento\Store\Model\Store')->disableOriginalConstructor()->setMethods(['getId'])->getMock();
        $store->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        
        $theme = $this->getMock('\Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getId')->willReturn('blank');
        $this->design->expects($this->once())->method('getDesignTheme')->willReturn($theme);
        $this->httpContext->expects($this->once())->method('getValue')->willReturn('context_group');

        $cacheKey = [
            'TABSPRO_TAB_WIDGET',
            1,
            'blank',
            'context_group',
            1
        ];
        $this->assertEquals($cacheKey, $this->productsList->getCacheKeyInfo());
    }
}