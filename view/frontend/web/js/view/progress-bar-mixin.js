define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/view/billing-address'
], function ($, _, ko, Component, stepNavigator, billingAddress) {
    'use strict';

        return function (Component) {
            return Component.extend({

                /**
                 * @override
                 */
                navigateTo: function (step) {
                    if (step.code === 'login') {
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