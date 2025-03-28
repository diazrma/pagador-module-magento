<?php

/**
 * Capture Request
 *
 * @author      Webjump Core Team <dev@webjump.com>
 * @copyright   2016 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */

namespace Braspag\BraspagPagador\Gateway\Transaction\Boleto\Resource\PaymentSplit;

use Magento\Framework\Session\SessionManagerInterface;
use Braspag\BraspagPagador\Transaction\Api\PaymentSplit\RequestInterface as BraspaglibRequestInterface;
use Braspag\BraspagPagador\Api\SplitDataProviderInterface;
use Braspag\BraspagPagador\Model\OAuth2TokenManager;
use Braspag\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface as PaymentSplitConfigInterface;

/**
 * Class Request
 * @package Braspag\BraspagPagador\Gateway\Transaction\Boleto\Resource\PaymentSplit
 */
class Request implements BraspaglibRequestInterface
{
    protected $quote;

    protected $order;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    protected $storeId;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SplitDataProviderInterface
     */
    protected $dataProvider;

    /**
     * @var OAuth2TokenManager
     */
    protected $oAuth2TokenManager;

    protected $splits;

    protected $splitTransaction;

    /**
     * @var PaymentSplitConfigInterface
     */
    protected $paymentSplitConfig;

    /**
     * Request constructor.
     * @param SessionManagerInterface $session
     * @param SplitDataProviderInterface $dataProvider
     * @param OAuth2TokenManager $oAuth2TokenManager
     * @param PaymentSplitConfigInterface $paymentSplitConfig
     */
    public function __construct(
        SessionManagerInterface $session,
        SplitDataProviderInterface $dataProvider,
        OAuth2TokenManager $oAuth2TokenManager,
        PaymentSplitConfigInterface $paymentSplitConfig
    ) {
        $this->setSession($session);
        $this->setDataProvider($dataProvider);
        $this->setOAuth2TokenManager($oAuth2TokenManager);
        $this->setPaymentSplitConfig($paymentSplitConfig);
    }

    /**
     * @return mixed
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * @param mixed $dataProvider
     */
    public function setDataProvider($dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @return mixed
     */
    public function getOAuth2TokenManager()
    {
        return $this->oAuth2TokenManager;
    }

    /**
     * @param mixed $oAuth2TokenManager
     */
    public function setOAuth2TokenManager($oAuth2TokenManager)
    {
        $this->oAuth2TokenManager = $oAuth2TokenManager;
    }

    /**
     * @return mixed
     */
    public function getSplits()
    {
        return $this->splits;
    }

    /**
     * @param $splits
     */
    public function setSplits($splits)
    {
        $this->splits = $splits;
    }

    /**
     * @return mixed
     */
    public function getSplitTransaction()
    {
        return $this->splitTransaction;
    }

    /**
     * @param mixed $splitTransaction
     */
    public function setSplitTransaction($splitTransaction): void
    {
        $this->splitTransaction = $splitTransaction;
    }

    /**
     * @return PaymentSplitConfigInterface
     */
    public function getPaymentSplitConfig(): PaymentSplitConfigInterface
    {
        return $this->paymentSplitConfig;
    }

    /**
     * @param PaymentSplitConfigInterface $paymentSplitConfig
     */
    public function setPaymentSplitConfig(PaymentSplitConfigInterface $paymentSplitConfig): void
    {
        $this->paymentSplitConfig = $paymentSplitConfig;
    }

    /**
     * @return $this|array
     */
    public function prepareSplits()
    {
        if (!$this->getConfig()->isPaymentSplitActive()) {
            return [];
        }

        $defaultMdr = $this->getConfig()->getPaymentSplitDefaultMrd();
        $defaultFee = $this->getConfig()->getPaymentSplitDefaultFee();
        $storeMerchantId = $this->getConfig()->getMerchantId();

        if (!empty($this->getQuote())) {
            $this->getDataProvider()->setQuote($this->getQuote());
        }

        if (!empty($this->getOrder())) {
            $this->getDataProvider()->setOrder($this->getOrder());
        }

        $this->setSplits($this->getDataProvider()->getData($storeMerchantId, $defaultMdr, $defaultFee));

        return $this;
    }

    /**
     * @return $this|array
     */
    public function prepareSplitTransactionData()
    {
        if (!$this->getConfig()->isPaymentSplitActive()) {
            return [];
        }

        $storePaymentSplitDiscountType = $this->getPaymentSplitConfig()
            ->getPaymentSplitMarketPlaceGeneralPaymentSplitDiscountType();

        $splitTransaction = [
            "MasterRateDiscountType" => $storePaymentSplitDiscountType
        ];

        $this->setSplitTransaction($splitTransaction);

        return $this;
    }

    /**
     * @return mixed
     */
    protected function getSession()
    {
        return $this->session;
    }

    /**
     * @param SessionManagerInterface $session
     */
    protected function setSession(SessionManagerInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (! $this->quote) {
            $this->quote =  $this->getSession()->getQuote();
        }

        return $this->quote;
    }

    /**
     * @param mixed $quote
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($storeId = null)
    {
        $this->storeId = $storeId;
    }

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->getOAuth2TokenManager()->getToken();
    }

    /**
     * @return mixed
     */
    public function isTestEnvironment()
    {
        return $this->getConfig()->getIsTestEnvironment();
    }

    /**
     * @return mixed
     */
    public function getOrderTransactionId()
    {
        if (empty($this->getOrder())) {
            return null;
        }

        return $this->getOrder()->getPayment()->getAuthorizationTransaction()->getTxnId();
    }
}
