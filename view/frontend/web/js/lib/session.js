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
     * @returns Readonly<Simplified.Lib.Session>
     */
    function (
        $,
        Url,
        CheckoutConfig
    ) {
        'use strict';

        /**
         * @typedef {object} Simplified.Lib.Session.RequestData
         * @property {string} gov_id
         * @property {boolean} is_company
         * @property {string} method_code
         * @property {(string|null)} contact_gov_id
         * @property {(string|null)} card_number
         * @property {(number|null)} card_amount
         */

        /**
         * @typedef {object} Simplified.Lib.Session.RequestParameter
         * @property {string} gov_id
         * @property {boolean} is_company
         * @property {string} form_key
         * @property {string} method_code
         * @property {string} [contact_gov_id]
         * @property {string} [card_number]
         * @property {number} [card_amount]
         */

        /**
         * @typedef {object} Simplified.Lib.Session.Call
         * @property {string} type
         * @property {string} url
         * @property {Simplified.Lib.Session.RequestParameter} data
         */

        /**
         * @typedef {object} Simplified.Lib.Session.Error
         * @property {string} message
         */

        /**
         * @typedef {object} Simplified.Lib.Session.Response
         * @property {Simplified.Lib.FetchAddress.Error} error
         */

        /**
         * @constant
         * @namespace Simplified.Lib.Session
         */
        var EXPORT = {
            /**
             * Sends a request to the server that sets the given information
             * in the session for later use.
             *
             * @param {Simplified.Lib.Session.RequestData} data
             * @return {jQuery}
             */
            setSessionData: function (data) {
                return $.ajax(EXPORT.getSetSessionCall(data));
            },

            /**
             * Produces a call object which can make a request to apply data in
             * the PHP session.
             *
             * @param {Simplified.Lib.Session.RequestData} data
             * @returns {Simplified.Lib.Session.Call}
             */
            getSetSessionCall: function (data) {
                /**
                 * @type {Simplified.Lib.Session.RequestParameter}
                 */
                var requestData = {
                    gov_id: data.gov_id,
                    is_company: data.is_company,
                    form_key: CheckoutConfig.getFormKey(),
                    method_code: data.method_code
                };

                if (typeof data.contact_gov_id === 'string') {
                    requestData.contact_gov_id = data.contact_gov_id;
                }

                if (typeof data.card_number === 'string') {
                    requestData.card_number = data.card_number;
                }

                if (typeof data.card_amount === 'number') {
                    requestData.card_amount = data.card_amount;
                }

                if (typeof data.method_code === 'string') {
                    requestData.method_code = data.method_code;
                }

                return {
                    type: 'POST',
                    url: EXPORT.buildUrl('checkout/session'),
                    data: requestData
                };
            },

            /**
             * Builds a URL to connect to a controller in the Simplified module.
             *
             * @param {string} path - Controller path. Do not start with "/".
             * @returns {string} URL to a controller in the Simplified module.
             */
            buildUrl: function (path) {
                return Url.build('resursbank_simplified/' + path);
            }
        };

        return Object.freeze(EXPORT);
    }
);
