<?php

declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields\exceptions;

/**
 * @codeCoverageIgnore
 */
final class FieldNotLoadedException extends \Exception
{
    public function __construct(string $field)
    {
        parent::__construct("Attempted to access field $field, but it does not support lazy loading and was not loaded");
    }
}
