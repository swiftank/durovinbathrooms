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

namespace Magezon\TabsPro\Block\Adminhtml\Form\Renderer;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class TabManager extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    protected $_template = 'form/field/array.phtml';

    /**
     * Rows cache
     *
     * @var array|null
     */
    private $_arrayRowsCache;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Magento\CatalogWidget\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context  $context       
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder 
     * @param \Magento\CatalogWidget\Model\RuleFactory $ruleFactory   
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\CatalogWidget\Model\RuleFactory $ruleFactory
        ){
        parent::__construct($context);
        $this->jsonDecoder = $jsonDecoder;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $fields = $this->getFields();

        foreach ($fields as $k => $field) {
            $fieldId = $field;
            if (is_array($field)) {
                $fieldId = $k;
            }
            $this->addColumn($fieldId, [
                'label' => $fieldId,
                'default' => isset($field['default'])?$field['default']:''
                ]);
        }

        $this->_addAfter = false;
    }

    public function getFields()
    {
        $fields = [
            'title',
            'description',
            'ajax'                                 => ['default' => 'on'],
            'custom_class',
            'is_default',
            'top_enable',
            'top_source',
            'top_order_by'                         => ['default' => 'default'],
            'top_number_products'                  => ['default' => 15],
            'top_items_per_column'                 => ['default' => 1],
            'top_type'                             => ['default' => 'condition'],
            'top_width'                            => ['default' => '100%'],
            'top_height',
            'top_padding',
            'top_margin',
            'top_custom_class',
            'top_html',
            'top_product_name'                     => ['default' => 1],
            'top_product_shortdescription',
            'top_product_price'                    => ['default' => 1],
            'top_product_review'                   => ['default' => 1],
            'top_product_image'                    => ['default' => 1],
            'top_product_image_width'              => ['default' => 250],
            'top_product_image_height'             => ['default' => 300],
            'top_product_compare'                  => ['default' => 1],
            'top_product_wishlist'                 => ['default' => 1],
            'top_product_addtocart'                => ['default' => 1],
            'top_product_swatches'                 => ['default' => 1],
            'top_owl_device_0'                     => ['default' => 1],
            'top_owl_device_480'                   => ['default' => 2],
            'top_owl_device_768'                   => ['default' => 3],
            'top_owl_device_960'                   => ['default' => 4],
            'top_owl_device_1024'                  => ['default' => 5],
            'top_owl_autoplay'                     => ['default' => 1],
            'top_owl_autoplay_timeout'             => ['default' => 5000],
            'top_owl_autoplay_hover_pause'         => ['default' => 1],
            'top_owl_dots'                         => ['default' => 1],
            'top_owl_nav'                          => ['default' => 1],
            'top_owl_lazyload'                     => ['default' => 1],
            'top_owl_rtl'                          => ['default' => 0],
            'top_owl_loop'                         => ['default' => 1],
            'top_owl_margin',
            'top_owl_mousedrag'                    => ['default' => 1],
            'top_owl_touchdrag'                    => ['default' => 1],
            'top_owl_pulldrag'                     => ['default' => 1],
            'left_enable',
            'left_order_by'                        => ['default' => 'default'],
            'left_number_products'                 => ['default' => 15],
            'left_items_per_column'                => ['default' => 1],
            'left_source',
            'left_type'                            => ['default' => 'html'],
            'left_width'                           => ['default' => '50%'],
            'left_height',
            'left_padding',
            'left_margin',
            'left_custom_class',
            'left_html',
            'left_product_name'                    => ['default' => 1],
            'left_product_shortdescription',
            'left_product_price'                   => ['default' => 1],
            'left_product_review'                  => ['default' => 1],
            'left_product_image'                   => ['default' => 1],
            'left_product_image_width'             => ['default' => 250],
            'left_product_image_height'            => ['default' => 300],
            'left_product_compare'                 => ['default' => 1],
            'left_product_wishlist'                => ['default' => 1],
            'left_product_addtocart'               => ['default' => 1],
            'left_product_swatches'                => ['default' => 1],
            'left_owl_device_0'                    => ['default' => 1],
            'left_owl_device_480'                  => ['default' => 2],
            'left_owl_device_768'                  => ['default' => 3],
            'left_owl_device_960'                  => ['default' => 4],
            'left_owl_device_1024'                 => ['default' => 5],
            'left_owl_autoplay'                    => ['default' => 1],
            'left_owl_autoplay_timeout'            => ['default' => 5000],
            'left_owl_autoplay_hover_pause'        => ['default' => 1],
            'left_owl_dots'                        => ['default' => 1],
            'left_owl_nav'                         => ['default' => 1],
            'left_owl_lazyload'                    => ['default' => 1],
            'left_owl_rtl'                         => ['default' => 0],
            'left_owl_loop'                        => ['default' => 1],
            'left_owl_margin',
            'left_owl_mousedrag'                   => ['default' => 1],
            'left_owl_touchdrag'                   => ['default' => 1],
            'left_owl_pulldrag'                    => ['default' => 1],
            'maincontent_enable'                   => ['default' => 'on'],
            'maincontent_order_by'                 => ['default' => 'default'],
            'maincontent_number_products'          => ['default' => 15],
            'maincontent_items_per_column'         => ['default' => 1],
            'maincontent_source'                   => ['default' => 'latest'],
            'maincontent_type'                     => ['default' => 'condition'],
            'maincontent_width'                    => ['default' => '100%'],
            'maincontent_height',
            'maincontent_padding',
            'maincontent_margin',
            'maincontent_custom_class',
            'maincontent_html',
            'maincontent_product_name'             => ['default' => 1],
            'maincontent_product_shortdescription',
            'maincontent_product_price'            => ['default' => 1],
            'maincontent_product_review'           => ['default' => 1],
            'maincontent_product_image'            => ['default' => 1],
            'maincontent_product_image_width'      => ['default' => 250],
            'maincontent_product_image_height'     => ['default' => 300],
            'maincontent_product_compare'          => ['default' => 1],
            'maincontent_product_wishlist'         => ['default' => 1],
            'maincontent_product_addtocart'        => ['default' => 1],
            'maincontent_product_swatches'         => ['default' => 1],
            'maincontent_owl_device_0'             => ['default' => 1],
            'maincontent_owl_device_480'           => ['default' => 2],
            'maincontent_owl_device_768'           => ['default' => 3],
            'maincontent_owl_device_960'           => ['default' => 4],
            'maincontent_owl_device_1024'          => ['default' => 5],
            'maincontent_owl_autoplay'             => ['default' => 1],
            'maincontent_owl_autoplay_timeout'     => ['default' => 5000],
            'maincontent_owl_autoplay_hover_pause' => ['default' => 1],
            'maincontent_owl_dots'                 => ['default' => 1],
            'maincontent_owl_nav'                  => ['default' => 1],
            'maincontent_owl_lazyload'             => ['default' => 1],
            'maincontent_owl_rtl'                  => ['default' => 0],
            'maincontent_owl_loop'                 => ['default' => 1],
            'maincontent_owl_margin',
            'maincontent_owl_mousedrag'            => ['default' => 1],
            'maincontent_owl_touchdrag'            => ['default' => 1],
            'maincontent_owl_pulldrag'             => ['default' => 1],
            'right_enable',
            'right_order_by'                       => ['default' => 'default'],
            'right_number_products'                => ['default' => 15],
            'right_items_per_column'               => ['default' => 1],
            'right_source',
            'right_type'                           => ['default' => 'html'],
            'right_width'                          => ['default' => '50%'],
            'right_height',
            'right_padding',
            'right_margin',
            'right_custom_class',
            'right_html',
            'right_product_name'                   => ['default' => 1],
            'right_product_shortdescription',
            'right_product_price'                  => ['default' => 1],
            'right_product_review'                 => ['default' => 1],
            'right_product_image'                  => ['default' => 1],
            'right_product_image_width'            => ['default' => 250],
            'right_product_image_height'           => ['default' => 300],
            'right_product_compare'                => ['default' => 1],
            'right_product_wishlist'               => ['default' => 1],
            'right_product_addtocart'              => ['default' => 1],
            'right_product_swatches'               => ['default' => 1],
            'right_owl_device_0'                   => ['default' => 1],
            'right_owl_device_480'                 => ['default' => 2],
            'right_owl_device_768'                 => ['default' => 3],
            'right_owl_device_960'                 => ['default' => 4],
            'right_owl_device_1024'                => ['default' => 5],
            'right_owl_autoplay'                   => ['default' => 1],
            'right_owl_autoplay_timeout'           => ['default' => 5000],
            'right_owl_autoplay_hover_pause'       => ['default' => 1],
            'right_owl_dots'                       => ['default' => 1],
            'right_owl_nav'                        => ['default' => 1],
            'right_owl_lazyload'                   => ['default' => 1],
            'right_owl_rtl'                        => ['default' => 0],
            'right_owl_loop'                       => ['default' => 1],
            'right_owl_margin',
            'right_owl_mousedrag'                  => ['default' => 1],
            'right_owl_touchdrag'                  => ['default' => 1],
            'right_owl_pulldrag'                   => ['default' => 1],
            'bottom_enable',
            'bottom_order_by'                      => ['default' => 'default'],
            'bottom_number_products'               => ['default' => 15],
            'bottom_items_per_column'              => ['default' => 1],
            'bottom_source',
            'bottom_type'                          => ['default' => 'html'],
            'bottom_width'                         => ['default' => '100%'],
            'bottom_height',
            'bottom_padding',
            'bottom_margin',
            'bottom_custom_class',
            'bottom_html',
            'bottom_product_name'                  => ['default' => 1],
            'bottom_product_shortdescription',
            'bottom_product_price'                 => ['default' => 1],
            'bottom_product_review'                => ['default' => 1],
            'bottom_product_image'                 => ['default' => 1],
            'bottom_product_image_width'           => ['default' => 250],
            'bottom_product_image_height'          => ['default' => 300],
            'bottom_product_compare'               => ['default' => 1],
            'bottom_product_wishlist'              => ['default' => 1],
            'bottom_product_addtocart'             => ['default' => 1],
            'bottom_product_swatches'              => ['default' => 1],
            'bottom_owl_device_0'                  => ['default' => 1],
            'bottom_owl_device_480'                => ['default' => 2],
            'bottom_owl_device_768'                => ['default' => 3],
            'bottom_owl_device_960'                => ['default' => 4],
            'bottom_owl_device_1024'               => ['default' => 5],
            'bottom_owl_autoplay'                  => ['default' => 1],
            'bottom_owl_autoplay_timeout'          => ['default' => 5000],
            'bottom_owl_autoplay_hover_pause'      => ['default' => 1],
            'bottom_owl_dots'                      => ['default' => 1],
            'bottom_owl_nav'                       => ['default' => 1],
            'bottom_owl_lazyload'                  => ['default' => 1],
            'bottom_owl_rtl'                       => ['default' => 0],
            'bottom_owl_loop'                      => ['default' => 1],
            'bottom_owl_margin',
            'bottom_owl_mousedrag'                 => ['default' => 1],
            'bottom_owl_touchdrag'                 => ['default' => 1],
            'bottom_owl_pulldrag'                  => ['default' => 1]
        ];
        return $fields;
    }

    public function getConditions($parameters = '', $htmlId = '') {
        $element = $this->getElement();
        $block = $this->getLayout()->createBlock('\Magezon\TabsPro\Block\Adminhtml\Product\Widget\Conditions');
        if ($parameters) {
            $block->setData('parameters', $parameters);
        }
        if ($htmlId) {
            $block->setData('htmlid', $htmlId);
            $element->setHtmlContainerId($htmlId);
        }
        $rule = $this->ruleFactory->create();
        $block->setRule($rule);
        return $block->render($element);
    }

    /**
     * @return string
     */
    public function getNewChildUrl()
    {
        return $this->getUrl(
            'tabspro/product_widget/conditions/form/tabspro_url/rule_id/id'
            );
    }

