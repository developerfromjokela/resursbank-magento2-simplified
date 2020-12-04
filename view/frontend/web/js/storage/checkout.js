/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
define(
    [
        'jquery',
        'Resursbank_Simplified/js/storage/resursbank-simplified',
        'jquery/jquery-storageapi',
    ],

    /**
     * @param {jQuery} $
     * @param {*} storage
     * @returns {Readonly<Simplified.Storage.Checkout>}
     */
    function (
        $,
        storage
    ) {
        'use strict';

        /**
         * @typedef {object} Simplified.Storage.Checkout.Data
         * @property {string|undefined} isCompany
         */

        /**
         * @type {string}
         */
        var cacheKey = 'checkout';

        /**
         * @namespace Simplified.Storage.Checkout
         * @constant
         */
        var EXPORT = {
            /**
             * Returns all data in the "checkout" section.
             *
             * NOTE: The data returned may not include all fields, some may be
             * uninitialized.
             *
             * @returns {Simplified.Storage.Checkout.Data|undefined}
             */
            getData: function () {
                return storage.get(cacheKey);
            },

            /**
             * @param {boolean} value
             * @throws {Error}
             */
            setIsCompany: function (value) {
                var data;

                if (typeof value !== 'boolean') {
                    throw Error(
                        'Local storage key [isCompany] expects a boolean value.'
                    );
                }

                data = EXPORT.getData();

                if (typeof data !== 'undefined') {
                    data.isCompany = JSON.stringify(value);
                }

                storage.set(cacheKey, data);
            },

            /**
             * @return {boolean|undefined}
             */
            getIsCompany: function () {
                var data = EXPORT.getData();

                /**
                 * @type {string|undefined}
                 */
                var value = typeof data !== 'undefined' ?
                    data.isCompany :
                    data;

                return typeof value !== 'undefined' ?
                    JSON.parse(value) :
                    value;
            },

            /**
             * @return {boolean}
             */
            removeIsCompany: function () {
                return storage.remove('isCompany');
            }
        };

        /**
         * Self-invoking initialization function, because it should only be
         * used once and is therefore unnecessary to allocate memory for.
         *
         * The name is not required but gives clarity as to what this function
         * does and helps when debugging.
         */
        (function init() {
            if (typeof storage.get(cacheKey) === 'undefined') {
                storage.set(cacheKey, {});
            }
        }());

        return Object.freeze(EXPORT);
    }
);
