/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'uiComponent',
        'Resursbank_Simplified/js/lib/checkout-config',
        'Resursbank_Simplified/js/lib/credentials',
        'Resursbank_Simplified/js/lib/fetch-address',
        'Resursbank_Simplified/js/lib/checkout'
    ],
    /**
     * @param $
     * @param ko
     * @param translate
     * @param Component
     * @param {Simplified.Lib.CheckoutConfig} CheckoutConfig
     * @param {Simplified.Lib.Credentials} Credentials
     * @param {Simplified.Lib.FetchAddress} FetchAddress
     * @param {Simplified.Lib.Checkout} Checkout
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
        Checkout
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
         * @param {boolean} [value]
         * @return {boolean}
         */

        return Component.extend({
            defaults: {
                template: 'Resursbank_Simplified/fetch-address'
            },

            initialize: function () {
                var me = this;

                me._super();

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
                me.selectedCustomerType = ko.observable('private_person');

                /**
                 * Whether a request can be sent to fetch an address.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.isFetchingEnabled = ko.computed(function() {
                    return !me.isAddressApplied() && !me.isFetchingAddress()
                });

                /**
                 * Whether the ID-number input should be disabled.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.disableIdInput = ko.computed(function() {
                    return !me.isFetchingEnabled();
                });

                /**
                 * Whether the customer selection inputs should be disabled.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.disableCustomerTypeSelection = ko.computed(function() {
                    return me.isFetchingAddress() || me.isAddressApplied();
                });

                /**
                 * Whether the customer is a company or not.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.isCompanyCustomer = ko.computed(function() {
                    return me.selectedCustomerType() === 'company';
                });

                /**
                 * Whether the component should be displayed.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.showComponent = ko.computed(function() {
                    return CheckoutConfig.getDefaultCountryId() === 'SE';
                });

                /**
                 * Whether the component has an error. Necessary to apply the
                 * correct CSS classes to the component's HTML.
                 *
                 * @type {Simplified.Observable.Boolean}
                 */
                me.hasError = ko.computed(function() {
                    return me.failedToFetchAddressError() !== '';
                });

                /**
                 * Callback for when the fetch address request was successful.
                 *
                 * @param {Simplified.Lib.FetchAddress.Response} response
                 */
                function onFetchAddressDone(response) {
                    if (response.error.message !== '') {
                        me.failedToFetchAddressError(response.error.message);
                    } else if (Object.keys(response.address).length > 0) {
                        Checkout.applyAddress(response.address);

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
                    Checkout.removeAddress();

                    me.isAddressApplied(false);
                    me.idNumber('');
                }
            }
        });
    }
);
