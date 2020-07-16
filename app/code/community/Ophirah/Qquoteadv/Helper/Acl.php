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
 * Class Ophirah_Qquoteadv_Helper_File
 */
final class Ophirah_Qquoteadv_Helper_Acl extends Mage_Core_Helper_Abstract
{
    const VIEW_SHIPPING_INFORMATION = 'admin/sales/qquoteadv/editquote/shippinginformation';
    const VIEW_DISCOUNT = 'admin/sales/qquoteadv/editquote/discount';
    const VIEW_QUOTE_TOTALS = 'admin/sales/qquoteadv/editquote/quotetotals';
    const VIEW_MESSAGE_HISTORY = 'admin/sales/qquoteadv/editquote/messagehistory';
    const VIEW_ATTACH_FILE_TO_PROPOSAL_EMAIL = 'admin/sales/qquoteadv/editquote/attachfiles';
    const VIEW_LINKED_ORDERS = 'admin/sales/qquoteadv/editquote/linkedorders';

    const VIEW_PRODUCT_INFORMATION = 'admin/sales/qquoteadv/editquote/productinformation';
    const VIEW_PRODUCT_INFORMATION_QTY = 'admin/sales/qquoteadv/editquote/productinformation/qty';
    const VIEW_PRODUCT_INFORMATION_ROW_TOTAL = 'admin/sales/qquoteadv/editquote/productinformation/rowtotal';
    const VIEW_PRODUCT_INFORMATION_MARGIN = 'admin/sales/qquoteadv/editquote/productinformation/margin';
    const VIEW_PRODUCT_INFORMATION_PRICE = 'admin/sales/qquoteadv/editquote/productinformation/price';
    const VIEW_PRODUCT_INFORMATION_ORIGINAL_PRICE = 'admin/sales/qquoteadv/editquote/productinformation/originalprice';
    const VIEW_PRODUCT_INFORMATION_COST_PRICE = 'admin/sales/qquoteadv/editquote/productinformation/costprice';
    const VIEW_PRODUCT_INFORMATION_COMMENT = 'admin/sales/qquoteadv/editquote/productinformation/comment';
    const VIEW_PRODUCT_INFORMATION_PRODUCT = 'admin/sales/qquoteadv/editquote/productinformation/product';
    const VIEW_PRODUCT_INFORMATION_SKU = 'admin/sales/qquoteadv/editquote/productinformation/sku';

    const VIEW_ACCOUNT_INFORMATION = 'admin/sales/qquoteadv/editquote/accountinformation';
    const VIEW_ACCOUNT_INFORMATION_CUSTOMER_GROUP = 'admin/sales/qquoteadv/editquote/accountinformation/customergroup';
    const VIEW_ACCOUNT_INFORMATION_TELEPHONE = 'admin/sales/qquoteadv/editquote/accountinformation/telephone';
    const VIEW_ACCOUNT_INFORMATION_EMAIL = 'admin/sales/qquoteadv/editquote/accountinformation/email';
    const VIEW_ACCOUNT_INFORMATION_COMPANY = 'admin/sales/qquoteadv/editquote/accountinformation/company';
    const VIEW_ACCOUNT_INFORMATION_CUSTOMER_NAME = 'admin/sales/qquoteadv/editquote/accountinformation/customername';

    const VIEW_QUOTATION_MANAGEMENT = 'admin/sales/qquoteadv/editquote/quotationmanagement';
    const VIEW_QUOTATION_MANAGEMENT_ASSIGNED_TO = 'admin/sales/qquoteadv/editquote/quotationmanagement/assignedto';
    const VIEW_QUOTATION_MANAGEMENT_GENERAL_COMMENT = 'admin/sales/qquoteadv/editquote/quotationmanagement/generalcomment';
    const VIEW_QUOTATION_MANAGEMENT_INTERNAL_COMMENT = 'admin/sales/qquoteadv/editquote/quotationmanagement/internalcomment';
    const VIEW_QUOTATION_MANAGEMENT_FOLLOW_UP_DATE = 'admin/sales/qquoteadv/editquote/quotationmanagement/followupdate';
    const VIEW_QUOTATION_MANAGEMENT_REMINDER_DATE = 'admin/sales/qquoteadv/editquote/quotationmanagement/reminderdate';
    const VIEW_QUOTATION_MANAGEMENT_EXPIRY_DATE = 'admin/sales/qquoteadv/editquote/quotationmanagement/expirydata';
    const VIEW_QUOTATION_MANAGEMENT_ALTERNATIVE_CHECKOUT = 'admin/sales/qquoteadv/editquote/quotationmanagement/alternativecheckout';

    const VIEW_BILLING_ADDRESS = 'admin/sales/qquoteadv/editquote/billingaddress';
    const VIEW_SHIPPING_ADDRESS = 'admin/sales/qquoteadv/editquote/shippingaddress';

    const VIEW_GENERAL_QUOTE_INFORMATION = 'admin/sales/qquoteadv/editquote/generalquoteinformation';
    const VIEW_GENERAL_QUOTE_INFORMATION_RFQ_FROM = 'admin/sales/qquoteadv/editquote/generalquoteinformation/store';
    const VIEW_GENERAL_QUOTE_INFORMATION_QUOTE_REQUEST_STATUS = 'admin/sales/qquoteadv/editquote/generalquoteinformation/status';
    const VIEW_GENERAL_QUOTE_INFORMATION_LAST_UPDATE = 'admin/sales/qquoteadv/editquote/generalquoteinformation/lastupdate';
    const VIEW_GENERAL_QUOTE_INFORMATION_LINK_TO_NEW_QUOTE = 'admin/sales/qquoteadv/editquote/generalquoteinformation/linktonewquote';
    const VIEW_GENERAL_QUOTE_INFORMATION_LINK_TO_PREVIOUS_QUOTE = 'admin/sales/qquoteadv/editquote/generalquoteinformation/linktopreviousquote';

    const VIEW_BUTTONS = 'admin/sales/qquoteadv/editquote/buttons';
    const VIEW_BUTTONS_CANCEL_QUOTE = 'admin/sales/qquoteadv/editquote/buttons/cancelquote';
    const VIEW_BUTTONS_HOLD = 'admin/sales/qquoteadv/editquote/buttons/hold';
    const VIEW_BUTTONS_EDIT_QUOTE = 'admin/sales/qquoteadv/editquote/buttons/editquote';
    const VIEW_BUTTONS_PRINT = 'admin/sales/qquoteadv/editquote/buttons/print';
    const VIEW_BUTTONS_SAVE = 'admin/sales/qquoteadv/editquote/buttons/save';
    const VIEW_BUTTONS_ADD_CUSTOM_PRODUCT = 'admin/sales/qquoteadv/editquote/buttons/addcustomproduct';
    const VIEW_BUTTONS_EDIT_PRODUCTS = 'admin/sales/qquoteadv/editquote/buttons/editproducts';
    const VIEW_BUTTONS_SUBMIT_PROPOSAL = 'admin/sales/qquoteadv/editquote/buttons/submitproposal';
    const VIEW_BUTTONS_CREATE_ORDER = 'admin/sales/qquoteadv/editquote/buttons/createorder';
    const VIEW_BUTTONS_UPDATE_TOTALS = 'admin/sales/qquoteadv/editquote/buttons/updatetotals';

    const VIEW_ADD_QTY = 'admin/sales/qquoteadv/editquote/addqtyallowed';

    /**
     * @param $path
     * @return mixed
     */
    public static function allowed($path){
        return Mage::getSingleton('admin/session')
            ->isAllowed($path);
    }
}
