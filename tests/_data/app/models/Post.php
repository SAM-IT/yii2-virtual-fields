<?php
declare(strict_types=1);

namespace tests;


use yii\db\ActiveRecord;

/**
 * Class Post
 * @property int $id
 * @property ?string $name
 * @property int $author_id
 */
class Post extends ActiveRecord
{


    public static function createTable()
    {
        $schema = self::getDb()->schema;
        self::getDb()->createCommand()->createTable(self::tableName(), [
            'id' => $schema->createColumnSchemaBuilder('pk'),
            'name' => $schema->createColumnSchemaBuilder('string'),
            'author_id' => $schema->createColumnSchemaBuilder('integer')->notNull()
        ])->execute();
    }
}