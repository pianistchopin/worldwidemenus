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
function Miniqquote(options) {
    this.formKey = options.formKey;
    this.previousVal = null;

    this.defaultErrorMessage = 'Error occurred. Try to refresh page.';

    this.selectors = {
        itemRemove:             '#quote-sidebar .remove',
        container:              '#header-quote',
        inputQty:               '.quote-item-quantity',
        qty:                    'div.header-miniquote span.count',
        overlay:                '.block-content-inner',
        error:                  '#miniquote-error-message',
        success:                '#miniquote-success-message',
        quantityButtonPrefix:   '#qbutton-',
        quantityInputPrefix:    '#qinput-',
        quantityButtonClass:    '.qquantity-button',
        reloadMiniQuoteUrl:     '#quote-sidebar'
    };

    if (options.selectors) {
        $j.extend(this.selectors, options.selectors);
    }
}

Miniqquote.prototype = {
    initAfterEvents : {},
    removeItemAfterEvents : {},
    init: function() {
        var quote = this;

        // bind remove event
        $j(this.selectors.itemRemove).unbind('click.miniquote').bind('click.miniquote', function(e) {
            e.preventDefault();
            quote.removeItem($j(this));
        });

        // bind update qty event
        $j(this.selectors.inputQty)
            .unbind('blur.miniquote')
            .unbind('focus.miniquote')
            .bind('focus.miniquote', function() {
                quote.previousVal = $j(this).val();
                quote.displayQuantityButton($j(this))
            })
            .bind('blur.miniquote', function() {
                quote.revertInvalidValue(this);
            });

        $j(this.selectors.quantityButtonClass)
            .unbind('click.quantity')
            .bind('click.quantity', function() {
                quote.processUpdateQuantity(this);
            });

        for (var i in this.initAfterEvents) {
            if (this.initAfterEvents.hasOwnProperty(i) && typeof(this.initAfterEvents[i]) === "function") {
                this.initAfterEvents[i]();
            }
        }

    },

    reloadContent: function() {
        var quote = this;
        var url = $j(this.selectors.reloadMiniQuoteUrl).attr('data-load-url');
        quote.hideMessage();
        quote.showOverlay();
        $j.ajax({
            type: 'GET',
            dataType: 'json',
            url: url
        }).done(function(result) {
            quote.hideOverlay();
            if (result.success) {
                quote.updateQuoteQty(result.qty);
                quote.updateContentOnUpdate(result);
            } else {
                quote.showMessage(result);
            }
        }).error(function() {
            quote.hideOverlay();
            quote.showError(quote.defaultErrorMessage);
        });
        return false;
    },

    removeItem: function(el) {
        var quote = this;
        if (confirm(el.data('confirm'))) {
            quote.hideMessage();
            quote.showOverlay();
            $j.ajax({
                type: 'POST',
                dataType: 'json',
                data: {form_key: quote.formKey},
                url: el.attr('href')
            }).done(function(result) {
                quote.hideOverlay();
                if (result.success) {
                    quote.updateQuoteQty(result.qty);
                    quote.updateContentOnRemove(result, el.closest('li'));
                } else {
                    quote.showMessage(result);
                }
            }).error(function() {
                quote.hideOverlay();
                quote.showError(quote.defaultErrorMessage);
            });
        }
        for (var i in this.removeItemAfterEvents) {
            if (this.removeItemAfterEvents.hasOwnProperty(i) && typeof(this.removeItemAfterEvents[i]) === "function") {
                this.removeItemAfterEvents[i]();
            }
        }
    },

    revertInvalidValue: function(el) {
        if (!this.isValidQty($j(el).val()) || $j(el).val() == this.previousVal) {
            $j(el).val(this.previousVal);
            this.hideQuantityButton(el);
        }
    },

    displayQuantityButton: function(el) {
        var buttonId = this.selectors.quantityButtonPrefix + $j(el).data('item-id');
        $j(buttonId).addClass('visible').attr('disabled',null);
    },

    hideQuantityButton: function(el) {
        var buttonId = this.selectors.quantityButtonPrefix + $j(el).data('item-id');
        $j(buttonId).removeClass('visible').attr('disabled','disabled');
    },

    processUpdateQuantity: function(el) {
        var input = $j(this.selectors.quantityInputPrefix + $j(el).data('item-id'));
        if (this.isValidQty(input.val()) && input.val() != this.previousVal) {
            this.updateItem(el);
        } else {
            this.revertInvalidValue(input);
        }
    },

    updateItem: function(el) {
        var quote = this;
        var input = $j(this.selectors.quantityInputPrefix + $j(el).data('item-id'));
        var quantity = parseInt(input.val(), 10);
        quote.hideMessage();
        quote.showOverlay();
        $j.ajax({
            type: 'POST',
            dataType: 'json',
            url: input.data('link'),
            data: {qty: quantity, form_key: quote.formKey}
        }).done(function(result) {
            quote.hideOverlay();
            if (result.success) {
                quote.updateQuoteQty(result.qty);
                if (quantity !== 0) {
                    quote.updateContentOnUpdate(result);
                } else {
                    quote.updateContentOnRemove(result, input.closest('li'));
                }
            } else {
                quote.showMessage(result);
            }
        }).error(function() {
            quote.hideOverlay();
            quote.showError(quote.defaultErrorMessage);
        });
        return false;
    },

    updateContentOnRemove: function(result, el) {
        var quote = this;
        el.hide('slow', function() {
            $j(quote.selectors.container).html(result.content);
            quote.showMessage(result);

        });
    },

    updateContentOnUpdate: function(result) {
        $j(this.selectors.container).html(result.content);
        this.showMessage(result);
    },

    updateQuoteQty: function(qty) {
        if (typeof qty != 'undefined') {
            $j(this.selectors.qty).text(qty);
            if(qty > 0){
                $j(this.selectors.qty).parent().removeClass('no-count');
            } else {
                $j(this.selectors.qty).parent().addClass('no-count')
            }
        }
    },

    isValidQty: function(val) {
        return (val.length > 0) && (val - 0 == val) && (val - 0 > 0);
    },

    showOverlay: function() {
        $j(this.selectors.overlay).addClass('loading');
    },

    hideOverlay: function() {
        $j(this.selectors.overlay).removeClass('loading');
    },

    showMessage: function(result) {
        if (typeof result.notice != 'undefined') {
            this.showError(result.notice);
        } else if (typeof result.error != 'undefined') {
            this.showError(result.error);
        } else if (typeof result.message != 'undefined') {
            this.showSuccess(result.message);
        }
    },

    hideMessage: function() {
        $j(this.selectors.error).fadeOut('slow');
        $j(this.selectors.success).fadeOut('slow');
    },

    showError: function(message) {
        $j(this.selectors.error).text(message).fadeIn('slow');
    },

    showSuccess: function(message) {
        $j(this.selectors.success).text(message).fadeIn('slow');
    }
};
