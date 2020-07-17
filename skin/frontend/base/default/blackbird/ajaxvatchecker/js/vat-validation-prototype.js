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

Event.observe(window, 'load', function () {

    //Add validation div
    $$(ajaxvatchecker_selectors).each(function (element) {
        element.insert({after: ajaxvatchecker_validmessage});
    });

    $$(ajaxvatchecker_selectors).each(function (element) {
        Event.observe(element, 'keyup', function () {

            checkVatField(element);

        });

        //restart ajax request if a country is selected
        $$(ajaxvatchecker_selectors_country).each(function (element) {
            Event.observe(element, 'change', function () {
                $$(ajaxvatchecker_selectors).each(function (elementKey) {
                    checkVatField(elementKey);
                })
            })
        })

    });

});

function checkVatField(element)
{
    var vat = element.value;
    var country;
    $$(ajaxvatchecker_selectors_country).each(function (elementCountry) {
        country = elementCountry.value;
    });

    element.up().getElementsBySelector('.ajaxvatchecker-validating-message').each(function (elementMsg) {
        elementMsg.hide();
    });

    //check VAT number after 8 characters in the input box
    if (element.value.length >= 8)
    {
        element.addClassName("ajaxvatchecker_inputtext");
        //abort previous ajax requests if a new one is loading
        if (ajaxvatcheckerxhr && ajaxvatcheckerxhr.readystate != 4) {
            ajaxvatcheckerxhr.transport.abort();
        }

        ajaxvatcheckerxhr = new Ajax.Request(ajaxvatchecker_controller, {
            method: "POST",
            parameters: ({vat: vat, country: country}),
            onSuccess: function (transport) {
                var data = transport.responseText.evalJSON();

                element.addClassName("ajaxvatchecker_inputtext");

                //if the VAT number is correct
                if (data.status) {
                    element.value=data.vat;
                    $$(ajaxvatchecker_selectors_country).each(function (elementCountry) {
                        elementCountry.value = data.codecountry;
                        if ("createEvent" in document) {
                            var evt = document.createEvent("HTMLEvents");
                            evt.initEvent("change", false, true);
                            elementCountry.dispatchEvent(evt);
                        }
                        else
                            elementCountry.fireEvent("onchange");

                    });
                    element.up().getElementsBySelector('.ajaxvatchecker-validating-message').each(function (elementMsg) {
                        elementMsg.addClassName("success");
                    });
                    element.removeClassName('validation-failed');
                }

                //if the vat number is wrong
                else {
                    element.addClassName('validation-failed');
                    element.up().getElementsBySelector('.ajaxvatchecker-validating-message').each(function (elementMsg) {
                        elementMsg.removeClassName("success");
                    });
                }

                //display validation message
                element.up().getElementsBySelector('.ajaxvatchecker-validating-message').each(function (elementMsg) {
                    elementMsg.innerHTML = data.message;
                    elementMsg.show();
                });
                element.removeClassName("ajaxvatchecker_inputtext");
            },
            //if ajax request failed
            error: function (xhr, status, code) {
                if (code != "abort")
                {
                    element.up().getElementsBySelector('.ajaxvatchecker-validating-message').each(function (elementMsg) {
                        elementMsg.innerHTML = "An error occurred, please try again later";
                        elementMsg.show().removeClassName("success");
                    });

                    element.removeClassName("ajaxvatchecker_inputtext");
                }
            }
        })

    }
}