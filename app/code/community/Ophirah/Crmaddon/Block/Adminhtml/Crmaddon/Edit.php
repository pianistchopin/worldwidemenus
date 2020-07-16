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
 * @package     Crmaddon
 * @copyright   Copyright (c) 2020 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Crmaddon_Block_Adminhtml_Crmaddon_Edit
 */
class Ophirah_Crmaddon_Block_Adminhtml_Crmaddon_Edit extends Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Edit
{
    /**
     * Construct function
     */
    public function __construct()
    {
        parent::__construct();

        // Adding scripts to the form container
        $this->_formScripts[] = "
            function sendMessage(event){
                event.preventDefault();
                if (confirm('".$this->__('Are you sure you want to send this message? (unsaved changes to the quote will be lost)')."')) {
                    editForm.submit($('edit_form').action =\"" . $this->getUrl('*/crmaddon/crmmessage', array('id' => $this->getRequest()->getParam('id'))) . "\");
                } else {
                    event.preventDefault();
                    $('loading-mask').hide();
                }
            }

            function loadTemplate(event){
                if (event !== undefined){
                    event.preventDefault();
                }
                editForm.submit($('edit_form').action =\"" . $this->getUrl('*/crmaddon/loadtemplate', array('id' => $this->getRequest()->getParam('id'))) . "\");
            }

            function loadCrmTemplate(event){
                if (event !== undefined){
                    event.preventDefault();
                }
                editForm.submit($('edit_form').action =\"" . $this->getUrl('*/crmaddon/loadcrmtemplate', array('id' => $this->getRequest()->getParam('id'))) . "\");
            }

            function saveCrmTemplate(event){
                if (event !== undefined){
                    event.preventDefault();
                }
                editForm.submit($('edit_form').action =\"" . $this->getUrl('*/crmaddon/savecrmtemplate', array('id' => $this->getRequest()->getParam('id'))) . "\");
            }

            function newCrmTemplate(event){
                if (event !== undefined){
                    event.preventDefault();
                }
                editForm.submit($('edit_form').action =\"" . $this->getUrl('*/crmaddon/newcrmtemplate', array('id' => $this->getRequest()->getParam('id'))) . "\");
            }

            function deleteCrmTemplate(event){
                if (event !== undefined){
                    event.preventDefault();
                }
                editForm.submit($('edit_form').action =\"" . $this->getUrl('*/crmaddon/deletecrmtemplate', array('id' => $this->getRequest()->getParam('id'))) . "\");
            }

        ";

    }
}
