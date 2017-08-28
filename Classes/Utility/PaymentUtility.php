<?php

namespace Extcode\CartMpay24\Utility;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

@include 'phar://' . ExtensionManagementUtility::extPath('cart_mpay24') . 'Libraries/mpay24-mpay24-php.phar/vendor/autoload.php';

use Mpay24\Mpay24;
use Mpay24\Mpay24Order;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Payment Utility
 *
 * @author Daniel Lorenz <ext.cart@extco.de>
 */
class PaymentUtility
{
    /**
     * Object Manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * Configuration Manager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * Cart Repository
     *
     * @var \Extcode\Cart\Domain\Repository\CartRepository
     */
    protected $cartRepository;

    /**
     * Cart Settings
     *
     * @var array
     */
    protected $cartConf = [];

    /**
     * Cart mPay24 Settings
     *
     * @var array
     */
    protected $cartMpay24Conf = [];

    /**
     * Payment Query Url
     *
     * @var string
     */
    protected $paymentQueryUrl = '';

    /**
     * Payment Query
     *
     * @var array
     */
    protected $paymentQuery = [];

    /**
     * Order Item
     *
     * @var \Extcode\Cart\Domain\Model\Order\Item
     */
    protected $orderItem = null;

    /**
     * Cart
     *
     * @var \Extcode\Cart\Domain\Model\Cart\Cart
     */
    protected $cart = null;

    /**
     * CartFHash
     *
     * @var string
     */
    protected $cartFHash = '';

    /**
     * Intitialize
     */
    public function __construct()
    {
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class
        );

        $this->configurationManager = $this->objectManager->get(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class
        );

        $this->cartConf =
            $this->configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'Cart'
            );

        $this->cartMpay24Conf =
            $this->configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'CartMpay24'
            );
    }

    /**
     * Handle Payment - Signal Slot Function
     *
     * @param array $params
     *
     * @return array
     */
    public function handlePayment($params)
    {
        $this->orderItem = $params['orderItem'];

        if ($this->orderItem->getPayment()->getProvider() == 'MPAY24') {
            $params['providerUsed'] = true;

            $this->cart = $params['cart'];

            $cart = $this->objectManager->get(
                \Extcode\Cart\Domain\Model\Cart::class
            );
            $cart->setOrderItem($this->orderItem);
            $cart->setCart($this->cart);
            $cart->setPid($this->cartConf['settings']['order']['pid']);

            $cartRepository = $this->objectManager->get(
                \Extcode\Cart\Domain\Repository\CartRepository::class
            );
            $cartRepository->add($cart);

            $this->persistenceManager->persistAll();

            $this->cartFHash = $cart->getFHash();

            $mpay24 = new Mpay24(
                $this->cartMpay24Conf['merchantId'],
                $this->cartMpay24Conf['soapPassword'],
                $this->cartMpay24Conf['test'],
                $this->cartMpay24Conf['enableDebug'],
                null,
                null,
                null,
                null,
                null,
                $this->cartMpay24Conf['enableDebugCurl'],
                null,
                null,
                null,
                'mpay24.log',
                PATH_site . '/typo3temp/logs/',
                'mpay24_curl.log'
            );
            $mpay24->setCurloptCainfoPath(ExtensionManagementUtility::extPath('cart_mpay24') . 'Libraries/');

            $mdxi = new Mpay24Order();
            $mdxi->Order->Tid = $this->orderItem->getOrderNumber();
            $mdxi->Order->Price = $this->orderItem->getTotalGross();
            $mdxi->Order->URL->Success      = $this->getUrl('paymentSuccess', $cart->getSHash());
            $mdxi->Order->URL->Error        = $this->getUrl('paymentCancel', $cart->getFHash());
            $mdxi->Order->URL->Confirmation = 'https://cart.extdev.de/warenkorb/confirmation';

            $paymentPageURL = $mpay24->paymentPage($mdxi)->getLocation(); // redirect location to the payment page

            header('Location: '.$paymentPageURL);
        }

        return [$params];
    }

    /**
     *
     */
    protected function getUrl($action, $hash)
    {
        $pid = $this->cartConf['settings']['cart']['pid'];

        $arguments = [
            ['tx_cart_cart' =>
                 [
                     'controller' => 'Order',
                     'order' => $this->orderItem->getUid(),
                     'action' => $action,
                     'hash' => $hash
                 ]
            ]
        ];

        $request = $this->objectManager->get(\TYPO3\CMS\Extbase\Mvc\Web\Request::class);
        $request->setRequestURI(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
        $request->setBaseURI(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));
        $uriBuilder = $this->objectManager->get(\TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class);
        $uriBuilder->setRequest($request);

        $uri = $uriBuilder->reset()
            ->setTargetPageUid($pid)
            ->setCreateAbsoluteUri(true)
            ->setArguments($arguments)
            ->build();

        return $uri;
    }
}
