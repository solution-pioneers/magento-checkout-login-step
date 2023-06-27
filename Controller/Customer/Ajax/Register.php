<?php
/**
 * Solution Pioneers
 *
 * @category    SolutionPioneers
 * @package     SolutionPioneers_CheckoutLoginStep
 * @copyright   Copyright (c) Solution Pioneers (https://www.solution-pioneers.com/)
 */

namespace SolutionPioneers\CheckoutLoginStep\Controller\Customer\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;

class Register extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    protected $helper;

    protected $session;

    protected $registration;

    /**
     * 
     */
    public function __construct (
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerFactory $customerFactory,
        AccountManagementInterface $accountManagement,
        Session $customerSession,
        Registration $registration,
        JsonHelper $helper,
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerFactory = $customerFactory;
        $this->accountManagement = $accountManagement;
        $this->helper = $helper;
        $this->session = $customerSession;
        $this->registration = $registration;

        parent::__construct($context);
    }

    /**
     * 
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


        try {
            $postValues = $this->helper->jsonDecode($this->getRequest()->getContent());
            
            /*$customer = $this->customerFactory->create();
            $customer->setData($postData);
            $customer->save();

            // Send a confirmation email to the customer
            $this->accountManagement->sendEmailConfirmation($customer);*/

            return $resultJson->setData(['success' => true]);
        } catch (\Exception $e) {
            return $resultJson->setData(['error' => $e->getMessage()]);
        }

         
         return $resultJson->setData($response);
    }
}