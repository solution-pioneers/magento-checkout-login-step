<?php
/**
 * Solution Pioneers
 *
 * @category    SolutionPioneers
 * @package     SolutionPioneers_CheckoutLoginStep
 * @copyright   Copyright (c) Solution Pioneers (https://www.solution-pioneers.com/)
 */

namespace SolutionPioneers\CheckoutLoginStep\Controller\Customer\Ajax;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Register extends Action
{
    /**
     * @var Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $cookieMetadataManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $registration;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface;
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\CustomerExtractor $customerExtractor
     * 
     */
    public function __construct (
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerFactory $customerFactory,
        CustomerExtractor $customerExtractor,
        CustomerUrl $customerUrl,
        AccountManagementInterface $accountManagement,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        Registration $registration,
        UrlInterface $urlModel,
        Escaper $escaper,
        JsonHelper $helper,
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerFactory = $customerFactory;
        $this->customerExtractor = $customerExtractor;
        $this->customerUrl = $customerUrl;
        $this->escaper = $escaper;
        $this->helper = $helper;
        $this->session = $customerSession;
        $this->storeManager = $storeManager;
        $this->registration = $registration;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->urlModel = $urlModel;

        parent::__construct($context);
    }

    /**
     * Create customer account action
     *
     *  @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        if ($this->session->isLoggedIn()) {
            $response = [
                'errors' => false,
                'message' => __('You are already logged in.')
            ];
        }
        
        if (!$this->registration->isAllowed()) {
            $response = [
                'errors' => true,
                'message' => __('Customer registration is already disabled.')
            ];
        }

        $this->session->regenerateId();
        try {
            $postValues = $this->helper->jsonDecode($this->getRequest()->getContent());

            $this->_request->setParams($postValues);
            
            $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);

            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');

            $this->checkPasswordConfirmation($password, $confirmation);

            $extensionAttributes = $customer->getExtensionAttributes();
            $extensionAttributes->setIsSubscribed($this->getRequest()->getParam('is_subscribed', false));
            $customer->setExtensionAttributes($extensionAttributes);
        
            $customer = $this->accountManagement->createAccount($customer, $password);

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );


            $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                $response = [
                    'errors' => false,
                    'message' => __(
                        'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                        $email
                    )
                ];
            } else {
                $this->session->setCustomerDataAsLoggedIn($customer);
                $response = [
                    'errors' => false,
                    'message' => $this->getSuccessMessage()
                ];
            }
            if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }

        } catch (StateException $e) {
            $url = $this->urlModel->getUrl('customer/account/forgotpassword');
            $response = [
                'errors' => true,
                'message' => __(
                    'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                    $url
                )
            ];
        } catch (InputException $e) {
            $response = [
                'errors' => true,
                'message' => $this->escaper->escapeHtml(__($e->getMessage()))
            ];
        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $this->escaper->escapeHtml(__($e->getMessage()))
            ];
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $this->escaper->escapeHtml(__($e->getMessage()))
            ];
        }
         
         return $resultJson->setData($response);

    }

    /**
     * @param string $password
     * @param string $confirmation
     */
    protected function checkPasswordConfirmation(string $password, string $confirmation)
    {
        if ($password != $confirmation) {
            throw new InputException(__('Please make sure your passwords match.'));
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Retrieve success message
     *
     * @return string
     */
    protected function getSuccessMessage()
    {
        return __('Thank you for registering with %1.', $this->storeManager->getStore()->getFrontendName());
    }
}