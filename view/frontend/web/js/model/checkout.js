/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

define(
    [
        'ko'
    ],
    /**
     * @param ko
     * @returns {Readonly<Simplified.Model.Checkout>}
     */
    function (
        ko
    ) {
        'use strict';

        /**
         * @callback Simplified.Model.Checkout.isCompany
         * @param {boolean} [value]
         * @return {boolean}
         */

        /**
         * @constant
         * @type {object}
         */
        var PRIVATE = Object.freeze({
            /**
             * @type {Simplified.Model.Checkout.isCompany}
             */
            isCompany: ko.observable(false)
        });

        /**
         * @namespace Simplified.Model.Checkout
         * @constant
         */
        var EXPORT = {
            /**
             * The selected payment method of the iframe.
             *
             * @type {Simplified.Model.Checkout.isCompany}
             */
            isCompany: ko.computed({
                read: function () {
                    return PRIVATE.isCompany();
                },

                write: function (value) {
                    if (typeof value === 'boolean') {
                        PRIVATE.isCompany(value);
                    }
                }
            })
        };

        return Object.freeze(EXPORT);
    }
);
