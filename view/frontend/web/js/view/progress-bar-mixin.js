define([
    'Magento_Customer/js/model/customer',
], function (customer) {
    'use strict';
        var mixin = {
            /**
             * @override
             */
            initialize: function() {
                this._super();
                
                window.scrollTo({top: 0});
            },

            /**
             * @override
             * 
             * @param {Object} step 
             */
            navigateTo: function (step) {
                if (customer.isLoggedIn() && step.code === 'login') {
                    return;
                }

                this._super();
            }
       };

       return function (target) {
           return target.extend(mixin);
       };
   }
);