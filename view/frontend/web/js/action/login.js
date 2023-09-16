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
         * @param {Object} loginData
         * @param {*} isGlobal
         * @param {Object} messageContainer
         */
        action = function (loginData, isGlobal, messageContainer) {
            messageContainer = messageContainer || globalMessageList;
            let customerLoginUrl = 'customer/ajax/login';

            if (loginData.customerLoginUrl) {
                customerLoginUrl = loginData.customerLoginUrl;
                delete loginData.customerLoginUrl;
            }

            return storage.post(
                customerLoginUrl,
                JSON.stringify(loginData),
                isGlobal
            ).done(function (response) {
                fullScreenLoader.stopLoader();
                if (response.errors) {
                    messageContainer.addErrorMessage(response);
                    callbacks.forEach(function (callback) {
                        callback(loginData);
                    });
                } else {
                    callbacks.forEach(function (callback) {
                        callback(loginData);
                    });
                    customerData.invalidate(['customer']);
                    location.reload();
                }
            }).fail(function () {
                fullScreenLoader.stopLoader();
                messageContainer.addErrorMessage({
                    'message': $t('Could not authenticate. Please try again later')
                });
                callbacks.forEach(function (callback) {
                    callback(loginData);
                });
            });
        };

    /**
     * @param {Function} callback
     */
    action.registerLoginCallback = function (callback) {
        callbacks.push(callback);
    };

    return action;
});
