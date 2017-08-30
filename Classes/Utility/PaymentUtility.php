<?php

namespace Extcode\CartMpay24\Utility;

use Extcode\Cart\Domain\Repository\CartRepository;
use Mpay24\Mpay24;
use Mpay24\Mpay24Order;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class PaymentUtility
{

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $conf = [];

    /**
     * @var array
     */
    protected $cartConf = [];

    /**
     * @var array
     */
    protected $paymentQuery = [];

    /**
     * @var \Extcode\Cart\Domain\Model\Order\Item
     */
    protected $orderItem = null;

    /**
     * Intitialize
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(
            ObjectManager::class
        );
        $this->persistenceManager = $this->objectManager->get(
            PersistenceManager::class
        );
        $this->configurationManager = $this->objectManager->get(
            ConfigurationManager::class
        );

        $this->conf = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'CartMpay24'
        );

        $this->cartConf = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'Cart'
        );
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function handlePayment(array $params): array
    {
        $this->orderItem = $params['orderItem'];

        list($provider, $type, $brand) = array_map('trim', explode('-', $this->orderItem->getPayment()->getProvider()));

        if ($provider === 'MPAY24') {
            $params['providerUsed'] = true;

            $cart = $this->objectManager->get(
                \Extcode\Cart\Domain\Model\Cart::class
            );
            $cart->setOrderItem($this->orderItem);
            $cart->setCart($params['cart']);
            $cart->setPid($this->cartConf['settings']['order']['pid']);

            $cartRepository = $this->objectManager->get(
                CartRepository::class
            );
            $cartRepository->add($cart);
            $this->persistenceManager->persistAll();

            $mpay24 = new Mpay24(
                $this->conf['merchantId'],
                $this->conf['soapPassword'],
                $this->conf['test'],
                $this->conf['enableDebug'],
                null,
                null,
                null,
                null,
                null,
                $this->conf['enableDebugCurl'],
                null,
                null,
                null,
                'mpay24.log',
                \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log',
                'mpay24_curl.log'
            );

            $mdxi = new Mpay24Order();
            $mdxi->Order->Tid = $this->orderItem->getOrderNumber();

            if (!empty($type)) {
                $mdxi->Order->PaymentTypes->setEnable('true');
                $mdxi->Order->PaymentTypes->Payment(1)->setType($type);
                if (!empty($brand)) {
                    $mdxi->Order->PaymentTypes->Payment(1)->setBrand($brand);
                }
            }
            $mdxi->Order->Price = $this->orderItem->getTotalGross();

            $mdxi->Order->URL->Success = $this->getUrl('success', $cart->getSHash());
            $mdxi->Order->URL->Error = $this->getUrl('cancel', $cart->getFHash());
            $mdxi->Order->URL->Confirmation = $this->getUrl('confirm', $cart->getSHash());

            $paymentPageURL = $mpay24->paymentPage($mdxi)->getLocation();

            header('Location: ' . $paymentPageURL);
        }

        return [$params];
    }

    /**
     * Builds a return URL to Cart order controller action
     *
     * @param string $action
     * @param string $hash
     *
     * @return string
     */
    protected function getUrl($action, $hash): string
    {
        $pid = $this->cartConf['settings']['cart']['pid'];

        $arguments = [
            'tx_cartmpay24_cart' => [
                'controller' => 'Order\Payment',
                'order' => $this->orderItem->getUid(),
                'action' => $action,
                'hash' => $hash
            ]
        ];

        $uriBuilder = $this->getUriBuilder();

        return $uriBuilder->reset()
            ->setTargetPageUid($pid)
            ->setTargetPageType($this->conf['redirectTypeNum'])
            ->setCreateAbsoluteUri(true)
            ->setUseCacheHash(false)
            ->setArguments($arguments)
            ->build();
    }

    /**
     * @return UriBuilder
     */
    protected function getUriBuilder(): UriBuilder
    {
        $request = $this->objectManager->get(Request::class);
        $request->setRequestURI(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
        $request->setBaseURI(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($request);

        return $uriBuilder;
    }
}
