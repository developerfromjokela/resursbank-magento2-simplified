/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
/**
 * This file serves as the initial local storage setup for the module. All
 * other sections for the local storage within the Simplified module should
 * be added to the namespace that this file adds to the local storage.
 */
define(
    [
        'jquery',
        'jquery/jquery-storageapi'
    ],

    /**
     * @param {jQuery} $
     * @returns {Readonly<Simplified.Storage>}
     */
    function ($) {
        'use strict';

        var cacheKey = 'resursbank-simplified';
        var storage = $.initNamespaceStorage(cacheKey).localStorage;

        /**
         * @namespace Simplified.Storage
         * @constant
         */
        var EXPORT = {
            /**
             * Returns all data under the "resursbank-simplified" namespace.
             *
             * @returns {*}
             */
            getData: function () {
                return storage.get();
            },

            /**
             * Adds a sub-section to the "resursbank-simplified" namespace.
             *
             * @param {string} sectionName
             * @param {*} data
             */
            set: function (sectionName, data) {
                storage.set(sectionName, data);
            },

            /**
             * Returns the data for a section defined in the
             * "resursbank-simplified" namespace.
             *
             * @param {string} sectionName
             * @returns {*}
             */
            get: function (sectionName) {
                return storage.get(sectionName);
            },

            /**
             * @link https://github.com/julien-maurel/jQuery-Storage-API#remove
             * @param {string|string[]} path
             * @param {...string} path
             * @returns {*}
             */
            remove: function (path) {
                return storage.remove(arguments);
            }
        };

        return Object.freeze(EXPORT);
    }
);
