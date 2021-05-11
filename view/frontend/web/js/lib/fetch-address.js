/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
define(
    [
        'jquery',
        'mage/url',
        'Resursbank_Simplified/js/lib/checkout-config'
    ],
    /**
     * @param {jQuery} $
     * @param Url
     * @param {Simplified.Lib.CheckoutConfig} CheckoutConfig
     * @returns Readonly<Simplified.Lib.FetchAddress>
     */
    function (
        $,
        Url,
        CheckoutConfig
    ) {
        'use strict';

        /**
         * @typedef {object} Simplified.Lib.FetchAddress.Address
         * @property {string} firstname
         * @property {string} lastname
         * @property {string} city
         * @property {string} company
         * @property {string} country
         * @property {string} postcode
         * @property {string} street0
         * @property {string} street1
         * @property {string} telephone
         */

        /**
         * @typedef {object} Simplified.Lib.FetchAddress.Error
         * @property {string} message
         */

        /**
         * @typedef {object} Simplified.Lib.FetchAddress.Call
         * @property {string} type
         * @property {string} url
         * @property {object} data
         * @property {string} data.identifier
         * @property {boolean} data.is_company
         * @property {string} data.form_key
         */

        /**
         * @typedef {object} Simplified.Lib.FetchAddress.Response
         * @property {Simplified.Lib.FetchAddress.Address} address
         * @property {Simplified.Lib.FetchAddress.Error} error
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
             * information for the supplied SSN/Org. nr (Sweden), or phone
             * number (Norway).
             *
             * @param {string} identifier
             * @param {boolean} isCompany
             * @return {jQuery}
             */
            fetchAddress: function (identifier, isCompany) {
                return $.ajax(EXPORT.getFetchAddressCall(
                    identifier,
                    isCompany)
                );
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
                    type: 'POST',
                    url: EXPORT.buildUrl('checkout/fetchAddress'),
                    data: {
                        identifier: idNum,
                        is_company: isCompany,
                        form_key: CheckoutConfig.getFormKey()
                    }
                };
            },

            /**
             * Builds a URL to connect to a controller in the Simplified module.
             *
             * @param {string} path - Path to a controller action. Should not
             * start with a "/".
             * @returns {string} URL to a controller in the Simplified module.
             */
            buildUrl: function (path) {
                return Url.build('resursbank_simplified/' + path);
            }
        };

        return Object.freeze(EXPORT);
    }
);
