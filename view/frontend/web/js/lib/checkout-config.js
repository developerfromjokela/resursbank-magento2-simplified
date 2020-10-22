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
            }
        };

        return Object.freeze(EXPORT);
    }
);
