<?php

defined('TYPO3_MODE') or die();

$dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$dispatcher->connect(
    'Extcode\Cart\Utility\OrderUtility',
    'handlePayment',
    'Extcode\CartMpay24\Utility\PaymentUtility',
    'handlePayment'
);
