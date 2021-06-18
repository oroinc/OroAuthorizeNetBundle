/* global Accept */
define(function(require) {
    'use strict';

    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const $ = require('jquery');
    const scriptjs = require('scriptjs');
    const mediator = require('oroui/js/mediator');
    const BaseComponent = require('oroui/js/app/components/base/component');
    require('jquery.validate');

    const PaymentMethodComponent = BaseComponent.extend({
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
         * @property {Boolean}
         */
        disposable: true,

        listen: {
            'checkout:payment:method:changed mediator': 'onPaymentMethodChanged',
            'checkout:payment:before-transit mediator': 'beforeTransit',
            'checkout:payment:before-hide-filled-form mediator': 'beforeHideFilledForm',
            'checkout:payment:before-restore-filled-form mediator': 'beforeRestoreFilledForm',
            'checkout:payment:remove-filled-form mediator': 'removeFilledForm',
            'checkout-content:initialized mediator': 'refreshPaymentMethod',
            'checkout:place-order:response mediator': 'placeOrderResponse'
        },

        /**
         * @inheritdoc
         */
        constructor: function PaymentMethodComponent(options) {
            PaymentMethodComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);

            $.validator.loadMethod('oropayment/js/validator/credit-card-number');
            $.validator.loadMethod('oropayment/js/validator/credit-card-type');
            $.validator.loadMethod('oropayment/js/validator/credit-card-expiration-date');
            $.validator.loadMethod('oropayment/js/validator/credit-card-expiration-date-not-blank');

            this.$el = this.options._sourceElement;

            this.$el
                .on(
                    'focusout.' + this.cid,
                    this.options.selectors.cardNumber,
                    this.validate.bind(this, this.options.selectors.cardNumber)
                )
                .on(
                    ['keyup.' + this.cid, 'focusout.' + this.cid].join(' '),
                    this.options.selectors.cvv,
                    this.validate.bind(this, this.options.selectors.cvv)
                ).on(
                    ['keyup.' + this.cid, 'focusout.' + this.cid].join(' '),
                    this.options.selectors.profileCvv,
                    this.validate.bind(this, this.options.selectors.profileCvv)
                );

            this.initForm(this.$el.find(this.options.selectors.form));

            this.onProfileChanged();
            this.onPaymentMethodAlreadySelected();
        },

        /**
         * Finds form and store it and its element to properties
         *
         * @param {jQuery} $form
         */
        initForm: function($form) {
            if (this.$profileSelector) {
                this.$profileSelector.off('change.' + this.cid);
            }

            this.$form = $form;
            this.$paymentDataForm = this.$form.find(this.options.selectors.paymentDataForm);
            this.$profileSelector = this.$form.find(this.options.selectors.profileSelector);
            this.$profileCvv = this.$form.find(this.options.selectors.profileCvv);
            this.$profileSelector.on('change.' + this.cid, this.onProfileChanged.bind(this));
        },

        onProfileChanged: function() {
            if (this._isPaymentDataProcessing()) {
                this._showForm();
            } else {
                this._hideForm();
            }
        },

        _isPaymentDataProcessing: function() {
            let result = true;
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
            if (this.disposed || !this.disposable) {
                return;
            }

            this.$el.off('.' + this.cid);
            this.$profileSelector.off('.' + this.cid);

            PaymentMethodComponent.__super__.dispose.call(this);
        },

        /**
         * @param {String} elementSelector
         */
        validate: function(elementSelector) {
            let appendElement;
            const self = this;

            if (elementSelector) {
                const element = this.$form.find(elementSelector);
                const parentForm = element.closest('form');

                if (elementSelector !== this.options.selectors.expirationDate && parentForm.length) {
                    return this._validateFormField(this.$form, element);
                }
                appendElement = element.clone();
            } else {
                appendElement = this.$form.clone();
            }

            const virtualForm = $('<form>');
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

            const validator = virtualForm.validate({
                ignore: '', // required to validate all fields in virtual form
                errorPlacement: function(error, element) {
                    const $el = self.$form.find('#' + $(element).attr('id'));
                    const parentWithValidation = $el.parents(self.options.selectors.validation);

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

            let errors;

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

            const self = this;
            const form = this.$form;

            const data = {
                authData: {
                    clientKey: this.options.clientKey,
                    apiLoginID: this.options.apiLoginID
                }
            };

            const $cardNumber = form.find(this.options.selectors.cardNumber);
            if ($cardNumber.length) {
                const cardData = {
                    cardNumber: $cardNumber.val(),
                    month: form.find(this.options.selectors.month).val(),
                    year: form.find(this.options.selectors.year).val()
                };
                const $cvv = form.find(this.options.selectors.cvv);
                if ($cvv.length) {
                    cardData.cardCode = $cvv.val();
                }

                data.cardData = cardData;
            }

            const $accountType = form.find(this.options.selectors.accountType);
            if ($accountType.length) {
                const bankData = {
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
            const additionalData = {
                profileId: this.$profileSelector.val()
            };

            const $profileCvvField = this.$form.find(this.options.selectors.profileCvvField);
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

        beforeHideFilledForm: function() {
            this.disposable = false;
        },

        beforeRestoreFilledForm: function() {
            if (this.disposable) {
                this.dispose();
            }
        },

        removeFilledForm: function() {
            // Remove hidden form js component
            if (!this.disposable) {
                this.disposable = true;
                this.dispose();
            }
        },

        onPaymentMethodAlreadySelected: function() {
            if (this.$paymentDataForm.is(':visible')) {
                this.loadAcceptJsLibrary();
            }
        },

        loadAcceptJsLibrary: function() {
            const acceptJsUrl = this.options.testMode ? this.options.acceptJsUrls.test : this.options.acceptJsUrls.prod;

            scriptjs(acceptJsUrl, function() {
                this.acceptJs = Accept;
            }.bind(this));
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
                const reasons = response.messages.message.map(function(item) {
                    return item.text;
                });
                mediator.execute(
                    'showFlashMessage',
                    'error',
                    __(this.options.messages.communication_err, {reasons: reasons.join(', ')})
                );
            } else {
                const additionalData = {
                    dataDescriptor: response.opaqueData.dataDescriptor,
                    dataValue: response.opaqueData.dataValue
                };

                const $saveProfile = this.$form.find(this.options.selectors.saveProfile);
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
