/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
define(
    [
        'mage/url',
        'Resursbank_Simplified/js/lib/checkout-config'
    ],
    /**
     * @param Url
     * @param {Simplified.Lib.CheckoutConfig} CheckoutConfig
     * @returns Readonly<Simplified.Lib.FetchAddress>
     */
    function (
        Url,
        CheckoutConfig
    ) {
        'use strict';

        /**
         * @typedef {object} Simplified.Lib.FetchAddress.Call
         * @property {object} options
         * @property {string} options.method
         * @property {string} options.url
         * @property {object} options.data
         * @property {string} options.data.id_num
         * @property {boolean} options.data.is_company
         * @property {string} options.data.form_key
         */

        /**
         * Includes functions that handle Magento's global frontend object
         * "window.checkoutConfig".
         *
         * @constant
         * @namespace Simplified.Lib.FetchAddress
         */
        var EXPORT = {
            /**
             * Sends a request to the server that returns the address
             * information for the given SSN/Org. nr.
             *
             * @param {string} idNum
             * @param {boolean} isCompany
             * @return {jQuery}
             */
            fetchAddress: function (idNum, isCompany) {
                return $.ajax(EXPORT.getFetchAddressCall(idNum, isCompany));
            },

            /**
             * Produces a call object to make a request to fetch the address of
             * a given SSN/Org. nr.
             *
             * @param {string} idNum
             * @param {boolean} isCompany
             * @returns {Simplified.Lib.FetchAddress.Call}
             */
            getFetchAddressCall: function (idNum, isCompany) {
                return {
                    options: {
                        method: 'POST',
                        url: EXPORT.buildUrl('fetchAddress'),
                        data: {
                            id_num: idNum,
                            is_company: isCompany,
                            form_key: CheckoutConfig.getFormKey()
                        }
                    }
                };
            },

            /**
             * Builds a URL to connect to a controller in the Simplified module.
             *
             * @param {string} path - Path to a controller action. Should not
             *  start with a "/".
             * @returns {string} URL to a controller in the Simplified module.
             */
            buildUrl: function (path) {
                return Url.build('resursbank_simplified/' + path);
            }
        };

        return Object.freeze(EXPORT);
    }
);
