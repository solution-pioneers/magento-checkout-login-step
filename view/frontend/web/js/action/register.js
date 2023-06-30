define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/translate'
], function (
    $, 
    storage, 
    globalMessageList, 
    customerData, 
    fullScreenLoader,
    $t
    ) {
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
                fullScreenLoader.stopLoader();
                if (response.errors) {
                    messageContainer.addErrorMessage(response);
                    callbacks.forEach(function (callback) {
                        callback(registerData);
                    });
                } else {
                    messageContainer.addSuccessMessage(response);
                    callbacks.forEach(function (callback) {
                        callback(registerData);
                    });
                    //registerData.invalidate(['customer']);
                }
            }).fail(function () {
                fullScreenLoader.stopLoader();
                messageContainer.addErrorMessage({
                    'message': $t('Could not create customer. Please try again later')
                });
                callbacks.forEach(function (callback) {
                    callback(registerData);
                });
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
