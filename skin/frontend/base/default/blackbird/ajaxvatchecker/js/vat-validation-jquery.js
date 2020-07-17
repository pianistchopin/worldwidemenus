/**
 * Blackbird AjaxVatChecker Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category            Blackbird
 * @package		Blackbird_AjaxVatChecker
 * @copyright           Copyright (c) 2015 Blackbird (http://black.bird.eu)
 * @author		Blackbird Team
 * @license		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */



var ajaxvatcheckerxhr;

jQuery(document).ready(function () {

    //Add validation div
    jQuery(ajaxvatchecker_selectors).after(ajaxvatchecker_validmessage);
    
    jQuery(ajaxvatchecker_selectors).keyup(function () {
        checkVatField(this);
    });

    //restart ajax request if a country is selected 
    jQuery(ajaxvatchecker_selectors_country).change(function () {
        checkVatField(this);
    });

});

function checkVatField(element)
{
    var vat = jQuery(element).val();
    var country = jQuery(ajaxvatchecker_selectors_country).val();
    jQuery('.ajaxvatchecker-validating-message', jQuery(element).parent()).hide();

    //check VAT number after 8 characters in the input box
    if (jQuery(element).val().length >= 8)
    {
        jQuery(element).addClass("ajaxvatchecker_inputtext");
        //abort previous ajax requests if a new one is loading
        if (ajaxvatcheckerxhr && ajaxvatcheckerxhr.readystate != 4) {
            ajaxvatcheckerxhr.abort();
        }

        ajaxvatcheckerxhr = jQuery.ajax({
            method: "POST",
            url: ajaxvatchecker_controller,
            data: {vat: vat, country: country},
            success: function (data) {
                jQuery(element).addClass("ajaxvatchecker_inputtext");

                //if the VAT number is correct
                if (data.status) {
                    jQuery(element).val(data.vat);
                    jQuery(ajaxvatchecker_selectors_country).val(data.codecountry);
                    
                    jQuery(ajaxvatchecker_selectors_country).each(function() {
                        
                        var elementCountry = document.getElementById(jQuery(this).attr('id'));
                        if ("createEvent" in document) {
                            var evt = document.createEvent("HTMLEvents");
                            evt.initEvent("change", false, true);
                            elementCountry.dispatchEvent(evt);
                        }
                        else
                            elementCountry.fireEvent("onchange");
                    });
                    
                        
                    jQuery('.ajaxvatchecker-validating-message', jQuery(element).parent()).addClass("success");
                    jQuery(element).removeClass('validation-failed');
                }

                //if the vat number is wrong
                else {
                    jQuery(element).addClass('validation-failed');
                    jQuery('.ajaxvatchecker-validating-message', jQuery(element).parent()).removeClass("success");
                }

                //display validation message
                jQuery('.ajaxvatchecker-validating-message', jQuery(element).parent()).html(data.message).show();
                jQuery(element).removeClass("ajaxvatchecker_inputtext");
            },
            //if ajax request failed
            error: function (xhr, status, code) {
                if (code != "abort")
                {
                    jQuery('.ajaxvatchecker-validating-message', jQuery(element).parent()).html("An error occurred, please try again later").show().removeClass("success");
                    jQuery(element).removeClass("ajaxvatchecker_inputtext");
                }
            }
        })

    }
}