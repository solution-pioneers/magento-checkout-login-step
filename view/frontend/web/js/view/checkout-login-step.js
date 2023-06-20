define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'SolutionPioneers_CheckoutLoginStep/js/action/login',
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
            customerEmail: '',
            isLoggedIn: false,
            stepCode: 'login',
            stepTitle: 'Login',

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
                    this.isLoggedIn = true;
                    this.customerEmail = customer.customerData.email;
                    this.stepTitle = $t('Logged in');
                }    

                return this;
            },

             /**
             * Provide login action.
             *
             * @param {HTMLElement} loginForm
             */
            login: function (loginForm) {
                var loginData = {},
                    formDataArray = $(loginForm).serializeArray();

                    formDataArray.forEach(function (entry) {
                        loginData[entry.name] = entry.value;
                    });

                    if ($(loginForm).validation() &&
                        $(loginForm).validation('isValid')
                    ) {
                        fullScreenLoader.startLoader();
                        loginAction(loginData, undefined, messageContainer).always(function () {
                            location.reload(); 
                        });

                        
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
