<?php
declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields\exceptions;

class FieldNotFoundException extends \Exception
{
    public function __construct(string $field)
    {
        parent::__construct("Unknown virtual field: $field");
    }
}
