/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

define(
    [
        'jquery',
        'Resursbank_Simplified/js/model/checkout',
        'Resursbank_Simplified/js/storage/checkout',
        'Resursbank_Simplified/js/lib/credentials'
    ],
    /**
     * @param {*} $
     * @param {Simplified.Model.Checkout} Model
     * @param {Simplified.Storage.Checkout} Storage
     * @param {Simplified.Lib.Credentials} Credentials
     * @returns {Readonly<Rco.Action.Checkout>}
     */
    function (
        $,
        Model,
        Storage,
        Credentials
    ) {
        /**
         * @constant
         * @namespace Simplified.Action.Checkout
         */
        var EXPORT = {
            /**
             * @param {boolean} value
             * @return {Readonly<Simplified.Action.Checkout>}
             */
            setIsCompany: function (value) {
                Storage.setIsCompany(value);
                Model.isCompany(value);

                return EXPORT;
            },

            /**
             * @param {string} value - Numeric string.
             * @return {Readonly<Simplified.Action.Checkout>}
             */
            setGovId: function (value) {
                Storage.setGovId(value);
                Model.govId(value);

                return EXPORT;
            },

            /**
             * @return {Readonly<Simplified.Action.Checkout>}
             */
            removeGovId: function () {
                Storage.removeGovId();
                Model.govId('');

                return EXPORT;
            }
        };

        return Object.freeze(EXPORT);
    }
);
