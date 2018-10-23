/* global Accept */
define(function(require) {
    'use strict';

    var PaymentMethodComponent;
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');
    require('jquery.validate');

    PaymentMethodComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            paymentMethod: null,
            allowedCreditCards: [],
            selectors: {
                form: '[data-payment-method-form]',
                paymentDataForm: '[data-payment-data-form]',
                profileForm: '[data-profile-form]',
                expirationDate: '[data-expiration-date]',
                month: '[data-expiration-date-month]',
                year: '[data-expiration-date-year]',
                cvv: '[data-card-cvv]',
                cardNumber: '[data-card-number]',
                validation: '[data-validation]',
                profileSelector: '[data-profile-selector]',
                profileCvv: '[data-profile-cvv]',
                profileCvvField: '[data-profile-cvv-field]',
                saveProfile: '[data-save-profile]',
                accountType: '[data-account-type]',
                accountNumber: '[data-account-number]',
                routingNumber: '[data-routing-number]',
                nameOnAccount: '[data-name-on-account]',
                bankName: '[data-bank-name]'
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
         * @inheritDoc
         */
        constructor: function PaymentMethodComponent() {
            PaymentMethodComponent.__super__.constructor.apply(this, arguments);
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

            this.$el
                .on(
                    'change',
                    this.options.selectors.expirationDate,
                    $.proxy(this.validate, this, this.options.selectors.expirationDate)
                )
                .on(
                    'focusout',
                    this.options.selectors.cardNumber,
                    $.proxy(this.validate, this, this.options.selectors.cardNumber)
                )
                .on(
                    'keyup focusout',
                    this.options.selectors.cvv,
                    $.proxy(this.validate, this, this.options.selectors.cvv)
                ).on(
                    'keyup focusout',
                    this.options.selectors.profileCvv,
                    $.proxy(this.validate, this, this.options.selectors.profileCvv)
                );

            mediator.on('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
            mediator.on('checkout:payment:before-transit', this.beforeTransit, this);
            mediator.on('checkout-content:initialized', this.refreshPaymentMethod, this);
            mediator.on('checkout:place-order:response', this.placeOrderResponse, this);

            this.$paymentDataForm = this.$form.find(this.options.selectors.paymentDataForm);
            this.$profileSelector = this.$form.find(this.options.selectors.profileSelector);
            this.$profileCvv = this.$form.find(this.options.selectors.profileCvv);
            this.$profileSelector.on('change', _.bind(this.onProfileChanged, this));
            this.onProfileChanged();
        },

        onProfileChanged: function() {
            if (this._isPaymentDataProcessing()) {
                this._showForm();
            } else {
                this._hideForm();
            }
        },

        _isPaymentDataProcessing: function() {
            var result = true;
            if (this.$profileSelector.length) {
                result = this.$profileSelector.val() === '';
            }
            return result;
        },

        _showForm: function() {
            this.$paymentDataForm.removeClass('hidden');
            this.$profileCvv.addClass('hidden');
        },

        _hideForm: function() {
            this.$paymentDataForm.addClass('hidden');
            this.$profileCvv.removeClass('hidden');
        },

        refreshPaymentMethod: function() {
            mediator.trigger('checkout:payment:method:refresh');
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off();
            this.$profileSelector.off();

            mediator.off('checkout-content:initialized', this.refreshPaymentMethod, this);
            mediator.off('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
            mediator.off('checkout:payment:before-transit', this.beforeTransit, this);
            mediator.off('checkout:place-order:response', this.placeOrderResponse, this);

            PaymentMethodComponent.__super__.dispose.call(this);
        },

        /**
         * @param {String} elementSelector
         */
        validate: function(elementSelector) {
            var appendElement;
            var self = this;

            if (elementSelector) {
                var element = this.$form.find(elementSelector);
                var parentForm = element.closest('form');

                if (parentForm.length) {
                    return this._validateFormField(this.$form, element);
                }
                appendElement = element.clone();
            } else {
                appendElement = this.$form.clone();
            }

            var virtualForm = $('<form>');
            virtualForm.append(appendElement);

            if (this.$paymentDataForm.is(':visible')) { // remove invisible fields
                virtualForm.find(this.options.selectors.profileForm).remove();
            } else {
                virtualForm.find(this.options.selectors.paymentDataForm).remove();
            }

            virtualForm.find('select').each(function(index, item) {
                // set new select to value of old select
                // http://stackoverflow.com/questions/742810/clone-isnt-cloning-select-values
                $(item).val(self.$form.find('#' + item.id).val());
            });

            var validator = virtualForm.validate({
                ignore: '', // required to validate all fields in virtual form
                errorPlacement: function(error, element) {
                    var $el = self.$form.find('#' + $(element).attr('id'));
                    var parentWithValidation = $el.parents(self.options.selectors.validation);

                    $el.addClass('error');

                    if (parentWithValidation.length) {
                        error.appendTo(parentWithValidation.first());
                    } else {
                        error.appendTo($el.parent());
                    }
                }
            });

            // Add validator to form
            $.data(virtualForm, 'validator', validator);

            var errors;

            if (elementSelector) {
                errors = this.$form.find(elementSelector).parent();
            } else {
                errors = this.$form;
            }

            errors.find(validator.settings.errorElement + '.' + validator.settings.errorClass).remove();
            errors.parent().find('.error').removeClass('error');

            return validator.form();
        },

        /**
         * @param {jQuery} form
         * @param {jQuery} element
         */
        _validateFormField: function(form, element) {
            return element.validate().form();
        },

        /**
         * @param {Object} eventData
         */
        beforeTransit: function(eventData) {
            if (eventData.data.paymentMethod !== this.options.paymentMethod || eventData.stopped) {
                return;
            }

            eventData.stopped = true;

            if (!this.validate()) {
                return;
            }

            if (this._isPaymentDataProcessing()) {
                this._processWithPaymentData(eventData);
            } else {
                this._processWithProfile(eventData);
            }
        },

        /**
         * @param {Object} eventData
         */
        _processWithPaymentData: function(eventData) {
            mediator.execute('showLoading');

            var self = this;
            var form = this.$form;

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
                self.acceptJsResponse(response, eventData);
            });
        },

        /**
         * @param {Object} eventData
         */
        _processWithProfile: function(eventData) {
            var additionalData = {
                profileId: this.$profileSelector.val()
            };

            var $profileCvvField = this.$form.find(this.options.selectors.profileCvvField);
            if ($profileCvvField.length) {
                additionalData.cvv = $profileCvvField.val();
            }

            mediator.trigger('checkout:payment:additional-data:set', JSON.stringify(additionalData));
            mediator.trigger('checkout:payment:validate:change', true);
            eventData.resume();
        },

        /**
         * @param {Object} eventData
         */
        onPaymentMethodChanged: function(eventData) {
            if (eventData.paymentMethod === this.options.paymentMethod) {
                this.loadAcceptJsLibrary();
            }
        },

        loadAcceptJsLibrary: function() {
            var acceptJsUrl = this.options.testMode ? this.options.acceptJsUrls.test : this.options.acceptJsUrls.prod;
            var self = this;
            require([acceptJsUrl], function() {
                self.acceptJs = Accept;
            });
        },

        /**
         * @param {Object} response
         * @param {Object} eventData
         */
        acceptJsResponse: function(response, eventData) {
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

                var $saveProfile = this.$form.find(this.options.selectors.saveProfile);
                if ($saveProfile.length) {
                    additionalData.saveProfile = $saveProfile.prop('checked');
                }

                mediator.trigger('checkout:payment:additional-data:set', JSON.stringify(additionalData));
                mediator.trigger('checkout:payment:validate:change', true);
                eventData.resume();
            }
        },

        /**
         * @param {Object} eventData
         */
        placeOrderResponse: function(eventData) {
            if (eventData.responseData.paymentMethod === this.options.paymentMethod) {
                if (true === eventData.responseData.successful) {
                    eventData.stopped = true;
                    mediator.execute('redirectTo', {url: eventData.responseData.successUrl}, {redirect: true});
                }
            }
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

    return PaymentMethodComponent;
});
