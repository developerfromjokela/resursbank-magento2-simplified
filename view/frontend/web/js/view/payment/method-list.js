/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * This component adds all of our dynamic payment methods to be rendered in the
 * list of payment methods in the billing step of the checkout process.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Resursbank_Simplified/js/lib/checkout-config'
    ],
    /**
     * @param Component
     * @param RendererList
     * @param {Simplified.Lib.CheckoutConfig} CheckoutConfig
     * @returns {*}
     */
    function (
        Component,
        RendererList,
        CheckoutConfig
    ) {
        var methods = CheckoutConfig.getPaymentMethods();

        methods.forEach(function (method) {
            RendererList.push({
                config: {
                    sortOrder: method.sortOrder
                },
                type: method.code,
                component: 'Resursbank_Simplified/js/view/payment/method'
            });
        });

        return Component.extend({});
    }
);
