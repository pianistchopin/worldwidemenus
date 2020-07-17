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

var billingForm = 'billDiv';
var shippingForm = 'shipDiv';
var customerForm = 'billing-new-address-form';
var addressCheckbox = 'addressCheckbox';
var checkBilling = 'address:copy';
var checkShipping = 'address:specifyShippingAddress';
var fireClickAddressCheckbox;
var customerFirstName = 'customer:firstname';
var customerLastName = 'customer:lastname';
var shippingName = 'shipping:firstname';
var shippingLastName = 'shipping:lastname';
var customerName = 'customerName';

function getBillingShippingOptions(checkbox, type){
    if(checkbox.checked){
        hideAllBoxes();
    }else{
        if(type == 'billing'){
            $(billingForm).show();
            if($(customerForm) !== null) {
                $(customerForm).show();
            }
        }else if(type == 'shipping'){
            $(shippingForm).show();
        }else{
            showAllBoxes();
        }

    }
}

function toggleDefaultAddress() {
    $(billingForm).show();
    $(shippingForm).show();
    if ($(checkBilling) !== null) {
        if ($(checkBilling).readAttribute('name') == 'address[billIsShip]') {
            if (!$(checkBilling).checked) {
                $(billingForm).show();
            } else {
                $(billingForm).hide();
            }
        } else if ($(checkBilling).readAttribute('name') == 'address[shipIsBill]') {
            if (!$(checkBilling).checked) {
                $(shippingForm).show();
            } else {
                $(shippingForm).hide();
            }
        }
    }

    if ($(checkShipping) !== null) {
        if (!$(checkShipping).checked) {
            $(shippingForm).show();
        } else {
            $(shippingForm).hide();
        }
    }
}

function getShippingAddressAction(checkbox){
    if(checkbox.checked){
        $(shippingForm).show();
        $(shippingName).value = $(customerFirstName).value;
        $(shippingLastName).value = $(customerLastName).value;
        $(customerFirstName).value = '';
        $(customerLastName).value = '';
        $(customerName).hide();

    }else {
        $(shippingForm).hide();
        $(customerFirstName).value = $(shippingName).value;
        $(customerLastName).value = $(shippingLastName).value;
        $(shippingName).value = '';
        $(shippingLastName).value = '';
        $(customerName).show();
    }
}

function hideAllBoxes(){
    $('option_billing').selected;
    $('option_shipping').selected;
}

function showAllBoxes(){
    $(billingForm).show();
    $(shippingForm).show();
    if($(customerForm) !== null) {
        $(customerForm).show();
    }
}

function showNewAddress(select, type){
    if(select === null){
        if($(checkShipping) === null){
            if($(checkBilling).readAttribute('name') == 'address[billIsShip]'){
                $(billingForm).hide();
            }else if($(checkBilling).readAttribute('name') == 'address[shipIsBill]'){
                $(shippingForm).hide();
            }
        }else{
            if($(checkShipping).checked){
                showAllBoxes();
            }else{
                $(billingForm).show();
            }
        }
        $('qquote-tbl-'+type).show();
    }else{
        if(select.value == 'new'){
            $('qquote-tbl-'+type).show();
        }else{
            $('qquote-tbl-'+type).hide();
        }
    }
}

document.observe("dom:loaded", function() {
    showNewAddress($('shipping:mage_address_id'), 'shipping');
    showNewAddress($('billing:mage_address_id'), 'billing');
    if($(checkShipping) === null){
        if($(checkBilling) != null){
            if($(checkBilling).readAttribute('name') == 'address[billIsShip]'){
                $(billingForm).hide();
            }else if($(checkBilling).readAttribute('name') == 'address[shipIsBill]'){
                $(shippingForm).hide();
            }
        }
    }else{
        if($(checkShipping).checked) {
            $(billingForm).show();
            $(shippingForm).show();
        }else{
            $(billingForm).hide();
            $(shippingForm).hide();
        }
    }
    if (fireClickAddressCheckbox == true) {
        document.getElementById('address:copy').click();
    }

});
function checkEmail(url, message){
    var elmEmail = $('customer:email');
    if (elmEmail) {
        Event.observe(elmEmail, 'change', function (event) {
            isExistUserEmail(event, url, message)
        });
    }
}

// Show remark textarea by clicking on the note message
function toggleRemark () {
    document.getElementById('toggle-remark-message').style.display = 'none';
    document.getElementById('toggle-remark-container').style.display = 'block';
}

// Toggle front and back of the Quick Quote form and toggle class on button
function toggleFrontBackContent () {

    var contentFront = document.getElementById('front-content');
    var contentBack = document.getElementById('back-content');
    var toggleButtonFront = document.getElementById("how-does-it-work");
    var btnDisableClassName = "btn-disable";

    if ( contentBack.style.display == 'block' ) {
        contentFront.style.display = 'block';
        contentBack.style.display = 'none';
        toggleButtonFront.className = toggleButtonFront.className.replace(btnDisableClassName,"");

    }else {
        contentFront.style.display = 'none';
        contentBack.style.display = 'block';
        toggleButtonFront.className = toggleButtonFront.className.replace(btnDisableClassName,"");
        toggleButtonFront.className = toggleButtonFront.className + btnDisableClassName;
    }
}
