<?php

namespace Extcode\CartMpay24\Controller\Order;

use Extcode\Cart\Domain\Repository\CartRepository;
use Extcode\Cart\Domain\Repository\Order\PaymentRepository;
use Extcode\Cart\Service\SessionHandler;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class PaymentController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var SessionHandler
     */
    protected $sessionHandler;

    /**
     * @var CartRepository
     */
    protected $cartRepository;

    /**
     * @var PaymentRepository
     */
    protected $paymentRepository;

    /**
     * @var \Extcode\Cart\Domain\Model\Cart
     */
    protected $cart = null;

    /**
     * @var array
     */
    protected $pluginSettings = [];

    /**
     * @var array
     */
    protected $cartPluginSettings = [];

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @param SessionHandler $sessionHandler
     */
    public function injectSessionHandler(SessionHandler $sessionHandler)
    {
        $this->sessionHandler = $sessionHandler;
    }

    /**
     * @param CartRepository $cartRepository
     */
    public function injectCartRepository(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param PaymentRepository $paymentRepository
     */
    public function injectPaymentRepository(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    protected function initializeAction()
    {
        $this->pluginSettings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'CartMpay24'
        );

        $this->cartPluginSettings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'Cart'
        );
    }

    public function confirmAction()
    {
        if ($this->request->hasArgument('hash') && !empty($this->request->getArgument('hash'))) {
            $hash = $this->request->getArgument('hash');

            $querySettings = $this->objectManager->get(
                \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class
            );
            $querySettings->setStoragePageIds([$this->cartPluginSettings['settings']['order']['pid']]);
            $this->cartRepository->setDefaultQuerySettings($querySettings);

            $this->cart = $this->cartRepository->findOneBySHash($hash);

            if ($this->cart) {
                $orderItem = $this->cart->getOrderItem();
                $payment = $orderItem->getPayment();

                if ($payment->getStatus() !== 'paid') {
                    $payment->setStatus('paid');

                    $this->paymentRepository->update($payment);
                    $this->persistenceManager->persistAll();

                    $this->invokeFinishers($orderItem, 'success');
                }
                $this->redirect('show', 'Cart\Order', 'Cart', ['orderItem' => $orderItem]);
            } else {
                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'tx_cartmpay24.controller.order.payment.action.confirm.error_occured',
                        $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                );
            }
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'tx_cartmpay24.controller.order.payment.action.confirm.access_denied',
                    $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
        }
    }

    public function successAction()
    {
        if ($this->request->hasArgument('hash') && !empty($this->request->getArgument('hash'))) {
            $hash = $this->request->getArgument('hash');

            $querySettings = $this->objectManager->get(
                \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class
            );
            $querySettings->setStoragePageIds([$this->cartPluginSettings['settings']['order']['pid']]);
            $this->cartRepository->setDefaultQuerySettings($querySettings);

            $this->cart = $this->cartRepository->findOneBySHash($hash);

            if ($this->cart) {
                $orderItem = $this->cart->getOrderItem();
                $payment = $orderItem->getPayment();

                if ($payment->getStatus() !== 'paid') {
                    $payment->setStatus('paid');

                    $this->paymentRepository->update($payment);
                    $this->persistenceManager->persistAll();

                    $this->invokeFinishers($orderItem, 'success');
                }
                $this->redirect('show', 'Cart\Order', 'Cart', ['orderItem' => $orderItem]);
            } else {
                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'tx_cartmpay24.controller.order.payment.action.success.error_occured',
                        $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                );
            }
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'tx_cartmpay24.controller.order.payment.action.success.access_denied',
                    $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
        }
    }

    public function cancelAction()
    {
        if ($this->request->hasArgument('hash') && !empty($this->request->getArgument('hash'))) {
            $hash = $this->request->getArgument('hash');

            $querySettings = $this->objectManager->get(
                \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class
            );
            $querySettings->setStoragePageIds([$this->cartPluginSettings['settings']['order']['pid']]);
            $this->cartRepository->setDefaultQuerySettings($querySettings);

            $this->cart = $this->cartRepository->findOneByFHash($hash);

            if ($this->cart) {
                $orderItem = $this->cart->getOrderItem();
                $payment = $orderItem->getPayment();

                $payment->setStatus('canceled');

                $this->paymentRepository->update($payment);
                $this->persistenceManager->persistAll();

                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'tx_cartmpay24.controller.order.payment.action.cancel.successfully_canceled',
                        $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::OK
                );

                $this->invokeFinishers($orderItem, 'cancel');

                $this->redirect('show', 'Cart\Cart', 'Cart');
            } else {
                $this->addFlashMessage(
                    LocalizationUtility::translate(
                        'tx_cartmpay24.controller.order.payment.action.cancel.error_occured',
                        $this->extensionName
                    ),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
                );
            }
        } else {
            $this->addFlashMessage(
                LocalizationUtility::translate(
                    'tx_cartmpay24.controller.order.payment.action.cancel.access_denied',
                    $this->extensionName
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
        }
    }

    /**
     * Executes all finishers of this form
     *
     * @param \Extcode\Cart\Domain\Model\Order\Item $orderItem
     * @param string $returnStatus
     */
    protected function invokeFinishers(\Extcode\Cart\Domain\Model\Order\Item $orderItem, string $returnStatus)
    {
        $cart = $this->sessionHandler->restore($this->cartPluginSettings['settings']['cart']['pid']);

        $finisherContext = $this->objectManager->get(
            \Extcode\Cart\Domain\Finisher\FinisherContext::class,
            $this->cartPluginSettings,
            $cart,
            $orderItem,
            $this->getControllerContext()
        );

        if (is_array($this->pluginSettings['finishers']) &&
            is_array($this->pluginSettings['finishers']['order']) &&
            is_array($this->pluginSettings['finishers']['order'][$returnStatus])
        ) {
            ksort($this->pluginSettings['finishers']['order'][$returnStatus]);
            foreach ($this->pluginSettings['finishers']['order'][$returnStatus] as $finisherConfig) {
                $finisherClass = $finisherConfig['class'];

                if (class_exists($finisherClass)) {
                    $finisher = $this->objectManager->get($finisherClass);
                    $finisher->execute($finisherContext);
                    if ($finisherContext->isCancelled()) {
                        break;
                    }
                } else {
                    $logManager = $this->objectManager->get(
                        \TYPO3\CMS\Core\Log\LogManager::class
                    );
                    $logger = $logManager->getLogger(__CLASS__);
                    $logger->error('Can\'t find Finisher class \'' . $finisherClass . '\'.', []);
                }
            }
        }
    }
}
