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

        // /**
        //  * Event handler for when the fetch address button is clicked.
        //  */
        // function onFetchBtnClick() {
        //     var idNum;
        //     var countryIso;
        //     var customerType;
        //     var valid;
        //
        //     if (!model.fetchingAddress() && !isAddressFetched()) {
        //         idNum = selectedIdNumber();
        //         countryIso = model.getConfigCountryIso();
        //         customerType = selectedCustomerType();
        //         valid = Credentials.validate(idNum, countryIso, customerType);
        //
        //         failedToFetchAddress(!valid);
        //
        //         if (valid) {
        //             model.fetchAddress(idNum, countryIso, customerType)
        //                 .done(function(response) {
        //                     var inputs = getInputs();
        //                     var data;
        //
        //                     if (typeof inputs !== 'undefined' &&
        //                         response.hasOwnProperty('address')
        //                     ) {
        //                         isAddressFetched(true);
        //                         data = response.address;
        //
        //                         Object.keys(response.address)
        //                             .forEach(function(key) {
        //                                 var el;
        //
        //                                 if (inputs.hasOwnProperty(key)) {
        //                                     el = inputs[key];
        //                                     el.value = data[key];
        //
        //                                     ko.utils.triggerEvent(el, 'keyup');
        //                                 }
        //                             });
        //                     }
        //                 }).fail(function(response, msg) {
        //                     failedToFetchAddressError(msg);
        //                     failedToFetchAddress(true);
        //             });
        //         } else {
        //             failedToFetchAddressError(
        //                 $.mage.__('Wrong SSN/Org. number.')
        //             );
        //         }
        //     }
        // }
        //
        // /**
        //  * Event handler for when the remove address button is clicked.
        //  */
        // function onRemoveAddressBtnClick() {
        //     if (isAddressFetched()) {
        //         removeAddress();
        //     }
        // }
        //
        // /**
        //  * Fetches the input fields from the checkout page and returns them
        //  * in an object where each key is the value of the "name" attribute of
        //  * the input.
        //  *
        //  * @returns {object|undefined}
        //  */
        // function getInputs() {
        //     var list;
        //     var els = $(
        //         '#co-shipping-form .field[name^="shippingAddress"] input'
        //     );
        //
        //     if (els.length > 0) {
        //         list = {};
        //         els.each(function(i, el) {
        //             var filteredName = el.name
        //                 .replace(/\[/g, '')
        //                 .replace(/\]/g, '');
        //
        //             list[filteredName] = el;
        //         });
        //     }
        //
        //     return list;
        // }
        //
        // /**
        //  * Removes the fetched address information from the checkout fields and
        //  * from the stored id number from the session.
        //  */
        // function removeAddress() {
        //     var inputs;
        //
        //     if (isAddressFetched() === true) {
        //         model.setIdNumber('').done(function () {
        //             inputs = getInputs();
        //
        //             if (typeof inputs !== 'undefined') {
        //                 Object.keys(inputs).forEach(function (key) {
        //                     inputs[key].value = '';
        //                 });
        //             }
        //
        //             isAddressFetched(false);
        //         });
        //     }
        // }

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
