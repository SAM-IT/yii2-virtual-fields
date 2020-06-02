<?php
declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields;

use yii\db\ActiveQuery;

/**
 * Trait VirtualFieldQueryTrait
 * Attach this to an ActiveQuery class
 */
trait VirtualFieldQueryTrait
{

    private function addField(ActiveQuery $query, array $fields): ActiveQuery
    {
        /** @var \yii\db\ActiveRecord $model */
        $model = $query->modelClass::instance();
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
        return $this->addField($this, $fields);
    }
}
