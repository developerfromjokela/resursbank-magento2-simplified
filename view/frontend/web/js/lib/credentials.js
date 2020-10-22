/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

define(
    [],
    function () {
        'use strict';

        /**
         * @constant
         * @namespace Simplified.Lib.Credentials
         */
        var EXPORT = {
            /**
             * Check if address fetching is allowed based on provided country.
             *
             * @param {string} country
             * @returns {boolean}
             */
            isCountryAllowed: function(country) {
                return EXPORT.isSweden(country) ||
                    EXPORT.isNorway(country) ||
                    EXPORT.isFinland(country);
            },

            /**
             * Check for the Swedish country id.
             *
             * @param {string} country
             * @returns {boolean}
             */
            isSweden: function(country) {
                return country === 'SE';
            },

            /**
             * Check for the Norwegian country id.
             *
             * @param {string} country
             * @returns {boolean}
             */
            isNorway: function(country) {
                return country === 'NO';
            },

            /**
             * Check for the Finnish country id.
             *
             * @param {string} country
             * @returns {boolean}
             */
            isFinland: function(country) {
                return country === 'FI';
            },

            /**
             * Validates an ssn or organization number.
             *
             * NOTE: The if-else is as intended to support the nestled business
             * logic.
             *
             * NOTE: Some countries do not require an SSN.
             *
             * @param {string} idNum
             * @param {string} country
             * @param {boolean} isCompany
             * @returns {boolean}
             */
            validate: function(idNum, country, isCompany) {
                var result = false;

                if (idNum !== '') {
                    if (EXPORT.isCountryAllowed(country)) {
                        if (isCompany) {
                            result = EXPORT.validateOrg(idNum, country);
                        } else {
                            result = EXPORT.validateSsn(idNum, country);
                        }
                    }
                } else {
                    result = true;
                }

                return result;
            },

            /**
             * Validates an SSN.
             *
             * NOTE: Validates for Sweden, Norway and Finland ONLY.
             *
             * @param {string} ssn
             * @param {string} country
             * @returns {boolean}
             */
            validateSsn: function(ssn, country) {
                var result = false;
                var norway = /^([0][1-9]|[1-2][0-9]|3[0-1])(0[1-9]|1[0-2])(\d{2})(\-)?([\d]{5})$/;
                var finland = /^([\d]{6})[\+-A]([\d]{3})([0123456789ABCDEFHJKLMNPRSTUVWXY])$/;
                var sweden = /^(18\d{2}|19\d{2}|20\d{2}|\d{2})(0[1-9]|1[0-2])([0][1-9]|[1-2][0-9]|3[0-1])(\-|\+)?([\d]{4})$/;

                if (EXPORT.isSweden(country)) {
                    result = sweden.test(ssn);
                } else if (EXPORT.isNorway(country)) {
                    result = norway.test(ssn);
                } else if (EXPORT.isFinland(country)) {
                    result = finland.test(ssn);
                }

                return result;
            },

            /**
             * Validates an organisation number.
             *
             * NOTE: Validates for Sweden, Norway and Finland ONLY.
             *
             * @param {string} org
             * @param {string} country
             * @returns {boolean}
             */
            validateOrg: function(org, country) {
                var result = false;
                var finland = /^((\d{7})(\-)?\d)$/;
                var sweden = /^(16\d{2}|18\d{2}|19\d{2}|20\d{2}|\d{2})(\d{2})(\d{2})(\-|\+)?([\d]{4})$/;
                var norway = /^([89]([ |-]?[0-9]){8})$/;

                if (EXPORT.isSweden(country)) {
                    result = sweden.test(org);
                } else if (EXPORT.isNorway(country)) {
                    result = norway.test(org);
                } else if (EXPORT.isFinland(country)) {
                    result = finland.test(org);
                }

                return result;
            },

            /**
             * Validates a card number.
             *
             * @param {string} num
             * @returns {boolean}
             */
            validateCard: function(num) {
                return num === '' ||
                    /^([1-9][0-9]{3}[ ]{0,1}[0-9]{4}[ ]{0,1}[0-9]{4}[ ]{0,1}[0-9]{4})$/.test(num);
            },

            /**
             * Validates a phone number based on a country.
             *
             * @param {string} num
             * @param {string} country
             * @returns {boolean}
             */
            validatePhone: function(num, country) {
                return (
                    EXPORT.isSweden(country) &&
                    EXPORT.validatePhoneSweden(num)
                ) || (
                    EXPORT.isNorway(country) &&
                    EXPORT.validatePhoneNorway(num)
                ) || (
                    EXPORT.isFinland(country) &&
                    EXPORT.validatePhoneFinland(num)
                );
            },

            /**
             * Validates a Swedish phone number.
             *
             * @param {string} num
             * @returns {boolean}
             */
            validatePhoneSweden: function(num) {
                return /^(0|\+46|0046)[ |-]?(200|20|70|73|76|74|[1-9][0-9]{0,2})([ |-]?[0-9]){5,8}$/.test(num);
            },

            /**
             * Validates a Norwegian phone number.
             *
             * @param {string} num
             * @returns {boolean}
             */
            validatePhoneNorway: function(num) {
                return /^(\+47|0047|)?[ |-]?[2-9]([ |-]?[0-9]){7,7}$/.test(num);
            },

            /**
             * Validates a Finnish phone number.
             *
             * @param {string} num
             * @returns {boolean}
             */
            validatePhoneFinland: function(num) {
                return /^((\+358|00358|0)[-| ]?(1[1-9]|[2-9]|[1][0][1-9]|201|2021|[2][0][2][4-9]|[2][0][3-8]|29|[3][0][1-9]|71|73|[7][5][0][0][3-9]|[7][5][3][0][3-9]|[7][5][3][2][3-9]|[7][5][7][5][3-9]|[7][5][9][8][3-9]|[5][0][0-9]{0,2}|[4][0-9]{1,3})([-| ]?[0-9]){3,10})?$/.test(num);
            }
        };

        return Object.freeze(EXPORT);
    }
);
