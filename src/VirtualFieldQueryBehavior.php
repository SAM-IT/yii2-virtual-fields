<?php

declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

/**
 * Class VirtualFieldBehavior
 * Attach this to ActiveQuery.
 * @property \yii\db\ActiveQuery $owner
 */
final class VirtualFieldQueryBehavior extends Behavior
{
    use VirtualFieldQueryTrait;

    public function attach($owner): void
    {
        parent::attach($owner);
        if (!$owner instanceof \yii\db\ActiveQuery) {
            throw new InvalidConfigException('Owner must be an instance of ' . \yii\db\ActiveQuery::class);
        }
    }

    /**
     * @throws exceptions\FieldNotFoundException
     */
    public function withFields(string ...$fields): ActiveQuery
    {
        $this->addField($this->owner, array_values($fields));
        return $this->owner;
    }
}
