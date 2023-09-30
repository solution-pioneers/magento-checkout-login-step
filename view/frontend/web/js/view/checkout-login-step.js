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
            isRegisterActionVisible: ko.observable(false),
            isLoginActionVisible: ko.observable(true),
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
                    stepNavigator.next();
                }    

                return this;
            },

            /**
             * 
             * @returns string
             */
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
                        loginAction(loginData, undefined, messageContainer).always(function () {});
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
                        registerAction(form, registrationData, undefined, messageContainer).always(function () {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        });       
                }
            },

            /**
             * Display container 
             * 
             * @param string action 
             * 
             * @returns void
             */
            showAction: function(action) {
                if (action === 'login') {
                    this.isLoginActionVisible(true);
                    this.isRegisterActionVisible(false);
                }
                
                if (action === 'register') {
                    this.isRegisterActionVisible(true);
                    this.isLoginActionVisible(false);
                }

                window.scrollTo({top: 0});
            },

            /**
             * @returns void
             */
            navigate: function () {
                if (customer.isLoggedIn()) {
                    this.isVisible(false);
                    stepNavigator.next();
                } else {
                    self.isVisible(true);
                }
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
