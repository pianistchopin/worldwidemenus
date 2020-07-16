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

$newConfigPaths = array();
$newConfigPaths["qquoteadv/general/enabled"]                                            = "qquoteadv_general/quotations/enabled";
$newConfigPaths["qquoteadv/general/licence_key"]                                        = "qquoteadv_general/quotations/licence_key";
$newConfigPaths["qquoteadv/layout/active_c2q_tmpl"]                                     = "qquoteadv_general/quotations/active_c2q_tmpl";
$newConfigPaths["qquoteadv/layout/layout_update_detailpage_activated"]                  = "qquoteadv_quote_frontend/catalog/layout_update_detailpage_activated";
$newConfigPaths["qquoteadv/layout/layout_update_listgrid_activated"]                    = "qquoteadv_quote_frontend/catalog/layout_update_listgrid_activated";
$newConfigPaths["qquoteadv/layout/ajax_add"]                                            = "qquoteadv_quote_frontend/catalog/ajax_add";
$newConfigPaths["qquoteadv/general/redirect_to_quotation"]                              = "qquoteadv_quote_frontend/catalog/redirect_to_quotation";
$newConfigPaths["qquoteadv/layout/layout_disable_all_order_references"]                 = "qquoteadv_quote_frontend/shoppingcart_quotelist/layout_disable_all_order_references";
$newConfigPaths["qquoteadv/layout/layout_disable_trier_option"]                         = "qquoteadv_quote_frontend/shoppingcart_quotelist/layout_disable_trier_option";
$newConfigPaths["qquoteadv/sales_representatives/show_admin_login"]                     = "qquoteadv_quote_frontend/shoppingcart_quotelist/show_admin_login";
$newConfigPaths["qquoteadv/general/disable_exist_account_check"]                        = "qquoteadv_quote_frontend/shoppingcart_quotelist/disable_exist_account_check";
$newConfigPaths["qquoteadv/quote_form/require_shipping"]                                = "qquoteadv_quote_form_builder/options/require_shipping";
$newConfigPaths["qquoteadv/quote_form/require_billing"]                                 = "qquoteadv_quote_form_builder/options/require_billing";
$newConfigPaths["qquoteadv/quote_form/delivery_options"]                                = "qquoteadv_quote_form_builder/options/delivery_options";
$newConfigPaths["qquoteadv/quote_form/require_delivery_options"]                        = "qquoteadv_quote_form_builder/options/require_delivery_options";
$newConfigPaths["qquoteadv/quote_form/newsletter_subscribe"]                            = "qquoteadv_quote_form_builder/options/newsletter_subscribe";
$newConfigPaths["qquoteadv/quote_form/hide_shipping_quote"]                             = "qquoteadv_quote_form_builder/options/hide_shipping_quote";
$newConfigPaths["qquoteadv/quote_form_customization/extrafield_1"]                      = "qquoteadv_quote_form_builder/quote_form_customization/extrafield_1";
$newConfigPaths["qquoteadv/quote_form_customization/extrafield_1_label"]                = "qquoteadv_quote_form_builder/quote_form_customization/extrafield_1_label";
$newConfigPaths["qquoteadv/quote_form_customization/extrafield_2"]                      = "qquoteadv_quote_form_builder/quote_form_customization/extrafield_2";
$newConfigPaths["qquoteadv/quote_form_customization/extrafield_2_label"]                = "qquoteadv_quote_form_builder/quote_form_customization/extrafield_2_label";
$newConfigPaths["qquoteadv/quote_form_customization/extrafield_3"]                      = "qquoteadv_quote_form_builder/quote_form_customization/extrafield_3";
$newConfigPaths["qquoteadv/quote_form_customization/extrafield_3_label"]                = "qquoteadv_quote_form_builder/quote_form_customization/extrafield_3_label";
$newConfigPaths["qquoteadv/quote_form_customization/extrafield_4"]                      = "qquoteadv_quote_form_builder/quote_form_customization/extrafield_4";
$newConfigPaths["qquoteadv/quote_form_customization/extrafield_4_label"]                = "qquoteadv_quote_form_builder/quote_form_customization/extrafield_4_label";
$newConfigPaths["qquoteadv/emails/sender"]                                              = "qquoteadv_quote_emails/sales_representatives/sender";
$newConfigPaths["qquoteadv/emails/bcc"]                                                 = "qquoteadv_quote_emails/sales_representatives/bcc";
$newConfigPaths["qquoteadv/emails/send_linked_sale_bcc"]                                = "qquoteadv_quote_emails/sales_representatives/send_linked_sale_bcc";
$newConfigPaths["qquoteadv/emails/new_account"]                                         = "qquoteadv_quote_emails/templates/new_account";
$newConfigPaths["qquoteadv/emails/request"]                                             = "qquoteadv_quote_emails/templates/request";
$newConfigPaths["qquoteadv/emails/proposal"]                                            = "qquoteadv_quote_emails/templates/proposal";
$newConfigPaths["qquoteadv/emails/proposal_reject"]                                     = "qquoteadv_quote_emails/templates/proposal_reject";
$newConfigPaths["qquoteadv/emails/proposal_cancel"]                                     = "qquoteadv_quote_emails/templates/proposal_cancel";
$newConfigPaths["qquoteadv/emails/proposal_expire"]                                     = "qquoteadv_quote_emails/templates/proposal_expire";
$newConfigPaths["qquoteadv/emails/proposal_reminder"]                                   = "qquoteadv_quote_emails/templates/proposal_reminder";
$newConfigPaths["qquoteadv/emails/proposal_accepted"]                                   = "qquoteadv_quote_emails/templates/proposal_accepted";
$newConfigPaths["qquoteadv/attach/pdf"]                                                 = "qquoteadv_quote_emails/attachments/pdf";
$newConfigPaths["qquoteadv/attach/short_desc"]                                          = "qquoteadv_quote_emails/attachments/short_desc";
$newConfigPaths["qquoteadv/attach/doc"]                                                 = "qquoteadv_quote_emails/attachments/doc";
$newConfigPaths["qquoteadv/general/expirtime_request"]                                  = "qquoteadv_quote_configuration/expiration_times_and_notices/expirtime_request";
$newConfigPaths["qquoteadv/general/expirtime_proposal"]                                 = "qquoteadv_quote_configuration/expiration_times_and_notices/expirtime_proposal";
$newConfigPaths["qquoteadv/general/send_reminder"]                                      = "qquoteadv_quote_configuration/expiration_times_and_notices/send_reminder";
$newConfigPaths["qquoteadv/general/auto_proposal"]                                      = "qquoteadv_quote_configuration/proposal/auto_proposal";
$newConfigPaths["qquoteadv/general/quoteconfirmation"]                                  = "qquoteadv_quote_configuration/proposal/quoteconfirmation";
$newConfigPaths["qquoteadv/general/itemprice"]                                          = "qquoteadv_quote_configuration/proposal/itemprice";
$newConfigPaths["qquoteadv/general/adjustment"]                                         = "qquoteadv_quote_configuration/proposal/adjustment";
$newConfigPaths["qquoteadv/general/profit"]                                             = "qquoteadv_quote_configuration/proposal/profit";
$newConfigPaths["qquoteadv/general/qquoteadv_remark"]                                   = "qquoteadv_quote_configuration/proposal/qquoteadv_remark";
$newConfigPaths["qquoteadv/number_format/prefix"]                                       = "qquoteadv_quote_configuration/quote_number_format/prefix";
$newConfigPaths["qquoteadv/number_format/startnumber"]                                  = "qquoteadv_quote_configuration/quote_number_format/startnumber";
$newConfigPaths["qquoteadv/number_format/increment"]                                    = "qquoteadv_quote_configuration/quote_number_format/increment";
$newConfigPaths["qquoteadv/number_format/pad_length"]                                   = "qquoteadv_quote_configuration/quote_number_format/pad_length";
$newConfigPaths["qquoteadv/sales_representatives/auto_assign_rotate"]                   = "qquoteadv_sales_representatives/quote_assignment/auto_assign_rotate";
$newConfigPaths["qquoteadv/sales_representatives/auto_assign_role"]                     = "qquoteadv_sales_representatives/quote_assignment/auto_assign_role";
$newConfigPaths["qquoteadv/sales_representatives/auto_assign_login"]                    = "qquoteadv_sales_representatives/quote_assignment/auto_assign_login";
$newConfigPaths["qquoteadv/sales_representatives/auto_assign_previous"]                 = "qquoteadv_sales_representatives/quote_assignment/auto_assign_previous";
$newConfigPaths["qquoteadv/general/beta"]                                               = "qquoteadv_advanced_settings/general/beta";
$newConfigPaths["qquoteadv/quote_advanced/default_cart2quote_attribute_value"]          = "qquoteadv_advanced_settings/general/default_cart2quote_attribute_value";
$newConfigPaths["qquoteadv/emails/force_log"]                                           = "qquoteadv_advanced_settings/general/force_log";
$newConfigPaths["qquoteadv/attach/upload_folder"]                                       = "qquoteadv_advanced_settings/general/upload_folder";
$newConfigPaths["qquoteadv/general/followup"]                                           = "qquoteadv_advanced_settings/backend/followup";
$newConfigPaths["qquoteadv/sales_representatives/internal_comment"]                     = "qquoteadv_advanced_settings/backend/internal_comment";
$newConfigPaths["qquoteadv/quote_advanced/calculate_quote_totals_on_load"]              = "qquoteadv_advanced_settings/backend/calculate_quote_totals_on_load";
$newConfigPaths["qquoteadv/emails/link_auto_login"]                                     = "qquoteadv_advanced_settings/checkout/link_auto_login";
$newConfigPaths["qquoteadv/emails/auto_confirm"]                                        = "qquoteadv_advanced_settings/checkout/auto_confirm";
$newConfigPaths["qquoteadv/general/checkout_url"]                                       = "qquoteadv_advanced_settings/checkout/checkout_url";
$newConfigPaths["qquoteadv/quote_advanced/checkout_alternative"]                        = "qquoteadv_advanced_settings/checkout/checkout_alternative";
$newConfigPaths["qquoteadv/quote_advanced/checkout_alternative_url"]                    = "qquoteadv_advanced_settings/checkout/checkout_alternative_url";
$newConfigPaths["qquoteadv/quote_advanced/checkout_alternative_email"]                  = "qquoteadv_advanced_settings/checkout/checkout_alternative_email";
$newConfigPaths["qquoteadv/quote_advanced/mass_update_cart2quote_attributes"]           = "qquoteadv_advanced_settings/mass_update/mass_update_cart2quote_attributes";
$newConfigPaths["qquoteadv/quote_advanced/mass_update_cart2quote_attribute_ranges"]     = "qquoteadv_advanced_settings/mass_update/mass_update_cart2quote_attribute_ranges";
$newConfigPaths["qquoteadv/quick_quote/quick_quote_mode"]                               = "qquoteadv_advanced_settings/quick_quote/quick_quote_mode";
$newConfigPaths["qquoteadv/quick_quote/quick_quote_mode_remark"]                        = "qquoteadv_advanced_settings/quick_quote/quick_quote_mode_remark";
$newConfigPaths["qquoteadv/quick_quote/quick_quote_mode_telephone"]                     = "qquoteadv_advanced_settings/quick_quote/quick_quote_mode_telephone";
$newConfigPaths["qquoteadv/quick_quote/quick_quote_mode_company"]                       = "qquoteadv_advanced_settings/quick_quote/quick_quote_mode_company";
$newConfigPaths["qquoteadv/quick_quote/quick_quote_mode_country"]                       = "qquoteadv_advanced_settings/quick_quote/quick_quote_mode_country";

