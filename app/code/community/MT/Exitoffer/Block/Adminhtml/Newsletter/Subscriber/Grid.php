<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.2 or later
 *
 * @category MageTrend
 * @package  MT/Exitoffer
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/exit-intent-popup
 */

class MT_Exitoffer_Block_Adminhtml_Newsletter_Subscriber_Grid
    extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
    protected function _prepareColumns()
    {
        $tableName = Mage::getSingleton('core/resource')->getTableName('exitoffer/popup');


        $fieldCollection = Mage::getModel('exitoffer/field')->getCollection();
        $fieldCollection->getSelect()
            ->joinLeft(array("t1" => $tableName), "main_table.popup_id = t1.entity_id", array())
            ->where('t1.content_type =?', MT_Exitoffer_Model_Popup::CONTENT_TYPE_NEWSLETTER_SUBSCRIPTION_FORM)
            ->where('t1.status =?', 1)
            ->group('main_table.name');

        $lastColumn = 'lastname';
        if ($fieldCollection->count() > 0) {
            foreach ($fieldCollection as $field) {
                $fieldName = 'subscriber_'.$field->getData('name');
                if ($this->getColumn($fieldName) || $fieldName == 'subscriber_firstname' || $fieldName == 'subscriber_lastname') {
                    continue;
                }

                $title = $field->getData('admin_title');
                if (empty($title)) {
                    $title = $field->getData('title');
                }

                $columnConfig = array(
                    'header'    => $title,
                    'index'     => $fieldName,
                    'default'   => '---',
                );

                if ($field->getData('type') == 'checkbox') {
                    $columnConfig['renderer'] = 'exitoffer/adminhtml_newsletter_subscriber_grid_column_renderer_checkbox';
                }

                $this->addColumnAfter($fieldName, $columnConfig, $lastColumn);
                $lastColumn = $fieldName;
            }
        }

        $this->addColumnAfter('exit_offer_coupon_code', array(
            'header'    => Mage::helper('exitoffer')->__('Exit Offer Coupon'),
            'index'     => 'exit_offer_coupon_code',
            'default'   => '---',
        ), $lastColumn);
        $lastColumn = 'exit_offer_coupon_code';

        if (!$this->getColumn('created_at')) {
            $this->addColumnAfter('created_at', array(
                'header'    => Mage::helper('exitoffer')->__('Created At'),
                'index'     => 'created_at',
                'type'   => 'date',
            ), $lastColumn);
            $lastColumn = 'created_at';
        }

        parent::_prepareColumns();

        if ($fieldCollection->count() > 0) {
            foreach ($fieldCollection as $field) {
                $fieldName = 'subscriber_'.$field->getData('name');
                if ($fieldName == 'subscriber_firstname') {
                    $this->getColumn('firstname')->setData('renderer', 'exitoffer/adminhtml_newsletter_subscriber_grid_column_renderer_firstname');
                } elseif ($fieldName == 'subscriber_lastname') {
                    $this->getColumn('lastname')->setData('renderer', 'exitoffer/adminhtml_newsletter_subscriber_grid_column_renderer_lastname');
                }
            }
        }

        return $this;
    }
}