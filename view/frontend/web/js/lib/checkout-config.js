/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
define(
    [],
    /**
     * @returns Readonly<Simplified.Lib.CheckoutConfig>
     */
    function () {
        'use strict';

        /**
         * @typedef {object} Simplified.Lib.CheckoutConfig.PaymentMethod
         * @property {string} code
         * @property {string} type
         * @property {string} title
         * @property {number} maxOrderTotal
         * @property {string} specificType
         */

        /**
         * Includes functions that handle Magento's global frontend object
         * "window.checkoutConfig".
         *
         * @constant
         * @namespace Simplified.Lib.CheckoutConfig
         */
        var EXPORT = {
            /**
             * Returns the default country the store is configured to as a
             * valid two-letter ISO 3166 code.
             *
             * @returns {string} An empty string will be returned if the ISO
             * code couldn't be found.
             */
            getDefaultCountryId: function() {
                return window.hasOwnProperty('checkoutConfig') &&
                    typeof window.checkoutConfig.defaultCountryId === 'string' ?
                        window.checkoutConfig.defaultCountryId :
                        '';
            },

            /**
             * Returns the form key to make server requests with.
             *
             * @returns {string} An empty string will be returned if the form
             * key couldn't be found.
             */
            getFormKey: function() {
                return window.hasOwnProperty('formKey') &&
                    typeof window.formKey === 'string' ?
                        window.checkoutConfig.formKey :
                        '';
            },

            /**
             * Returns an object containing the data for all of the available
             * Resurs Bank payment methods.
             *
             * @returns {Simplified.Lib.CheckoutConfig.PaymentMethod[]}
             */
            getPaymentMethods: function() {
                var config = window.checkoutConfig;

                /**
                 * @type {Simplified.Lib.CheckoutConfig.PaymentMethod[]}
                 */
                var result = [];

                if (config.hasOwnProperty('payment') &&
                    config.payment.hasOwnProperty('resursbank_simplified') &&
                    Array.isArray(config.payment.resursbank_simplified.methods)
                ) {
                    result = config.payment.resursbank_simplified.methods;
                }

                return result;
            },

            /**
             * Returns the data for a Resurs Bank payment method using the
             * provided payment method code.
             *
             * @returns {
             * (Simplified.Lib.CheckoutConfig.PaymentMethod|undefined)
             * } The data for a Resurs Bank payment method. If the code does
             * not point to a Resurs Bank payment method in the
             * "window.checkoutConfig" object, undefined is returned.
             */
            getPaymentMethod: function(code) {
                return EXPORT.getPaymentMethods()
                    .filter(function (method) {
                        return method.code === code;
                    })[0];
            }
        };

        return Object.freeze(EXPORT);
    }
);
