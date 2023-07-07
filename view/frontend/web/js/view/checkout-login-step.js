define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'SolutionPioneers_CheckoutLoginStep/js/action/login',
        'SolutionPioneers_CheckoutLoginStep/js/action/register',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/authentication-messages',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/translate'
    ],
    function (
        $, 
        ko, 
        Component, 
        _, 
        stepNavigator, 
        loginAction, 
        registerAction,
        customer, 
        messageContainer, 
        fullScreenLoader,
        $t
        ) 
    {
        'use strict';

        /**
        * check-login - is the name of the component's .html template
        */
        return Component.extend({
            defaults: {
                template: 'SolutionPioneers_CheckoutLoginStep/checkout-login-step'
            },

            forgotPasswordUrl: checkoutConfig.forgotPasswordUrl,
            isVisible: ko.observable(true),
            isAuthenticationTabVisible: ko.observable(true),
            isNewTabVisible: ko.observable(false),
            isLoginTabVisible: ko.observable(true),
            customerEmail: '',
            isLoggedIn: false,
            stepCode: 'login',
            stepTitle: 'Login',
            isAgreementEnabled: checkoutConfig.agreement_enabled,
            agreementCheckboxText: checkoutConfig.agreement_checkbox_text,

            /**
            *
            * @returns {*}
            */
            initialize: function () {
                this._super();
                stepNavigator.registerStep(
                    this.stepCode,
                    null,
                    this.stepTitle,
                    this.isVisible,
                    _.bind(this.navigate, this),
                    5
                );

                if (customer.isLoggedIn()) {
                    this.isLoggedIn(true);
                    this.customerEmail = customer.customerData.email;
                    this.stepTitle = $t('Logged in');
                }    

                return this;
            },

            getFormKey: function () {
                return window.checkoutConfig.formKey;
            },

             /**
             * Provide login action.
             *
             * @param {HTMLElement} form
             */
            login: function (form) {
                var loginData = {},
                    formDataArray = $(form).serializeArray();

                    formDataArray.forEach(function (entry) {
                        loginData[entry.name] = entry.value;
                    });

                    if ($(form).validation() &&
                        $(form).validation('isValid')
                    ) {
                        fullScreenLoader.startLoader();
                            loginAction(loginData, undefined, messageContainer).always(function () {
                        });
                    }
            },
            /**
             * Provide registration action.
             *
             * @param {HTMLElement} form
             */
            registration: function (form) {
                var registrationData = {},
                    formDataArray = $(form).serializeArray();

                formDataArray.forEach(function (entry) {
                    registrationData[entry.name] = entry.value;
                });
                
                if ($(form).validation() &&
                        $(form).validation('isValid')
                    ) {
                        fullScreenLoader.startLoader();
                        registerAction(registrationData, undefined, messageContainer).always(function () {});       
                }
            },

            showTab: function(tab) {
                if (tab === 'login') {
                    this.isLoginTabVisible(true);
                    this.isNewTabVisible(false);
                }
                
                if (tab === 'new') {
                    this.isNewTabVisible(true);
                    this.isLoginTabVisible(false);
                }

            },

            /**
             * @returns void
             */
            navigate: function () {
            },

            /**
             * @returns void
             */
            navigateToNextStep: function () {
                stepNavigator.next();
            }
        });
    }
);
