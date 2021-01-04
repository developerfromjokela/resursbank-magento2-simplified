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
        'Magento_Checkout/js/model/quote',
        'Resursbank_Simplified/js/lib/checkout-config',
        'Resursbank_Simplified/js/lib/credentials',
        'Resursbank_Simplified/js/lib/fetch-address'
    ],
    /**
     *
     * @param $
     * @param ko
     * @param translate
     * @param Component
     * @param quote
     * @param {Simplified.Lib.CheckoutConfig} CheckoutConfig
     * @param {Simplified.Lib.Credentials} Credentials
     * @param {Simplified.Lib.FetchAddress} FetchAddress
     * @returns {*}
     */
    function (
        $,
        ko,
        translate,
        Component,
        quote,
        CheckoutConfig,
        Credentials,
        FetchAddress
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Resursbank_Simplified/fetch-address'
            },

            initialize: function () {
                var me = this;

                me._super();

                me.idNumber = ko.observable('');
                me.selectedCustomerType = ko.observable('person');
                me.isCompanyCustomer = ko.computed(function() {
                    return me.selectedCustomerType() === 'company';
                });

                me.isFetchingAddress = ko.observable(false);
                me.isAddressFetched = ko.observable(false);
                me.failedToFetchAddressError = ko.observable('');
                me.isFetchingEnabled = ko.computed(function() {
                    return !me.isAddressFetched() && !me.isFetchingAddress();
                });

                me.showComponent = ko.computed(function() {
                    return CheckoutConfig.getDefaultCountryId() === 'SE';
                });
                me.hasError = ko.computed(function() {
                    return me.failedToFetchAddressError() !== '';
                });

                me.fetchAddress = function () {
                    var isIdValid;

                    if (!me.isFetchingAddress()) {
                        isIdValid = Credentials.validate(
                            me.idNumber(),
                            'SE',
                            me.isCompanyCustomer()
                        );

                        if (isIdValid) {
                            me.isFetchingAddress(true);

                            FetchAddress.fetchAddress(
                                me.idNumber(),
                                me.isCompanyCustomer()
                            ).done(function (response) {
                                // Implement details.
                            }).always(function () {
                                // Implement details.
                            });
                        } else {
                            me.failedToFetchAddressError(
                                $.mage.__('Wrong SSN/Org. number.')
                            );
                        }
                    }
                };

                me.removeAddress = function () {
                    console.log('REMOVE ADDRESS');
                    me.isFetchingAddress(false);
                }
            }
        });
    }
);
