/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
define(
    ['libphonenumber'],
    function (libphonenumber) {
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
            isCountryAllowed: function (country) {
                return EXPORT.isSweden(country) ||
                    EXPORT.isNorway(country) ||
                    EXPORT.isFinland(country) ||
                    EXPORT.isDenmark(country);
            },

            /**
             * Check for the Swedish country id.
             *
             * @param {string} country
             * @returns {boolean}
             */
            isSweden: function (country) {
                return country === 'SE';
            },

            /**
             * Check for the Norwegian country id.
             *
             * @param {string} country
             * @returns {boolean}
             */
            isNorway: function (country) {
                return country === 'NO';
            },

            /**
             * Check for the Finnish country id.
             *
             * @param {string} country
             * @returns {boolean}
             */
            isFinland: function (country) {
                return country === 'FI';
            },

            /**
             * Check for the Danish country id.
             *
             * @param {string} country
             * @returns {boolean}
             */
            isDenmark: function (country) {
                return country === 'DK';
            },

            /**
             * Validates an ssn or organization number.
             *
             * NOTE: Some countries do not require an SSN, so the result will
             * be "true" when the "idNum" parameter is an empty string.
             *
             * @param {string} idNum
             * @param {string} country
             * @param {boolean} isCompany
             * @returns {boolean}
             */
            validate: function (idNum, country, isCompany) {
                var result = false;

                if (idNum !== '') {
                    if (EXPORT.isCountryAllowed(country)) {
                        if (isCompany) {
                            result = EXPORT.validateOrg(idNum, country);
                        } else {
                            result = EXPORT.validateSsn(idNum, country);
                        }
                    }
                } else if (!this.isSweden(country)) {
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
            validateSsn: function (ssn, country) {
                var result = false;
                var norway = /^([0][1-9]|[1-2][0-9]|3[0-1])(0[1-9]|1[0-2])(\d{2})(\-)?([\d]{5})$/;
                var finland = /^([\d]{6})[\+-A]([\d]{3})([0123456789ABCDEFHJKLMNPRSTUVWXY])$/;
                var sweden = /^(18\d{2}|19\d{2}|20\d{2}|\d{2})(0[1-9]|1[0-2])([0][1-9]|[1-2][0-9]|3[0-1])(\-|\+)?([\d]{4})$/;
                var denmark = /^((3[0-1])|([1-2][0-9])|(0[1-9]))((1[0-2])|(0[1-9]))(\d{2})(\-)?([\d]{4})$/;

                if (EXPORT.isSweden(country)) {
                    result = sweden.test(ssn);
                } else if (EXPORT.isNorway(country)) {
                    result = norway.test(ssn);
                } else if (EXPORT.isFinland(country)) {
                    result = finland.test(ssn);
                } else if (EXPORT.isDenmark(country)) {
                    result = denmark.test(ssn);
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
            validateOrg: function (org, country) {
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
             * Validates a phone number based on a country.
             *
             * @param {string} num
             * @param {string} country
             * @returns {boolean}
             */
            validatePhone: function (num, country) {
                let phoneutil = libphonenumber.PhoneNumberUtil.getInstance()
                try {
                    return (
                        EXPORT.isSweden(country) &&
                        phoneutil.isValidNumberForRegion(phoneutil.parse(num, 'SE'), 'SE')
                    ) || (
                        EXPORT.isNorway(country) &&
                        phoneutil.isValidNumberForRegion(phoneutil.parse(num, 'NO'), 'NO')
                    ) || (
                        EXPORT.isFinland(country) &&
                        phoneutil.isValidNumberForRegion(phoneutil.parse(num, 'FI'), 'FI')
                    ) || (
                        EXPORT.isDenmark(country) &&
                        phoneutil.isValidNumberForRegion(phoneutil.parse(num, 'DK'), 'DK')
                    );
                } catch (e) {

                }
                return false
            }
        };

        return Object.freeze(EXPORT);
    }
);
