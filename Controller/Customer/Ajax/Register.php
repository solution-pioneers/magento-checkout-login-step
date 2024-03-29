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
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

class Register extends Action
{
    const XML_PATH_CREATE_ACCOUNT_CONFIRM = 'solutionpioneers_checkout_login_step/create_account/confirm';

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
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

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
    * @var \Magento\Framework\Registry
    */
    private $registry;

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
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Framework\UrlInterface $urlModel
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Json\Helper\Data $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface config
     * @param \Magento\Framework\Registry $registry
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
        FormKeyValidator $formKeyValidator,
        JsonHelper $helper,
        ScopeConfigInterface $config,
        Registry $registry,
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerFactory = $customerFactory;
        $this->customerExtractor = $customerExtractor;
        $this->customerUrl = $customerUrl;
        $this->escaper = $escaper;
        $this->formKeyValidator = $formKeyValidator;
        $this->helper = $helper;
        $this->session = $customerSession;
        $this->storeManager = $storeManager;
        $this->registration = $registration;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->urlModel = $urlModel;
        $this->config = $config;
        $this->registry = $registry;

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

        $formKeyValidation = $this->formKeyValidator->validate($this->getRequest());

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

            return $resultJson->setData($response);
        }

        $this->session->regenerateId();
        try {
            $postValues = $this->helper->jsonDecode($this->getRequest()->getContent());

            $this->_request->setParams($postValues);
            
            $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);

            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');

            if ($password != $confirmation) {
                $response = [
                    'errors' => true,
                    'message' => __('Please make sure your passwords match.')
                ];

                return $resultJson->setData($response);
            }

            $extensionAttributes = $customer->getExtensionAttributes();
            $extensionAttributes->setIsSubscribed($this->getRequest()->getParam('is_subscribed', false));
            $customer->setExtensionAttributes($extensionAttributes);
        
            if (!$this->isCheckoutAccountRegistrationConfirmationRequired()) {
                $this->registry->register('skip_confirmation_if_email', $this->getRequest()->getParam('email'));
            }

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
                        'You must confirm your account. Please check your email for the confirmation link.',
                        $email
                    )
                ];

            } else {
                $this->session->setCustomerDataAsLoggedIn($customer);
                $response = [
                    'errors' => false,
                    'message' => $this->getSuccessMessage()
                ];

                $this->messageManager->addSuccess(__($this->getSuccessMessage()));
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

    /**
     * @return bool
     */
    protected function isCheckoutAccountRegistrationConfirmationRequired(): bool
    {
        return $this->config->getValue(self::XML_PATH_CREATE_ACCOUNT_CONFIRM, ScopeInterface::SCOPE_STORE);
    }
}