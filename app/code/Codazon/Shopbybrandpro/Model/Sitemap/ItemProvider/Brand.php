<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Model\Sitemap\ItemProvider;

use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
class Brand implements \Magento\Sitemap\Model\ItemProvider\ItemProviderInterface
{

    /**
     * Sitemap item factory
     *
     * @var SitemapItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * Config reader
     *
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * CategorySitemapItemResolver constructor.
     *
     * @param ConfigReaderInterface $configReader
     * @param CategoryFactory $categoryFactory
     * @param SitemapItemInterfaceFactory $itemFactory
     */
    public function __construct(
        \Codazon\Shopbybrandpro\Block\Widget\BrandList $brandList,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->brandList = $brandList;
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = $this->brandList->getBrandObject();

        $items = array_map(function ($item) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $item->getUrl(),
                'updatedAt' => $item->getUpdatedAt(),
                'images' => $item->getImages(),
                'priority' => 1,//$this->configReader->getPriority($storeId),
                'changeFrequency' => 'daily'//$this->configReader->getChangeFrequency($storeId),
            ]);
        }, $collection);

        return $items;
    }
}