$newTemplatePaths = array();
$newTemplatePaths["qquoteadv_quote_advanced_checkout_alternative_email"]                = "qquoteadv_advanced_settings_checkout_checkout_alternative_email";
$newTemplatePaths["qquoteadv_emails_new_account"]                                       = "qquoteadv_quote_emails_templates_new_account";
$newTemplatePaths["qquoteadv_emails_request"]                                           = "qquoteadv_quote_emails_templates_request";
$newTemplatePaths["qquoteadv_emails_request_common"]                                    = "qquoteadv_quote_emails_templates_request_common";
$newTemplatePaths["qquoteadv_emails_proposal"]                                          = "qquoteadv_quote_emails_templates_proposal";
$newTemplatePaths["qquoteadv_emails_proposal_reject"]                                   = "qquoteadv_quote_emails_templates_proposal_reject";
$newTemplatePaths["qquoteadv_emails_proposal_cancel"]                                   = "qquoteadv_quote_emails_templates_proposal_cancel";
$newTemplatePaths["qquoteadv_emails_proposal_expire"]                                   = "qquoteadv_quote_emails_templates_proposal_expire";
$newTemplatePaths["qquoteadv_emails_proposal_reminder"]                                 = "qquoteadv_quote_emails_templates_proposal_reminder";
$newTemplatePaths["qquoteadv_emails_proposal_accepted"]                                 = "qquoteadv_quote_emails_templates_proposal_accepted";
//_responsive
$newTemplatePaths["qquoteadv_quote_advanced_checkout_alternative_email_responsive"]     = "qquoteadv_advanced_settings_checkout_checkout_alternative_email_responsive";
$newTemplatePaths["qquoteadv_emails_new_account_responsive"]                            = "qquoteadv_quote_emails_templates_new_account_responsive";
$newTemplatePaths["qquoteadv_emails_request_responsive"]                                = "qquoteadv_quote_emails_templates_request_responsive";
$newTemplatePaths["qquoteadv_emails_request_common_responsive"]                         = "qquoteadv_quote_emails_templates_request_common_responsive";
$newTemplatePaths["qquoteadv_emails_proposal_responsive"]                               = "qquoteadv_quote_emails_templates_proposal_responsive";
$newTemplatePaths["qquoteadv_emails_proposal_reject_responsive"]                        = "qquoteadv_quote_emails_templates_proposal_reject_responsive";
$newTemplatePaths["qquoteadv_emails_proposal_cancel_responsive"]                        = "qquoteadv_quote_emails_templates_proposal_cancel_responsive";
$newTemplatePaths["qquoteadv_emails_proposal_expire_responsive"]                        = "qquoteadv_quote_emails_templates_proposal_expire_responsive";
$newTemplatePaths["qquoteadv_emails_proposal_reminder_responsive"]                      = "qquoteadv_quote_emails_templates_proposal_reminder_responsive";
$newTemplatePaths["qquoteadv_emails_proposal_accepted_responsive"]                      = "qquoteadv_quote_emails_templates_proposal_accepted_responsive";

/** @var Ophirah_Qquoteadv_Model_Mysql4_Setup $this */
$installer = $this;
$installer->startSetup();

foreach ($newConfigPaths as $oldPath => $newPath) {
    $installer->run("UPDATE {$installer->getTable('core_config_data')} SET `path` = REPLACE(`path`, '".$oldPath."', '".$newPath."') WHERE `path` = '".$oldPath."'");
}

foreach ($newTemplatePaths as $oldPath => $newPath) {
    $installer->run("UPDATE {$installer->getTable('core_config_data')} SET `value` = REPLACE(`value`, '".$oldPath."', '".$newPath."') WHERE `value` = '".$oldPath."'");
}

$installer->endSetup();

