<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2020] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit
 */
class Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Construct function
     *
     * Handles also the header buttons
     */
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'qquoteadv';
        $this->_controller = 'adminhtml_qquoteadv';
        $status = Mage::registry('qquote_data')->getData('status');

        // back, reset, save, and delete are the default button
        // removing buttons
        // $this->_removeButton('save');
        $this->_removeButton('reset');

        $this->_updateButton('save', 'label', Mage::helper('qquoteadv')->__('Save Quote'));
        $this->_updateButton('save', 'onclick', 'save()');

        $this->_updateButton('delete', 'label', Mage::helper('qquoteadv')->__('Cancel Quote'));

        if(!Ophirah_Qquoteadv_Helper_Acl::allowed(Ophirah_Qquoteadv_Helper_Acl::VIEW_BUTTONS_SAVE)) {
            $this->_removeButton('save');
        }
        if(!Ophirah_Qquoteadv_Helper_Acl::allowed(Ophirah_Qquoteadv_Helper_Acl::VIEW_BUTTONS_CANCEL_QUOTE)) {
            $this->_removeButton('delete');
        }

        // On Hold Button
        $onclick = 'hold();';
        $style = '';
        $hold = 1;

        if (Ophirah_Qquoteadv_Helper_Acl::allowed(Ophirah_Qquoteadv_Helper_Acl::VIEW_BUTTONS_HOLD)) {
            if ($status == Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_SAVED) {
                $hold = 2;
                $this->_addButton('hold', array(
                    'label' => Mage::helper('qquoteadv')->__('Unhold'),
                    'class' => $style,
                    'onclick' => $onclick,
                ));
            } else {
                $this->_addButton('hold', array(
                    'label' => Mage::helper('qquoteadv')->__('Hold'),
                    'class' => $style,
                    'onclick' => $onclick,
                ));
            }
        }
        if(Ophirah_Qquoteadv_Helper_Acl::allowed(Ophirah_Qquoteadv_Helper_Acl::VIEW_BUTTONS_EDIT_QUOTE)) {
            // Edit quote button (in case of quote state above request)
            if (intval($status) >= 50) {
                $this->_addButton('edit', array(
                    'label' => Mage::helper('qquoteadv')->__('Edit Quote'),
                    'class' => '',
                    'onclick' => 'edit();',
                ));
            }
        }
        if(Ophirah_Qquoteadv_Helper_Acl::allowed(Ophirah_Qquoteadv_Helper_Acl::VIEW_BUTTONS_PRINT)) {
            // Print Button
            $onclick = 'printPDF();';
            $style = '';
            $this->_addButton('print', array(
                'label' => Mage::helper('sales')->__('Print'),
                'class' => $style,
                'onclick' => $onclick,
            ));
        }

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('qquote_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'qquote_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'qquote_content');
                }
            }

            function saveAndContinueEdit(event){
                if (event !== undefined){
                    event.preventDefault();
                }
                removeCrmData();
                editForm.submit($('edit_form').action =\"" . $this->getUrl('*/*/save',
                    array(
                        'id' => $this->getRequest()->getParam('id'),
                        'back' => 'edit'
                    )
                ) . "\");
            }

            function printPDF(event){
                if (event !== undefined){
                    event.preventDefault();
                }
                //removeCrmData(); //don't clear crmaddon data on pdf save as this action doesn't reload the page
                editForm.submit($('edit_form').action =\"" . $this->getPrintUrl() . "\");;
            }

            function save(event){
                if (event !== undefined){
                    event.preventDefault();
                }
                removeCrmData();
                editForm.submit($('edit_form').action =\"" . $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))) . "\");
            }

            function hold(event){
                if (event !== undefined){
                    event.preventDefault();
                }
                removeCrmData();
                editForm.submit($('edit_form').action =\"" . $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'), 'hold' => $hold)) . "\");
            }

            function edit(event){
                if (event !== undefined){
                    event.preventDefault();
                }
                
                if (confirm('Are you sure you want to edit this quote? This will cancel this quote and create a new one.')) {
                    removeCrmData();
                    editForm.submit($('edit_form').action =\"" . $this->getUrl('*/*/editLockQuote', array('id' => $this->getRequest()->getParam('id'))) . "\");
                } else {
                    //Do nothing
                }
            }

            //remove crm data when not needed (this is to reduce mod_security errors)
            function removeCrmData(){
                if(($$('textarea[name=\"crm_message\"]').first() !== undefined)){
                    $$('textarea[name=\"crm_message\"]').first().value = '';
                    $$('textarea[name=\"crm_message\"]').first().innerHTML = '';
                    $$('#crmaddon_template').first().value = '';
                    $$('#crmaddon_template').first().innerHTML = '';
                    tinyMCE.get(\"crmaddon_template\").setContent('');
                }

                if(($$('textarea[name=\"crm_templatebody\"]').first() !== undefined)){
                    $$('textarea[name=\"crm_templatebody\"]').first().value = '';
                    $$('textarea[name=\"crm_templatebody\"]').first().innerHTML = '';
                }
            }
        ";

    }

    /**
     * Function that returns the header text in the quote edit page
     *
     * @return string
     */
    public function getHeaderText()
    {
        $quote_id = Mage::registry('qquote_data')->getData('quote_id');
        $increment_id = Mage::registry('qquote_data')->getData('increment_id');
        $created_at = Mage::registry('qquote_data')->getData('created_at');

        if (intval(Mage::registry('qquote_data')->getData('status')) >= 50) {
            $lockedState = ' | ['.Mage::helper('qquoteadv')->__('LOCKED').']';
        } else {
            $lockedState = '';
        }

        $text = Mage::helper('qquoteadv')->__('Quote # %s | Quote Date %s',
            $increment_id ? $increment_id : $quote_id,
            $this->formatDate($created_at, 'medium', true)
        );

        $text .= $lockedState;

        return $text;
    }

    /**
     * Returns the pdf print url
     *
     * @return mixed
     */
    public function getPrintUrl()
    {
        $quote_id = Mage::registry('qquote_data')->getData('quote_id');
        return $this->getUrl('*/*/pdfqquoteadv/id/' . $quote_id);
    }

    /**
     * Returns the create order from quote url
     *
     * @return mixed
     */
    public function getConvertUrl()
    {
        $quote_id = Mage::registry('qquote_data')->getData('quote_id');
        return $this->getUrl('*/*/convert/id/' . $quote_id);
    }
}
