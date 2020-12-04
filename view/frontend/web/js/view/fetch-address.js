/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'uiComponent',
        'Resursbank_Simplified/js/lib/checkout-config',
        'Resursbank_Simplified/js/lib/credentials',
        'Resursbank_Simplified/js/lib/fetch-address',
        'Resursbank_Simplified/js/lib/checkout',
        'Resursbank_Simplified/js/action/checkout',
        'Resursbank_Simplified/js/model/checkout',
        'Resursbank_Simplified/js/storage/checkout'
    ],

    /**
     * @param {jQuery} $
     * @param ko
     * @param translate
     * @param Component
     * @param {Simplified.Lib.CheckoutConfig} CheckoutConfig
     * @param {Simplified.Lib.Credentials} Credentials
     * @param {Simplified.Lib.FetchAddress} FetchAddress
     * @param {Simplified.Lib.Checkout} CheckoutLib
     * @param {Simplified.Action.Checkout} CheckoutAction
     * @param {Simplified.Model.Checkout} CheckoutModel
     * @param {Simplified.Storage.Checkout} CheckoutStorage
     * @returns {*}
     */
    function (
        $,
        ko,
        translate,
        Component,
        CheckoutConfig,
        Credentials,
        FetchAddress,
        CheckoutLib,
        CheckoutAction,
        CheckoutModel,
        CheckoutStorage
    ) {
        'use strict';

        /**
         * @callback Simplified.Observable.String
         * @param {string} [value]
         * @return {string}
         */

        /**
         * @callback Simplified.Observable.Boolean
         * @param {boolean} [value]
         * @return {boolean}
         */

        /**
         * @callback Simplified.Observable.Number
         * @param {number} [value]
         * @return {number}
         */

        /**
         * @type {string}
         * @constant
         */
        var CUSTOMER_TYPE_PERSON = 'person';

        /**
         * @type {string}
         * @constant
         */
        var CUSTOMER_TYPE_COMPANY = 'company';

        /**
         * Self-invoking initialization function, because it should only be
         * used once and is therefore unnecessary to allocate memory for.
         *
         * The name is not required but gives clarity as to what this function
         * does and helps when debugging.
         */
        (function init() {
            var storedIsCompany = CheckoutStorage.getIsCompany();

            CheckoutAction.setIsCompany(
                typeof storedIsCompany === 'boolean' && storedIsCompany
            );
        }());

        return Component.extend({
            defaults: {
                template: 'Resursbank_Simplified/fetch-address'
            },

            initialize: function () {
                var me = this;

                me._super();

                /**
                 * @type {string}
                 */
                me.customerTypeCompany = CUSTOMER_TYPE_COMPANY;

                /**
                 * @type {string}
                 */
                me.customerTypePerson = CUSTOMER_TYPE_PERSON;

                /**
                 * Whether a request has been sent to fetch a shipping address
                 * for the applied ID-number.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.isFetchingAddress = ko.observable(false);

                /**
                 * Whether a shipping address has been fetched and applied.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.isAddressApplied = ko.observable(false);

                /**
                 * Whether an error occurred when fetching the shipping
                 * address.
                 *
                 * @type {Simplified.Observable.String}
                 */
                me.failedToFetchAddressError = ko.observable('');

                /**
                 * The customer's ID-number.
                 *
                 * @type {Simplified.Observable.String}
                 */
                me.idNumber = ko.observable('');

                /**
                 * The selected customer type.
                 *
                 * @type {Simplified.Observable.String}
                 */
                me.selectedCustomerType = ko.observable(
                    CheckoutModel.isCompany() ?
                        CUSTOMER_TYPE_COMPANY :
                        CUSTOMER_TYPE_PERSON
                );

                /**
                 * Whether a request can be sent to fetch an address.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.isFetchingEnabled = ko.computed(function () {
                    return !me.isAddressApplied() && !me.isFetchingAddress()
                });

                /**
                 * Whether the ID-number input should be disabled.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.disableIdInput = ko.computed(function () {
                    return !me.isFetchingEnabled();
                });

                /**
                 * Whether the customer selection inputs should be disabled.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.disableCustomerTypeSelection = ko.computed(function () {
                    return me.isFetchingAddress() || me.isAddressApplied();
                });

                /**
                 * Whether the customer is a company or not.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.isCompanyCustomer = ko.computed(function () {
                    return me.selectedCustomerType() === CUSTOMER_TYPE_COMPANY;
                });

                /**
                 * Whether the component should be displayed.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.showComponent = ko.computed(function () {
                    return CheckoutConfig.getDefaultCountryId() === 'SE';
                });

                /**
                 * Whether the component has an error. Necessary to apply the
                 * correct CSS classes to the component's HTML.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.hasError = ko.computed(function () {
                    return me.failedToFetchAddressError() !== '';
                });

                /**
                 * Fetches the address of the given SSN/Org. nr. If the number
                 * is invalid, the address cannot be fetched and an error
                 * message will be displayed underneath the ID-number input.
                 */
                me.fetchAddress = function () {
                    if (!me.isFetchingAddress() && validateId()) {
                        me.failedToFetchAddressError('');
                        me.isFetchingAddress(true);

                        FetchAddress
                            .fetchAddress(
                                me.idNumber(),
                                me.isCompanyCustomer()
                            )
                            .done(onFetchAddressDone)
                            .fail(onFetchAddressFail)
                            .always(onFetchAddressAlways)
                    }
                };

                /**
                 * Removes the fetched address (if an address has been fetched),
                 * resetting address fields to their initial values. The
                 * ID-number input will also be emptied.
                 */
                me.removeAddress = function () {
                    CheckoutLib.removeAddress();

                    me.isAddressApplied(false);
                    me.idNumber('');
                };

                me.onCustomerTypeChange = function () {
                    CheckoutAction.setIsCompany(me.isCompanyCustomer());
                };

                /**
                 * Callback for when the fetch address request was successful.
                 *
                 * @param {Simplified.Lib.FetchAddress.Response} response
                 */
                function onFetchAddressDone(response) {
                    if (response.error.message !== '') {
                        me.failedToFetchAddressError(response.error.message);
                    } else if (Object.keys(response.address).length > 0) {
                        CheckoutLib.applyAddress(response.address);

                        me.isAddressApplied(true)
                    }
                }

                /**
                 * Callback for when the fetch address callback fails.
                 */
                function onFetchAddressFail() {
                    me.failedToFetchAddressError($.mage.__(
                        'Something went wrong when fetching ' +
                        'the address. Please try again.'
                    ));
                }

                /**
                 * Callback for when the fetch address completes.
                 */
                function onFetchAddressAlways() {
                    me.isFetchingAddress(false);
                }

                /**
                 * Validates the applied ID-number and displays relevant error
                 * messages.
                 *
                 * @returns {boolean}
                 */
                function validateId() {
                    var valid = Credentials.validate(
                        me.idNumber(),
                        'SE',
                        me.isCompanyCustomer()
                    );

                    if (!valid) {
                        me.failedToFetchAddressError(
                            $.mage.__('Wrong SSN/Org. number.')
                        );
                    }

                    return valid;
                }
            }
        });
    }
);
