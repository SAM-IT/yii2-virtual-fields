<?php

declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields\exceptions;

/**
 * @codeCoverageIgnore
 */
final class FieldNotGreedyException extends \Exception
{
    public function __construct(string $field)
    {
        parent::__construct("Attempted to load field $field greedily, but it does not support greedy loading");
    }
}
