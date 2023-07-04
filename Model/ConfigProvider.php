<?php
namespace SolutionPioneers\CheckoutLoginStep\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const XML_PATH_CHECKBOX_TEXT = 'solutionpioneers_checkout_login_step/agreement/checkbox_text';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array<string, string>
     */
    public function getConfig()
    {
        $config['checkbox_text'] = $this->scopeConfig
            ->getValue(self::XML_PATH_CHECKBOX_TEXT, ScopeInterface::SCOPE_STORE);
                     
        return $config;
    }
}