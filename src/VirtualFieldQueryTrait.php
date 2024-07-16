<?php

declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Trait VirtualFieldQueryTrait
 * Attach this to an ActiveQuery class
 * @mixin ActiveQuery
 */
trait VirtualFieldQueryTrait
{
    /**
     * @param class-string<ActiveRecord> $class
     * @psalm-suppress InvalidReturnType
     * @phpstan-return ActiveRecord&GetVirtualExpression
     */
    private function getModelInstance(string $class): ActiveRecord
    {
        return ($class)::instance();
    }

    /**
     * @param list<string> $fields
     */
    private function addField(ActiveQuery $query, array $fields): void
    {
        /** @var class-string<ActiveRecord> $modelClass */
        $modelClass = $query->modelClass;
        $model = $this->getModelInstance($modelClass);

        $columns = [];
        if ($query->select === null || $query->select === []) {
            $columns[] = '*';
        }
        foreach ($fields as $field) {
            $columns[$field] = $model->getVirtualExpression($field);
        }
        $query->addSelect($columns);
    }

    /**
     * @return $this
     */
    public function withFields(string ...$fields): ActiveQuery
    {
        $this->addField($this, array_values($fields));
        return $this;
    }
}
