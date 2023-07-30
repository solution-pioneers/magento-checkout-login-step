# Magento Checkout Login Step extension

A simple module which enhances the checkout process by introducing a new step: Login. Customers can log in to their accounts. By allowing customers to log in during checkout, it provides a seamless and personalized experience, enabling faster order processing and access to their saved information.

## 1. How to install Magento Checkout Login Step

Add the following lines into your composer.json
 
```
"require":{
    ...
    "solution-pioneers/magento-checkout-login-step":"{version}"
 }
```
or install via composer

```
composer require solution-pioneers/magento-checkout-login-step
```

Then execute the following commands:

```
$ composer update
$ bin/magento setup:upgrade
$ bin/magento setup:static-content:deploy
```

# Snapshot

## Customer login action

![ScreenShot](https://raw.githubusercontent.com/solution-pioneers/magento-checkout-login-step/master/Snapshot/login-action.png)

## Customer registration action

![ScreenShot](https://raw.githubusercontent.com/solution-pioneers/magento-checkout-login-step/master/Snapshot/registration-action.png)