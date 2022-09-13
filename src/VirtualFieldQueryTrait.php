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
     * @param ActiveQuery $query
     * @param list<string> $fields
     * @return ActiveQuery
     * @throws exceptions\FieldNotFoundException
     */
    private function addField(ActiveQuery $query, array $fields): ActiveQuery
    {
        /** @var class-string<ActiveRecord> $modelClass */
        $modelClass = $query->modelClass;
        $model = $this->getModelInstance($modelClass);

        $columns = [];
        if (empty($query->select)) {
            $columns[] = '*';
        }
        foreach ($fields as $field) {
            $columns[$field] = $model->getVirtualExpression($field);
        }
        $query->addSelect($columns);
        return $query;
    }

    public function withFields(string ...$fields): ActiveQuery
    {
        return $this->addField($this, array_values($fields));
    }
}
