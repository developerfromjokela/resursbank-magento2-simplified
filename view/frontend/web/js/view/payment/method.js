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
        'mage/url',
        'uiLayout',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/checkout-data',
        'Magento_Ui/js/lib/validation/validator',
        'Resursbank_Core/js/lib/read-more',
        'Resursbank_Simplified/js/lib/checkout-config',
        'Resursbank_Simplified/js/lib/credentials',
        'Resursbank_Simplified/js/lib/session',
        'Resursbank_Simplified/js/model/checkout',
        'Resursbank_Simplified/js/action/checkout',
        'Resursbank_Simplified/js/storage/checkout',
        'Resursbank_Simplified/js/model/payment/method-render-list'
    ],

    /**
     * @param $
     * @param ko
     * @param uiRegistry
     * @param translate
     * @param url
     * @param Layout
     * @param Quote
     * @param Component
     * @param redirectOnSuccessAction
     * @param totals
     * @param CheckoutData
     * @param validator
     * @param {RbC.Lib.ReadMore} ReadMoreLib
     * @param {Simplified.Lib.CheckoutConfig} CheckoutConfigLib
     * @param {Simplified.Lib.Credentials} CredentialsLib
     * @param {Simplified.Lib.Session} SessionLib
     * @param {Simplified.Model.Checkout} CheckoutModel
     * @param {Simplified.Action.Checkout} CheckoutAction
     * @param {Simplified.Storage.Checkout} CheckoutStorage
     * @returns {*}
     */
    function (
        $,
        ko,
        uiRegistry,
        translate,
        url,
        Layout,
        Quote,
        Component,
        redirectOnSuccessAction,
        totals,
        CheckoutData,
        validator,
        ReadMoreLib,
        CheckoutConfigLib,
        CredentialsLib,
        SessionLib,
        CheckoutModel,
        CheckoutAction,
        CheckoutStorage
    ) {
        'use strict';

        /**
         * Get applied billing address (fallback to shipping address).
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
            var method = CheckoutConfigLib.getPaymentMethod(code);

            return typeof method !== 'undefined' ?
                method.type !== 'PAYMENT_PROVIDER' :
                false;
        }

        /**
         * Checks whether a payment method is provided by Resurs Bank directly.
         *
         * @param {string} code
         * @returns {boolean}
         */
        function isResursInternalMethod(code) {
            return CheckoutConfigLib.getPaymentMethods().some(
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
            return CheckoutConfigLib.getPaymentMethods().some(
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
            return CheckoutConfigLib.getPaymentMethods().some(
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
         * Checks whether a payment method is a Trustly payment method.
         *
         * @param {string} code
         * @returns {boolean}
         */
        function isTrustlyMethod(code) {
            return CheckoutConfigLib.getPaymentMethods().some(
                function(method) {
                    return method.code === code
                        && method.type === 'PAYMENT_PROVIDER'
                        && method.specificType === 'INTERNET';
                }
            );
        }

        /**
         * Whether the payment method is available for the chosen customer
         * type.
         *
         * @param {string} code
         * @returns {boolean}
         */
        function isAvailable(code) {
            var method = CheckoutConfigLib.getPaymentMethod(code);

            return (
                CheckoutModel.isCompany() &&
                method.customerType.includes('LEGAL')
            ) || (
                !CheckoutModel.isCompany() &&
                method.customerType.includes('NATURAL')
            );
        }

        /**
         * Whether the method has a "Legal information link" attached to it.
         *
         * @param {string} code
         * @returns {boolean}
         */
        function hasLegalInfoLink(code) {
            var method = CheckoutConfigLib.getPaymentMethod(code);

            return method.specificType === 'PART_PAYMENT' ||
                method.specificType === 'REVOLVING_CREDIT' ||
                method.specificType === 'CARD' ||
                method.specificType === 'NEW_CARD' ||
                method.specificType === 'INVOICE';
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
                var me = this;
                var storageGovId = CheckoutStorage.getGovId();

                CheckoutAction.setGovId(
                    typeof storageGovId === 'string' ?
                        storageGovId :
                        ''
                );

                me._super();

                /**
                 * Whether this payment method is from Resurs Bank.
                 *
                 * @type {RbC.Ko.Boolean}
                 */
                me.isResursInternalMethod = ko.observable(
                    isResursInternalMethod(this.getCode())
                );

                /**
                 * Path to the logo of a Resurs Bank payment method.
                 *
                 * @type {string}
                 */
                me.resursBankLogo = require.toUrl(
                    'Resursbank_Simplified/images/logo.png'
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
                 * @type {boolean}
                 */
                me.isCreditCardMethod = isCreditCardMethod(this.getCode());

                /**
                 * Path to the logo of a credit card payment method.
                 *
                 * @type {string}
                 */
                me.creditCardLogo = require.toUrl(
                    'Resursbank_Simplified/images/card.svg'
                );

                /**
                 * Whether this is a Trustly payment method.
                 *
                 * @type {boolean}
                 */
                me.isTrustlyMethod = isTrustlyMethod(this.getCode());

                /**
                 * Path to the logo of a Trustly payment method.
                 *
                 * @type {string}
                 */
                me.trustlyLogo = require.toUrl(
                    'Resursbank_Simplified/images/trustly.svg'
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
                 * @type {RbC.Ko.String}
                 */
                me.govId = ko.computed({
                    read: function () {
                        return CheckoutModel.govId();
                    },

                    write: function (value) {
                        CheckoutAction.setGovId(value);
                    }
                });

                /**
                 * Whether the given id number is invalid.
                 *
                 * @type {RbC.Ko.Boolean}
                 */
                me.invalidGovId = ko.computed(function () {
                    var address = getRelevantQuoteAddress();

                    return me.govId() !== '' && !CredentialsLib.validate(
                        me.govId(),
                        address.countryId || '',
                        CheckoutModel.isCompany()
                    );
                });

                /**
                 * Whether the id number input should be disabled.
                 *
                 * @type {RbC.Ko.Boolean}
                 */
                me.disableGovId = ko.computed(function () {
                    return false;
                });

                /**
                 * Whether the customer is a company or not.
                 *
                 * @type {RbC.Ko.Boolean}
                 */
                me.isCompanyCustomer = ko.computed(function() {
                    return CheckoutModel.isCompany();
                });

                /**
                 * The contact id that the customer has entered.
                 *
                 * @type {RbC.Ko.String}
                 */
                me.contactId = ko.observable('');

                /**
                 * Whether the contact id input should be disabled.
                 *
                 * @type {RbC.Ko.Boolean}
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
                 * @type {RbC.Ko.Boolean}
                 */
                me.invalidContactId = ko.computed(function() {
                    var address = getRelevantQuoteAddress();

                    return !CredentialsLib.validate(
                        me.contactId(),
                        address.countryId || '',
                        CheckoutModel.isCompany()
                    );
                });

                /**
                 * The availability status of the payment method.
                 *
                 * @type {RbC.Ko.Boolean}
                 */
                me.isAvailable = ko.observable(isAvailable(me.getCode()));

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
                    var method = CheckoutConfigLib.getPaymentMethod(
                        me.getCode()
                    );

                    var result = '';

                    if (method) {
                        result = method.title;
                    }

                    return (typeof result === 'string' && result !== '') ?
                        result :
                        me._super();
                };

                /**
                 * Retrieve configured USP for currently selected payment
                 * method.
                 *
                 * @returns {string}
                 */
                me.getUsp = function () {
                    var method = CheckoutConfigLib.getPaymentMethod(
                        me.getCode()
                    );

                    var result = '';

                    if (method) {
                        result = method.usp;
                    }

                    return (typeof result === 'string' && result !== '') ?
                        result :
                        '';
                };

                /**
                 * Whether all requirements for an order placement has been met.
                 *
                 * @type {RbC.Ko.Boolean}
                 */
                me.canPlaceOrder = ko.computed(function () {
                    var idResult =
                        !me.hasSsnField ||
                        (me.govId() !== '' && !me.invalidGovId());

                    var companyResult =
                        !me.isCompanyCustomer() ||
                        (me.contactId() !== '' && !me.invalidContactId());

                    return me.isAvailable() &&
                        idResult &&
                        companyResult &&
                        me.isPlaceOrderActionAllowed();
                });

                // noinspection JSUnusedLocalSymbols
                /**
                 * Starts the order placement process.
                 *
                 * @param {object} data - Data that KnockoutJS supplies.
                 * @param {object} event
                 */
                me.resursBankPlaceOrder = function (
                    data,
                    event
                ) {
                    if (!me.isResursInternalMethod()) {
                        me.placeOrder(data, event);
                    } else if (me.canPlaceOrder()) {
                        SessionLib.setSessionData({
                            gov_id: me.govId(),
                            is_company: me.isCompanyCustomer(),
                            method_code: me.getCode(),

                            contact_gov_id:
                                me.isCompanyCustomer() ?
                                    me.contactId() :
                                    null
                        }).done(function (response) {
                            onSetSessionDataDone(response, data, event);
                        });
                    }
                };

                /**
                 * Action taken after order has successfully been created.
                 */
                me.afterPlaceOrder = function () {
                    redirectOnSuccessAction.redirectUrl = url.build(
                        'resursbank_simplified/checkout/redirect'
                    );
                };

                /**
                 * @param {Simplified.Lib.FetchAddress.Response} response
                 * @param {object} data - Data that KnockoutJS supplies.
                 * @param {object} event
                 */
                function onSetSessionDataDone (
                    response,
                    data,
                    event
                ) {
                    if (response.error.message === '') {
                        me.placeOrder(data, event);
                    } else {
                        me.messageContainer.addErrorMessage({
                            message: response.error.message
                        });
                    }
                }

                (function init() {
                    if (hasLegalInfoLink(me.getCode())) {
                        Layout([{
                            parent: me.name,
                            name: me.name + '.legal-info',
                            displayArea: 'legal-info-link',
                            component: 'Resursbank_Core/js/view/read-more',
                            config: {
                                modalComponent: 'Resursbank_Core/js/view/remodal-checkout',
                                methodCode: me.getCode(),
                                modalTitle: '',
                                requestFn: function () {
                                    var method = Quote.paymentMethod();
                                    var gt = parseFloat(
                                        Quote.totals().base_grand_total
                                    );
                                    var result;

                                    if (method !== null && !Number.isNaN(gt)) {
                                        result = ReadMoreLib.getCostOfPurchase(
                                            gt,
                                            method.method,
                                            CheckoutConfigLib.getFormKey()
                                        );
                                    }

                                    return result;
                                }
                            }
                        }]);
                    }

                    // Subscriber to change the availability status of the
                    // payment method when the customer type changes.
                    CheckoutModel.isCompany.subscribe(function () {
                        me.isAvailable(isAvailable(me.getCode()));
                    });
                }());
            }
        });
    }
);
