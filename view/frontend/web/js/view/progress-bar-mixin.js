define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/view/billing-address'
], function ($, _, ko, Component, customer, stepNavigator, billingAddress) {
    'use strict';

        return function (Component) {
            return Component.extend({

                /**
                 * @override
                 */
                navigateTo: function (step) {
                    if (customer.isLoggedIn() && step.code === 'login') {
                        return;
                    }
    
                    if (step.code === 'shipping') {
                        billingAddress().needCancelBillingAddressChanges();
                    }
                    stepNavigator.navigateTo(step.code);
                },
            });
        }

   }
);