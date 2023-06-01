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

namespace Magezon\TabsPro\Helper;

class ImageBuilder extends \Magento\Catalog\Block\Product\ImageBuilder
{
	protected $_imageWidth;
	protected $_imageHeight;
	protected $_resizeImageWidth;
	protected $_resizeImageHeight;
	protected $_lazyload;

	public function setImageWidth($imageWidth)
	{
		$this->_imageWidth = $imageWidth;
		return $this;
	}

	public function getImageWidth()
	{
		return $this->_imageWidth;
	}

	public function setImageHeight($imageHeight)
	{
		$this->_imageHeight = $imageHeight;
		return $this;
	}

	public function getImageHeight()
	{
		return $this->_imageHeight;
	}

	public function setResizeImageWidth($resizeImageWidth)
	{
		$this->_resizeImageWidth = $resizeImageWidth;
		return $this;
	}

	public function getResizeImageWidth()
	{
		return $this->_resizeImageWidth;
	}

	public function setResizeImageHeight($resizeImageHeight)
	{
		$this->_resizeImageHeight = $resizeImageHeight;
		return $this;
	}

	public function getResizeImageHeight()
	{
		return $this->_resizeImageHeight;
	}

	public function setLazyLoad($lazyload)
	{
		$this->_lazyload = $lazyload;
		return $this;
	}

	public function getLazyLoad()
	{
		return $this->_lazyload;
	}

    /**
     * Create image block
     *
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function create()
    {
        /** @var \Magento\Catalog\Helper\Image $helper */
        /** @var \Magento\Catalog\Helper\Image $helper */
        $helper = $this->helperFactory->create()->init($this->product, $this->imageId, ['width' => $this->getImageWidth(), 'height' => $this->getImageHeight()]);

        $template = $helper->getFrame()
            ? 'Magento_Catalog::product/image.phtml'
            : 'Magento_Catalog::product/image_with_borders.phtml';

		$imagesize   = $helper->getResizedImageInfo();
		$imageWidth  = $this->getImageWidth()?$this->getImageWidth():$helper->getWidth();
		$imageHeight = $this->getImageHeight()?$this->getImageHeight():$helper->getHeight();
		
		if ($this->getResizeImageWidth()) {
			$resizeImageWidth = $this->getResizeImageWidth();
		} else {
			$resizeImageWidth  = !empty($imagesize[0]) ? $imagesize[0] : $helper->getWidth();
		}
		if ($this->getResizeImageHeight()) {
			$resizeImageHeight = $this->getResizeImageHeight();
		} else {
			$resizeImageHeight = !empty($imagesize[1]) ? $imagesize[1] : $helper->getHeight();
		}

		$data = [
            'data' => [
				'template'             => $template,
				'image_url'            => $helper->getUrl(),
				'width'                => $imageWidth,
				'height'               => $imageHeight,
				'label'                => $helper->getLabel(),
				'ratio'                => $this->getRatio($helper),
				'custom_attributes'    => $this->getCustomAttributes(),
				'resized_image_width'  => $resizeImageWidth,
				'resized_image_height' => $resizeImageHeight,
            ],
        ];

		$attrs = $this->getCustomAttributes();
		if ($this->getLazyLoad())  {
			$data['data']['custom_attributes'] .= ' data-src="' . $helper->getUrl() . '"';
			$data['data']['image_url']         = '';
		}
        return $this->imageFactory->create($data);
    }
}