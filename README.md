# vendic/magento2-postnl-api 
This module adds [service contracts](https://developer.adobe.com/commerce/php/development/components/service-contracts/) to [postnl/postnl-magento2](https://github.com/postnl/postnl-magento2), since the original module works through controllers only.

This allows you to use the PostNL API in your own Magento classes, without using a request. We use it in the PostNL module for the [Hyvä checkout](https://www.hyva.io/hyva-checkout.html).

## Installation
```bash
composer require vendic/magento2-postnl-api
```

## Currently supported service contracts
### Locations
Service contract: `\Vendic\PostnlApi\Api\LocationsInterface`

Get's the nearest PostNL locations based on a given address.

### Save
Serivce contract: `\Vendic\PostnlApi\Api\SaveInterface`

Saves/initalizes a shipment into the Magento database.

### Timeframes (WIP)
Service contract: `\Vendic\PostnlApi\Api\TimeframesInterface`

Get's the available delivery timeframes for a given address.
