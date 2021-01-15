/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
/**
 * This component represents a payment method on the billing step of the
 * checkout process.
 */
define(
    [
        'jquery',
        'ko',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/url',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/checkout-data',
        'Magento_Ui/js/lib/validation/validator',
        'Resursbank_Simplified/js/lib/checkout-config',
        'Resursbank_Simplified/js/lib/credentials',
        'Resursbank_Simplified/js/model/checkout'
    ],

    /**
     * @param $
     * @param ko
     * @param uiRegistry
     * @param translate
     * @param Quote
     * @param Component
     * @param redirectOnSuccessAction
     * @param url
     * @param totals
     * @param CheckoutData
     * @param validator
     * @param CheckoutConfig {Simplified.Lib.CheckoutConfig}
     * @param Credentials {Simplified.Lib.Credentials}
     * @param CheckoutModel {Simplified.Model.Checkout}
     * @returns {*}
     */
    function (
        $,
        ko,
        uiRegistry,
        translate,
        Quote,
        Component,
        redirectOnSuccessAction,
        url,
        totals,
        CheckoutData,
        validator,
        CheckoutConfig,
        Credentials,
        CheckoutModel
    ) {
        'use strict';

        /**
<<<<<<< HEAD
         * @typedef {object} Simplified.Method.CardOption
         * @property {(string|number)} value
         * @property {(string|number)} text
         */

        /**
         * @callback Simplified.Method.CardOptions
         * @param {Array<Simplified.Method.CardOption>} [value]
         * @return {Array<Simplified.Method.CardOption>}
         */

        /**
         *
=======
         * Get applied billing address (fallback to shipping address).
>>>>>>> dev/sprint-1
         *
         * @returns {object}
         */
        function getRelevantQuoteAddress() {
            return Quote.billingAddress() !== null ?
                Quote.billingAddress() :
                Quote.shippingAddress();
        }

        /**
         * Checks whether a payment method has an SSN field.
         *
         * @param {string} code
         * @returns {boolean}
         */
        function hasSsnField(code) {
            var method = CheckoutConfig.getPaymentMethod(code);

            return typeof method !== 'undefined' ?
                method.type === 'PAYMENT_PROVIDER' :
                false;
        }

        /**
         * Checks whether a payment method is provided by Resurs Bank directly.
         *
         * @param {string} code
         * @returns {boolean}
         */
        function isResursInternalMethod(code) {
            return CheckoutConfig.getPaymentMethods().some(
                function(method) {
                    return method.code === code
                        && method.type !== 'PAYMENT_PROVIDER';
                }
            );
        }

        /**
         * Checks whether a payment method is connected to Swish.
         *
         * @param {string} code
         * @returns {boolean}
         */
        function isSwishMethod(code) {
            return CheckoutConfig.getPaymentMethods().some(
                function(method) {
                    return method.code === code
                        && method.type === 'PAYMENT_PROVIDER'
                        && method.specificType === 'SWISH';
                }
            );
        }

        /**
         * Checks whether a payment method is a credit card.
         *
         * @param {string} code
         * @returns {boolean}
         */
        function isCreditCardMethod(code) {
            return CheckoutConfig.getPaymentMethods().some(
                function(method) {
                    return method.code === code
                        && method.type === 'PAYMENT_PROVIDER'
                        && (
                            method.specificType === 'DEBIT_CARD'
                            || method.specificType === 'CREDIT_CARD'
                        );
                }
            );
        }

        /**
         * Checks whether a payment method has card amount options.
         *
         * @param {string} code
         * @returns {boolean}
         */
        function hasCardAmount(code) {
            var method = CheckoutConfig.getPaymentMethod(code);
            var maxOrderTotal = parseFloat(String(method.maxOrderTotal));

            return method.type === 'REVOLVING_CREDIT' &&
                method.specificType === 'REVOLVING_CREDIT' &&
                !isNaN(maxOrderTotal) && maxOrderTotal > 0;
        }

        /**
         * Takes the name of a payment method and returns an array of credit
         * limit intervals for that payment method.
         *
         * @param {string} code
         * @returns {Array<Simplified.Method.CardOption>}
         */
        function getCardAmountOptions(code) {
            var i;
            var grandTotal;
            var interval;
            var result = [];
            var method = CheckoutConfig.getPaymentMethod(code);
            var maxOrderTotal = parseFloat(
                String(method.maxOrderTotal)
            );

            if (!isNaN(maxOrderTotal)) {
                grandTotal = Math.ceil(Quote.totals().base_grand_total);
                interval = CheckoutModel.cardAmountInterval;
                i = grandTotal + interval - grandTotal % interval;

                result.push(createCardAmountOption(grandTotal, grandTotal));

                for (i; i <= maxOrderTotal; i += interval) {
                    result.push(createCardAmountOption(i, i));
                }
            }

            return result;
        }

        /**
         * Creates and returns an object that represents a card amount option
         * in a <select> element.
         *
         * @param {string|number} value
         * @param {string|number} text
         * @returns {Simplified.Method.CardOption}
         */
        function createCardAmountOption(
            value,
            text
        ) {
            return {
                value: value,
                text: text
            }
        }

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: true,
                template: 'Resursbank_Simplified/payment/method'
            },

            /**
             * Initialization method.
             */
            initialize: function() {
                this._super();
                var me = this;

                me._super();

                me.isResursInternalMethod = ko.observable(
                    isResursInternalMethod(this.getCode())
                );

                /**
                 * Whether this payment method is connected to Swish.
                 *
                 * @type {boolean}
                 */
                me.isSwishMethod = isSwishMethod(this.getCode());

                /**
                 * Path to the logo of a Swish payment method.
                 *
                 * @type {string}
                 */
                me.swishLogo = require.toUrl(
                    'Resursbank_Simplified/images/swish.png'
                );

                /**
                 * Whether this payment method is a credit card.
                 *
                 * @type {string}
                 */
                me.isCreditCardMethod = isCreditCardMethod(this.getCode());

                /**
                 * Whether this payment method is connected to Visa or
                 * Mastercard.
                 *
                 * @type {boolean}
                 */
                me.isVisaMcMethod = isVisaMcMethod(this.getCode());

                /**
                 * Path to the logo of a credit card payment method.
                 *
                 * @type {string}
                 */
                me.creditCardLogo = require.toUrl(
                    'Resursbank_Simplified/images/credit-card-x2.png'
                );

                /**
                 * Whether the payment method has an SSN field. Some methods
                 * require the customer to specify their SSN before checking
                 * out.
                 *
                 * @type {boolean}
                 */
                me.hasSsnField = hasSsnField(me.getCode());

                /**
                 * The id number that the customer has entered, if any.
                 *
                 * @type {Simplified.Observable.String}
                 */
                me.idNumber = ko.observable('');

                /**
                 * Whether the given id number is invalid.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.invalidIdNumber = ko.computed(function () {
                    var address = getRelevantQuoteAddress();

                    return !Credentials.validate(
                        me.idNumber(),
                        address.countryId || '',
                        CheckoutModel.isCompany()
                    );
                });

                /**
                 * Whether the id number input should be disabled.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.disableIdNumber = ko.computed(function () {
                    return false;
                });

                /**
                 * Whether the customer is a company or not.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.isCompanyCustomer = ko.computed(function() {
                    return CheckoutModel.isCompany();
                });

                /**
                 * The contact id that the customer has entered.
                 *
                 * @type {Simplified.Observable.String}
                 */
                me.contactId = ko.observable('');

                /**
                 * Whether the contact id input should be disabled.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.disableContactId = ko.computed(function () {
                    return false;
                });

                /**
                 * Whether the contact ID-number is invalid.
                 *
                 * NOTE: contact id's are always private SSN numbers, never
                 * org. numbers.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.invalidContactId = ko.computed(function() {
                    var address = getRelevantQuoteAddress();

                    return !Credentials.validate(
                        me.contactId(),
                        address.countryId || '',
                        CheckoutModel.isCompany()
                    );
                });

                /**
                 * Whether this payment method has a field for selecting the
                 * card amount.
                 *
                 * @type {boolean}
                 */
                me.hasCardAmount = hasCardAmount(me.getCode());

                /**
                 * The amount the card should be worth.
                 *
                 * @type {Simplified.Observable.Number}
                 */
                me.cardAmount = ko.observable(0);

                /**
                 * List of card available card options for the method.
                 *
                 * @type {Simplified.Method.CardOptions}
                 */
                me.cardAmountOptions = ko.observable([]);

                /**
                 * Selects the payment method.
                 *
                 * @returns {boolean}
                 */
                me.select = function () {
                    // noinspection JSUnresolvedFunction
                    me.selectPaymentMethod();

                    return true;
                };

                /**
                 * Retrieve configured title for currently selected payment
                 * method.
                 *
                 * @returns {string}
                 */
                me.getTitle = function () {
                    var method = CheckoutConfig.getPaymentMethod(
                        me.getCode()
                    );

                    var result = '';

                    if (method) {
                        result = method.title;
                    }

                    return (typeof result === 'string' && result !== '') ?
                        result :
                        me._super();
                }

                /**
                 * Whether all requirements for an order placement has been met.
                 *
                 * @todo Full implementation in a later issue. Needed now to
                 *      render payment method.
                 * @type {Simplified.Observable.Boolean}
                 */
                me.canPlaceOrder = ko.computed(function () {
                    return false;
                });

                // noinspection JSUnusedLocalSymbols
                /**
                 * Starts the order placement process.
                 *
                 * @todo Full implementation in a later issue. Needed now to
                 *      render payment method.
                 * @param {object} data - Data that KnockoutJS supplies.
                 * @param {object} event
                 */
                me.resursBankPlaceOrder = function (
                    data,
                    event
                ) {
                    console.log('Placing order');
                };

                if (me.hasCardAmount) {
                    me.cardAmount(Quote.totals().base_grand_total);
                    me.cardAmountOptions(getCardAmountOptions(this.getCode()));

                    // If totals change we want to update the card amount
                    // options for this payment method.
                    Quote.totals.subscribe(function (value) {
                        var paymentMethod =
                            CheckoutData.getSelectedPaymentMethod();

                        if (paymentMethod === me.getCode()) {
                            me.cardAmountOptions(
                                getCardAmountOptions(me.getCode())
                            );
                            me.cardAmount(value.base_grand_total);
                        } else if (me.cardAmount() !== 0) {
                            me.cardAmount(0);
                            me.cardAmountOptions([]);
                        }
                    });
                }
            }
        });
    }
);