/**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
public function renderCellTemplate($columnName)
{
    if (empty($this->_columns[$columnName])) {
        throw new \Exception('Wrong column name specified.');
    }
    $column = $this->_columns[$columnName];
    $inputName = $this->_getCellInputElementName($columnName);

    if (isset($column['type'])) {
        switch ($column['type']) {
            case 'radio':
            return '<input type="radio" id="' . $this->_getCellInputElementId(
                '<%- _id %>',
                $columnName
                ) .
            '"' .
            ' name="' .
            $columnName .
            '" value="<%- ' .
            $columnName .
            ' %>" ' .
            ($column['size'] ? 'size="' .
                $column['size'] .
                '"' : '') .
            ' class="' .
            (isset(
                $column['class']
                ) ? $column['class'] : 'input-text') . '"' . (isset(
                $column['style']
                ) ? ' style="' . $column['style'] . '"' : '') . '/>';
                break;

                case 'checkbox':
                return '<input type="checkbox" id="' . $this->_getCellInputElementId(
                    '<%- _id %>',
                    $columnName
                    ) .
                '"' .
                ' name="' .
                $columnName .
                '" value="<%- ' .
                $columnName .
                ' %>" ' .
                ($column['size'] ? 'size="' .
                    $column['size'] .
                    '"' : '') .
                ' class="' .
                (isset(
                    $column['class']
                    ) ? $column['class'] : 'input-text') . '"' . (isset(
                    $column['style']
                    ) ? ' style="' . $column['style'] . '"' : '') . '/>';
                    break;
                }
            }

            if ($column['renderer']) {
                return $column['renderer']->setInputName(
                    $inputName
                    )->setInputId(
                    $this->_getCellInputElementId('<%- _id %>', $columnName)
                    )->setColumnName(
                    $columnName
                    )->setColumn(
                    $column
                    )->toHtml();
                }

                return '<input type="text" id="' . $this->_getCellInputElementId(
                    '<%- _id %>',
                    $columnName
                    ) .
                '"' .
                ' name="' .
                $inputName .
                '" value="<%- ' .
                $columnName .
                ' %>" ' .
                ($column['size'] ? 'size="' .
                    $column['size'] .
                    '"' : '') .
                ' class="' .
                (isset(
                    $column['class']
                    ) ? $column['class'] : 'input-text') . '"' . (isset(
                    $column['style']
                    ) ? ' style="' . $column['style'] . '"' : '') . '/>';
                }

    /**
     * Get id for cell element
     *
     * @param string $columnName
     * @return string
     */
    public function getCellInputElementName($columnName)
    {
        return $this->getElement()->getName() . '[<%- _id %>][' . $columnName . ']';
    }

    /**
     * Add a column to array-grid
     *
     * @param string $name
     * @param array $params
     * @return void
     */
    public function addColumn($name, $params)
    {
        $this->_columns[$name] = [
            'label'    => $this->_getParam($params, 'label', 'Column'),
            'size'     => $this->_getParam($params, 'size', false),
            'style'    => $this->_getParam($params, 'style'),
            'class'    => $this->_getParam($params, 'class'),
            'type'     => $this->_getParam($params, 'type'),
            'default'  => $this->_getParam($params, 'default'),
            'renderer' => false
        ];
        if (!empty($params['renderer']) && $params['renderer'] instanceof \Magento\Framework\View\Element\AbstractBlock) {
            $this->_columns[$name]['renderer'] = $params['renderer'];
        }
    }

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of \Magento\Framework\DataObject
     *
     * @return array
     */
    public function getArrayRows()
    {
        if (null !== $this->_arrayRowsCache) {
            return $this->_arrayRowsCache;
        }
        $result = [];
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
        $element = $this->getElement();

        if ($element->getValue()) {
            $value = $element->getValue();

            $value = base64_decode($value);

            if ($value) {
                $value = $this->jsonDecoder->decode($value);
            } 
            $fields = $this->getFields();
            foreach ($value as $rowId => $row) {
                $rowColumnValues = [];
                foreach ($row as $key => $value) {
                    $row[$key] = $value;
                    $rowColumnValues[$this->_getCellInputElementId($rowId, $key)] = $row[$key];
                }
                $row['_id'] = $rowId;
                foreach ($fields as $_fieldId => $_field) {
                    $fieldId = $_field;
                    if (is_array($_field)) {
                        $fieldId = $_fieldId;
                    }

                    if (!isset($row[$fieldId])) {
                        $row[$fieldId] = '';
                    }
                }
                $row['column_values'] = $rowColumnValues;
                $result[$rowId] = new \Magento\Framework\DataObject($row);
                $this->_prepareArrayRow($result[$rowId]);
            }
        }
        $this->_arrayRowsCache = $result;
        return $this->_arrayRowsCache;
    }
}
