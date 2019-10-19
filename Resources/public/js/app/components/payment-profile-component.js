/* global Accept */
define(function(require) {
    'use strict';

    var PaymentProfileComponent;
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var $ = require('jquery');
    var scriptjs = require('scriptjs');
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');
    require('jquery.validate');

    PaymentProfileComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            allowedCreditCards: [],
            selectors: {
                form: '[data-payment-profile-form]',
                expirationDate: '[data-expiration-date]',
                month: '[data-expiration-date-month]',
                year: '[data-expiration-date-year]',
                cvv: '[data-card-cvv]',
                cardNumber: '[data-card-number]',
                lastDigits: '[data-last-digits]',
                dataDescriptor: '[data-encoded-descriptor]',
                dataValue: '[data-encoded-value]',
                validation: '[data-validation]',
                updatePaymentData: '[data-update-payment-data]',

                lastDigitsSource: '[data-last-digits-source]',
                accountType: '[data-account-type]',
                accountNumber: '[data-account-number]',
                routingNumber: '[data-routing-number]',
                nameOnAccount: '[data-name-on-account]',
                bankName: '[data-bank-name]',
                sensitiveData: '[data-sensitive-data]'
            },
            messages: {
                communication_err: 'oro.authorize_net.errors.accept_js.communication_err'
            },
            clientKey: null,
            apiLoginID: null,
            testMode: null,
            acceptJsUrls: {
                test: 'https://jstest.authorize.net/v1/Accept.js',
                prod: 'https://js.authorize.net/v1/Accept.js'
            }
        },

        /**
         * @property {jQuery}
         */
        $el: null,

        /**
         * @property {jQuery}
         */
        $form: null,

        /**
         * @property {(Accept|null)}
         */
        acceptJs: null,

        /**
         * @property {bool}
         */
        submitted: false,

        /**
         * @inheritDoc
         */
        constructor: function PaymentProfileComponent() {
            PaymentProfileComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);

            $.validator.loadMethod('oropayment/js/validator/credit-card-number');
            $.validator.loadMethod('oropayment/js/validator/credit-card-type');
            $.validator.loadMethod('oropayment/js/validator/credit-card-expiration-date');
            $.validator.loadMethod('oropayment/js/validator/credit-card-expiration-date-not-blank');

            this.$el = this.options._sourceElement;
            this.$form = this.$el.find(this.options.selectors.form);
            this.loadAcceptJsLibrary();
            this.$form.submit(_.bind(this.onSubmit, this));
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$form.off();

            PaymentProfileComponent.__super__.dispose.call(this);
        },

        /**
         * @param {Object} eventData
         */
        onSubmit: function(eventData) {
            var processForm = this.$el.find(this.options.selectors.updatePaymentData).prop('checked');

            if (this.submitted || !processForm) { // prevent processing
                return;
            }

            var self = this;
            var form = this.$form;

            if (form.valid()) {
                mediator.execute('showLoading');

                var data = {
                    authData: {
                        clientKey: this.options.clientKey,
                        apiLoginID: this.options.apiLoginID
                    }
                };

                var $cardNumber = form.find(this.options.selectors.cardNumber);
                if ($cardNumber.length) {
                    var cardData = {
                        cardNumber: $cardNumber.val(),
                        month: form.find(this.options.selectors.month).val(),
                        year: form.find(this.options.selectors.year).val()
                    };
                    var $cvv = form.find(this.options.selectors.cvv);
                    if ($cvv.length) {
                        cardData.cardCode = $cvv.val();
                    }

                    data.cardData = cardData;
                }

                var $accountType = form.find(this.options.selectors.accountType);
                if ($accountType.length) {
                    var bankData = {
                        accountType: $accountType.val(),
                        accountNumber: form.find(this.options.selectors.accountNumber).val(),
                        routingNumber: form.find(this.options.selectors.routingNumber).val(),
                        nameOnAccount: form.find(this.options.selectors.nameOnAccount).val(),
                        echeckType: $accountType.val() === 'businessChecking' ? 'CCD' : 'WEB',
                        bankName: form.find(this.options.selectors.bankName).val()
                    };

                    data.bankData = bankData;
                }

                this.acceptJs.dispatchData(data, function(response) {
                    mediator.execute('hideLoading');
                    self.acceptJsResponse(response);
                });
            }

            return false;
        },

        loadAcceptJsLibrary: function() {
            var acceptJsUrl = this.options.testMode ? this.options.acceptJsUrls.test : this.options.acceptJsUrls.prod;

            scriptjs(acceptJsUrl, function() {
                this.acceptJs = Accept;
            }.bind(this));
        },

        /**
         * @param {Object} response
         */
        acceptJsResponse: function(response) {
            if (response.messages.resultCode !== 'Ok' || !response.opaqueData ||
                !response.opaqueData.dataDescriptor || !response.opaqueData.dataValue
            ) {
                this.logError(response);
                var reasons = response.messages.message.map(function(item) {
                    return item.text;
                });
                mediator.execute(
                    'showFlashMessage',
                    'error',
                    __(this.options.messages.communication_err, {reasons: reasons.join(', ')})
                );
            } else {
                var additionalData = {
                    dataDescriptor: response.opaqueData.dataDescriptor,
                    dataValue: response.opaqueData.dataValue
                };

                this.setAdditionalData(additionalData);
                this.eraseSensitiveData();

                this.submitted = true;
                this.$form.submit();
            }
        },

        /**
         * @param {Object} additionalData
         */
        setAdditionalData: function(additionalData) {
            var selectors = this.options.selectors;
            var form = this.$form;

            form.find(selectors.dataDescriptor).val(additionalData.dataDescriptor);
            form.find(selectors.dataValue).val(additionalData.dataValue);

            var lastDigits = form.find(selectors.lastDigitsSource).val().slice(-4);
            form.find(selectors.lastDigits).val(lastDigits);
        },

        eraseSensitiveData: function() {
            var selectors = this.options.selectors;
            var form = this.$form;

            // prevent sending sensitive data to server
            form.find(selectors.sensitiveData).prop('disabled', true);
        },

        /**
         * @param {(string|Object)} message
         */
        logError: function(message) {
            if (typeof window.console === 'undefined') {
                // can not log error because console doesn't exist
                return;
            }

            window.console.error(message);
        }
    });

    return PaymentProfileComponent;
});
