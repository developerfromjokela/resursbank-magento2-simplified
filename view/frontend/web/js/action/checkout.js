/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

define(
    [
        'jquery',
        'Resursbank_Simplified/js/model/checkout',
        'Resursbank_Simplified/js/storage/checkout'
    ],
    /**
     * @param {*} $
     * @param {Simplified.Model.Checkout} Model
     * @param {Simplified.Storage.Checkout} Storage
     * @returns {Readonly<Rco.Action.Checkout>}
     */
    function (
        $,
        Model,
        Storage
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
            }
        };

        return Object.freeze(EXPORT);
    }
);
