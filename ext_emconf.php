<?php

$EM_CONF['cart_mpay24'] = [
    'title' => 'Cart - mPAY24',
    'description' => 'Shopping Cart(s) for TYPO3 - mPAY24 Payment Provider',
    'category' => 'services',
    'author' => 'Daniel Gohlke',
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
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-8.7.99',
            'cart' => '5.3.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
