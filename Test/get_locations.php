<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '5G');
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;
require __DIR__ . '/../../../../../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

/** @var \Vendic\PostnlApi\Api\LocationsInterface $locations */
$locations = $objectManager->get(\Vendic\PostnlApi\Api\LocationsInterface::class);

/** @var \Vendic\PostnlApi\Request\PostnlRequestFactory $postNLRequestFactory */
$postnlRequestFactory = $objectManager->get(\Vendic\PostnlApi\Request\PostnlRequestFactory::class);

$address = [
    'country' => 'NL',
    'street' => 'Keulenstraat',
    'postcode' => '7418ET',
    'housenumber' => '7',
    'firstname' => 'Tjitse',
    'lastname' => 'Efdé',
    'telephone' => '0612345678',
];

/** @var \Vendic\PostnlApi\Request\PostnlRequest $postnlRequest */
$postnlRequest = $postnlRequestFactory->create();
$postnlRequest->setAddress($address);

var_dump($locations->get($postnlRequest));

