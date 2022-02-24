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
     * @return ActiveRecord&VirtualFieldBehavior
     * @psalm-suppress InvalidReturnType
     * @param class-string<ActiveRecord> $class
     */
    private function getModelInstance($class): ActiveRecord
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
        /** @var class-string<ActiveRecord> $query->modelClass */
        $model = $this->getModelInstance($query->modelClass);
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
