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

namespace Magezon\TabsPro\Block\Adminhtml;

class TopMenu extends \Magezon\Core\Block\Adminhtml\TopMenu
{
	/**
	 * Init menu items
	 * 
	 * @return array
	 */
	public function intLinks()
	{
		$links = [
			[
				[
					'title'    => __('Add New Tab Profile'),
					'link'     => $this->getUrl('*/*/new'),
					'resource' => 'Magezon_TabsPro::tag_save'
				],
				[
					'title'    => __('Manage Tab Profiles'),
					'link'     => $this->getUrl('*/*'),
					'resource' => 'Magezon_TabsPro::tab'
				],
				[
					'title'    => __('Settings'),
					'link'     => $this->getUrl('adminhtml/system_config/edit/section/tabspro'),
					'resource' => 'Magezon_TabsPro::settings'
				]
			],
			[
				'class' => 'separator'
			],
			[
				'title'  => __('User Guide'),
				'link'   => 'https://magezon.com/tabs-pro.html',
				'target' => '_blank'
			],
			[
				'title'  => __('Change Log'),
				'link'   => 'https://magezon.com/tabs-pro.html#release_notes',
				'target' => '_blank'
			],
			[
				'title'  => __('Get Support'),
				'link'   => $this->getSupportLink(),
				'target' => '_blank'
			]
		];
		return $links;
	}
}