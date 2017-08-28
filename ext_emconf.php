<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Cart - mPay24',
    'description' => 'Shopping Cart(s) for TYPO3 - mPay24 Payment Provider',
    'category' => 'services',
    'author' => 'Daniel Lorenz',
    'author_email' => 'ext.cart@extco.de',
    'author_company' => 'extco.de UG (haftungsbeschränkt)',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-8.7.99',
            'php' => '5.6.0',
            'cart' => '4.0.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
