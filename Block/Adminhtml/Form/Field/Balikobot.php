<?php

namespace Beecom\Balikobot\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;

class Balikobot extends AbstractFieldArray
{
    /**
     * Rows cache
     *
     * @var array|null
     */
    private $_arrayRowsCache;

    protected $_groupRenderer;

    protected $_matrixRenderer;

    protected $_popupRenderer;

    protected function _getGroupRenderer()
    {
        if (!$this->_groupRenderer) {
            $this->_groupRenderer = $this->getLayout()->createBlock(
                'Beecom\Balikobot\Block\Adminhtml\Form\Field\AvailableDeliveryMethods',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_groupRenderer;
    }

    protected function _getMatrixRateRenderer()
    {
        if (!$this->_matrixRenderer) {
            $this->_matrixRenderer = $this->getLayout()->createBlock(
                'Beecom\Balikobot\Block\Adminhtml\Form\Field\AvailableMatrixRates',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_matrixRenderer;
    }

    protected function _getPopupRenderer()
    {
        if (!$this->_popupRenderer) {
            $this->_popupRenderer = $this->getLayout()->createBlock(
                'Beecom\Balikobot\Block\Adminhtml\Form\Field\PopupOptions',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_popupRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('matrixrate',
            ['label' => __('Matrix Rate'), 'renderer' => $this->_getMatrixRateRenderer()]
        );
        $this->addColumn('balikobot',
            ['label' => __('Balikobot Delivery'), 'renderer' => $this->_getGroupRenderer()]
        );
        $this->addColumn('popup',
            ['label' => __('Show services popup'), 'renderer' => $this->_getPopupRenderer()]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('balikobot'))] =
            'selected="selected"';
        $optionExtraAttr['option_' . $this->_getMatrixRateRenderer()->calcOptionHash($row->getData('matrixrate'))] =
            'selected="selected"';
        $optionExtraAttr['option_' . $this->_getPopupRenderer()->calcOptionHash($row->getData('popup'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
