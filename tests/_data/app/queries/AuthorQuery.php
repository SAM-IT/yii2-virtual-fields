<?php

declare(strict_types=1);
namespace tests;

use SamIT\Yii2\VirtualFields\VirtualFieldQueryTrait;
use yii\db\ActiveQuery;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class AuthorQuery extends ActiveQuery
{
    use VirtualFieldQueryTrait;
}
