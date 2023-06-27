define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, storage, globalMessageList, customerData, $t) {
    'use strict';

    var callbacks = [],

        /**
         * @param {Object} registerData
         * @param {*} isGlobal
         * @param {Object} messageContainer
         */
        action = function (registerData, isGlobal, messageContainer) {
            messageContainer = messageContainer || globalMessageList;
            let customerRegistrationUrl = 'sp_checkoutloginstep/customer_ajax/register';
            
            return storage.post(
                customerRegistrationUrl,
                JSON.stringify(registerData),
                isGlobal
            ).done(function (response) {

                /*if (response.errors) {
                    messageContainer.addErrorMessage(response);
                    callbacks.forEach(function (callback) {
                        callback(loginData);
                    });
                } else {
                    callbacks.forEach(function (callback) {
                        callback(loginData);
                    });
                    customerData.invalidate(['customer']);
                }*/
            }).fail(function () {
               /* messageContainer.addErrorMessage({
                    'message': $t('Could not authenticate. Please try again later')
                });
                callbacks.forEach(function (callback) {
                    callback(loginData);
                });*/
            });
        };

    /**
     * @param {Function} callback
     */
    action.registerRegistrationCallback = function (callback) {
        callbacks.push(callback);
    };

    return action;
});
