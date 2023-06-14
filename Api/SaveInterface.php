<?php
declare(strict_types=1);

/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Api;

use Vendic\PostnlApi\Api\Data\PostnlRequestInterface;

interface SaveInterface
{
    public function execute(PostnlRequestInterface $postnlRequest): bool;
}
