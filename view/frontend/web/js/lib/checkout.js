/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
define(
    [
        'uiRegistry'
    ],
    /**
     * @param uiRegistry
     * @returns Readonly<Simplified.Lib.Checkout>
     */
    function (
        uiRegistry
    ) {
        'use strict';

        /**
         * @typedef {object} Simplified.Lib.Checkout.AddressInputs
         * @property {object} firstname
         * @property {object} lastname
         * @property {object} company
         * @property {object} street0
         * @property {object} street1
         * @property {object} street2
         * @property {object} postcode
         * @property {object} country
         * @property {object} city
         * @property {object} telephone
         */

        /**
         * @constant
         * @namespace Simplified.Lib.Checkout
         */
        var EXPORT = {
            /**
             * Returns the JS-components of the input fields in the simplified
             * checkout.
             *
             * @returns {Simplified.Lib.Checkout.AddressInputs}
             */
            getAddressInputs: function() {
                var path =
                    'checkout.steps.shipping-step.shippingAddress' +
                    '.shipping-address-fieldset';

                return {
                    firstname: uiRegistry.get(path + '.firstname'),
                    lastname: uiRegistry.get(path + '.lastname'),
                    company: uiRegistry.get(path + '.company'),
                    street0: uiRegistry.get(path + '.street.0'),
                    street1: uiRegistry.get(path + '.street.1'),
                    street2: uiRegistry.get(path + '.street.2'),
                    postcode: uiRegistry.get(path + '.postcode'),
                    country: uiRegistry.get(path + '.country_id'),
                    city: uiRegistry.get(path + '.city'),
                    telephone: uiRegistry.get(path + '.telephone')
                };
            },

            /**
             * Applies a fetched shipping address to the shipping address
             * input fields in the simplified checkout.
             *
             * @param {Simplified.Lib.FetchAddress.Address} address
             * @return {Readonly<Simplified.Lib.Checkout>}
             */
            applyAddress: function(address) {
                var inputs = EXPORT.getAddressInputs();

                inputs.firstname.value(address.firstname);
                inputs.lastname.value(address.lastname);
                inputs.city.value(address.city);
                inputs.postcode.value(address.postcode);
                inputs.street0.value(address.street0);
                inputs.street1.value(address.street1);
                inputs.country.value(address.country);
                inputs.company.value(address.company);

                return EXPORT;
            },

            /**
             * Removes an applied shipping address from the input fields of
             * the simplified checkout. The input fields will have their
             * initial values restored.
             *
             * @return {Readonly<Simplified.Lib.Checkout>}
             */
            removeAddress: function() {
                var inputs = EXPORT.getAddressInputs();

                inputs.firstname.reset();
                inputs.lastname.reset();
                inputs.city.reset();
                inputs.postcode.reset();
                inputs.street0.reset();
                inputs.street1.reset();
                inputs.country.reset();
                inputs.company.reset();

                return EXPORT;
            }
        };

        return Object.freeze(EXPORT);
    }
);
