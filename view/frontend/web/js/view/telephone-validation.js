/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Telephone number is required for Norwegian customers since it's used instead
 * of an SSN. We need to validate the telephone number using the same regex:es
 * used by Resurs Banks API, otherwise the API may throw an Exception when
 * attempting to create the requested payment.
 *
 * There are more appropriate ways of adding a validation rule, but we setup
 * the phone validation of the simplified checkout page as a component because:
 *
 * 1. We need to know the chosen country when we register the validation
 * method and perform validation, and to do that we need to reach the country
 * field.
 * 2. We need to reach the phone field to register the validation method on it.
 * Afterwards the method will be called for every input in that field.
 *
 * So the phone field and country field must be loaded before we can register
 * the validation method. If you look inside the layout file you will see these
 * fields added as dependencies to this component.
 */
define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'uiRegistry',
        'uiComponent',
        'Magento_Ui/js/lib/validation/validator',
        'Resursbank_Simplified/js/lib/credentials',
        'Resursbank_Simplified/js/lib/checkout'
    ],
    /**
     * @param {jQuery} $
     * @param ko
     * @param translate
     * @param uiRegistry
     * @param Component
     * @param validator
     * @param Credentials {Simplified.Lib.Credentials}
     * @param Checkout {Simplified.Lib.Checkout}
     * @returns {*}
     */
    function (
        $,
        ko,
        translate,
        uiRegistry,
        Component,
        validator,
        Credentials,
        Checkout
    ) {
        'use strict';

        /**
         * Country ID of the chosen country during checkout.
         *
         * @type {string}
         */
        var chosenCountryId = '';

        /**
         * Whether the validation has been initialized.
         *
         * @type {boolean}
         */
        var initialized = false;

        /**
         * Validation function for phone numbers.
         *
         * @param {string} value
         * @return {boolean}
         */
        function validatePhoneNumber(value) {
            return Credentials.isCountryAllowed(chosenCountryId) ?
                Credentials.validatePhone(value, chosenCountryId) :
                true;
        }

        /**
         * Initialization function for the component. Calling it more than once
         * will have no effect.
         */
        function init() {
            if (!initialized) {
                // noinspection JSUnresolvedVariable
                validator.addRule(
                    'resursbank-checkout-telephone',
                    validatePhoneNumber,
                    $.mage.__(
                        'Please provide a valid phone number for your chosen ' +
                        'country.'
                    )
                );

                initialized = true;
            }
        }

        return Component.extend({
            initialize: function() {
                var addressInputs = Checkout.getAddressInputs();
                var phone = addressInputs.telephone;
                var country = addressInputs.country;

                this._super();

                init();

                if (typeof country !== 'undefined') {
                    country.value.subscribe(function(value) {
                        chosenCountryId = value;
                    });

                    chosenCountryId = country.value();
                }

                if (typeof phone !== 'undefined') {
                    phone.setValidation(
                        'resursbank-checkout-telephone',
                        true
                    );

                    if (phone.value() === '') {
                        // Phone field will validate after we use
                        // setValidation() so we need to remove any errors from
                        // that if the field is empty.
                        phone.error(false);
                    }
                }
            }
        });
    }
);
