define(function(require) {
    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const routing = require('routing');
    const BaseComponent = require('oroui/js/app/components/base/component');

    const CheckCredentialsComponent = BaseComponent.extend({
        _container: null,

        _alertContainer: null,

        _apiLoginInput: null,
        _transactionKeyInput: null,

        _isTestModeInput: null,

        _options: {
            checkButtonSelector: 'button[data-check-authorizenet-credentials-button]',
            containerSelector: 'div[data-check-authorizenet-credentials-container]',
            checkConfigurationRouteName: 'oro_authorize_net_settings_check_credentials',
            alertContainerSelector: 'div[data-check-authorizenet-credentials-alert-container]',
            unexpectedErrorMessage: 'Something went wrong',
            apiLoginInputSelector: null,
            transactionKeyInputSelector: null,
            integrationId: null,
            isTestModeInputSelector: null
        },

        /**
         * @inheritDoc
         */
        constructor: function CheckCredentialsComponent(options) {
            CheckCredentialsComponent.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            CheckCredentialsComponent.__super__.initialize.call(this, options);

            this._options = _.extend(this._options, options);

            this._container = document.querySelector(this._options.containerSelector);

            this._alertContainer = document.querySelector(this._options.alertContainerSelector);

            this._apiLoginInput = document.querySelector(this._options.apiLoginInputSelector);
            this._transactionKeyInput = document.querySelector(this._options.transactionKeyInputSelector);

            this._isTestModeInput = document.querySelector(this._options.isTestModeInputSelector);

            this._initCheckButtonActionListeners();
            this._initCheckButtonVisibilityListeners();

            this._checkButtonVisibilitySwitcher();
        },

        _check: function() {
            this._hideAndClearAlert();

            const data = {
                apiLogin: this._apiLoginInput.value,
                transactionKey: this._transactionKeyInput.value,
                isTestMode: this._isTestModeInput.checked ? 1 : 0
            };

            if (!_.isEmpty(this._options.integrationId)) {
                data.integrationId = this._options.integrationId;
            }

            $.ajax({
                method: 'POST',
                type: 'json',
                url: routing.generate(this._options.checkConfigurationRouteName),
                data: data,
                success: (function(result) {
                    this._showAlert(result.message, result.status);
                }).bind(this),
                error: this._showAlert.bind(this, __(this._options.unexpectedErrorMessage), false)
            });
        },

        _initCheckButtonActionListeners: function() {
            const checkButton = document.querySelector(this._options.checkButtonSelector);
            if (checkButton === null) {
                return;
            }

            checkButton.addEventListener('click', this._check.bind(this));
        },

        _initCheckButtonVisibilityListeners: function() {
            if (this._apiLoginInput === null || this._transactionKeyInput === null) {
                return;
            }

            const handler = this._checkButtonVisibilitySwitcher.bind(this);
            this._apiLoginInput.addEventListener('change', handler);
            this._transactionKeyInput.addEventListener('change', handler);
        },

        _checkButtonVisibilitySwitcher: function() {
            const isHide = _.isEmpty(this._apiLoginInput.value) || _.isEmpty(this._transactionKeyInput.value);
            this._container.style.display = isHide ? 'none' : 'block';

            if (isHide) {
                this._hideAndClearAlert();
            }
        },

        _showAlert: function(message, isSuccess) {
            const type = isSuccess ? 'success' : 'error';

            this._alertContainer.style.display = 'block';

            this._alertContainer.classList.remove('alert-success');
            this._alertContainer.classList.remove('alert-error');

            this._alertContainer.classList.add('alert-' + type);
            this._alertContainer.innerText = message;
        },

        _hideAndClearAlert: function() {
            this._alertContainer.style.display = 'none';
            this._alertContainer.innerText = '';
        }
    });

    return CheckCredentialsComponent;
});
