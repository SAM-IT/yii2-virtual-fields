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
class VirtualFieldQueryBehavior extends Behavior
{
    use VirtualFieldQueryTrait;
    /**
     * @param \yii\db\ActiveQuery $owner
     */
    public function attach($owner)
    {
        parent::attach($owner);
        if (!$owner instanceof \yii\db\ActiveQuery) {
            throw new InvalidConfigException('Owner must be an instance of ' . \yii\db\ActiveQuery::class);
        }
    }

    public function withFields(string ...$fields): ActiveQuery
    {
        return $this->addField($this->owner, $fields);
    }
}
