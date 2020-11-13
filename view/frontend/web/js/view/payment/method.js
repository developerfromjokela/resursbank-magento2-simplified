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
     * @param quote
     * @param Component
     * @param redirectOnSuccessAction
     * @param url
     * @param totals
     * @param checkoutData
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
        quote,
        Component,
        redirectOnSuccessAction,
        url,
        totals,
        checkoutData,
        validator,
        CheckoutConfig,
        Credentials,
        CheckoutModel
    ) {
        'use strict';

        /**
         *
         *
         * @returns {object}
         */
        function getRelevantQuoteAddress() {
            return quote.billingAddress() !== null ?
                quote.billingAddress() :
                quote.shippingAddress();
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
         * Checks whether a payment method is provided by an external partner
         * to Resurs Bank.
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
                 * Whether the payment method has an SSN field. Some methods
                 * require the customer to specify their SSN before checking
                 * out.
                 *
                 * @type {boolean}
                 */
                me.hasSsnField = hasSsnField(me.getCode());

                /**
                 * The id number that the customer has entered.
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
            }
        });
    }
);
