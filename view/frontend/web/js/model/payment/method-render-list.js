/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * @typedef {object} Simplified.Checkout.MethodRender
 * @property {string} component
 * @property {string} name
 * @property {string} methodCode
 * @property {string} displayArea
 * @property {object} config
 */

/**
 * @typedef {
 *  Array<Simplified.Checkout.MethodRender>
 * } Simplified.Checkout.MethodRenderList
 */

/**
 * @callback Simplified.Observable.MethodRenderList
 * @param {Simplified.Checkout.MethodRenderList} [value]
 * @return {Simplified.Checkout.MethodRenderList}
 */

define(
    [
        'ko'
    ],

    /**
     * @param ko
     * @return {Simplified.Observable.MethodRenderList}
     */
    function (
        ko
    ) {
        'use strict';

        return ko.observableArray([]);
    }
);
