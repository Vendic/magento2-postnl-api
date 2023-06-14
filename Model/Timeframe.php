<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Model;

use Vendic\PostnlApi\Api\Data\TimeframeInterface;
use Magento\Framework\DataObject;

class Timeframe extends DataObject implements TimeframeInterface
{
    public function getDate(): ?string
    {
        return $this->getData(TimeframeInterface::DATE);
    }

    public function getOption(): ?string
    {
        return $this->getData(TimeframeInterface::OPTION);
    }

    public function getFrom(): ?string
    {
        return $this->getData(TimeframeInterface::FROM);
    }

    public function getTo(): ?string
    {
        return $this->getData(TimeframeInterface::TO);
    }
}
