<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Service;

use Magento\Quote\Model\Quote\Address;
use Vendic\PostnlApi\Api\SaveInterface;
use Vendic\PostnlApi\Utils\RequestBuilder;

class InitializePostNLOrder
{
    private RequestBuilder $requestBuilder;
    private SaveInterface $save;

    public function __construct(RequestBuilder $requestBuilder, SaveInterface $save)
    {
        $this->requestBuilder = $requestBuilder;
        $this->save = $save;
    }

    /**
     * Initialize a new PostNL order in the DB by calling the save interface.
     * @return void
     */
    public function execute(Address $address)
    {
        $request = $this->requestBuilder->buildForDeliverySave($address);

        $this->save->execute($request);
    }
}
