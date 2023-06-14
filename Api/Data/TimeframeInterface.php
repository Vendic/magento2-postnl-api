<?php
declare(strict_types=1);

/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\PostnlApi\Api\Data;


interface TimeframeInterface
{
    public const DATE = 'date';
    public const OPTION = 'option';
    public const FROM = 'from';
    public const TO = 'to';

    public function getDate();
    public function getOption();
    public function getFrom();
    public function getTo();
}
