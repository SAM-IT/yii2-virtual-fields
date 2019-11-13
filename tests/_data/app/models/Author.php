<?php
declare(strict_types=1);

namespace tests;


use SamIT\Yii2\VirtualFields\VirtualFieldBehavior;
use yii\db\ActiveRecord;

class Author extends ActiveRecord
{
    public function behaviors()
    {
        return [
            VirtualFieldBehavior::class => [
                'class' => VirtualFieldBehavior::class,
                'virtualFields' => [
                    'postCount' => [
                        VirtualFieldBehavior::LAZY => function(Author $author) { return $this->getPosts()->count(); },
                        VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_INT,
                        VirtualFieldBehavior::GREEDY => Post::find()
                            ->andWhere('[[author_id]] = [[author]].[[id]]')
                            ->limit(1)
                            ->select('count(*)')
                    ]
                ]
            ]
        ];
    }
    public static function createTable()
    {
        $schema = self::getDb()->schema;
        self::getDb()->createCommand()->createTable(self::tableName(), [
            'id' => $schema->createColumnSchemaBuilder('pk'),
            'name' => $schema->createColumnSchemaBuilder('string'),
        ])->execute();
    }


    public function getPosts() {
        return $this->hasMany(Post::class, ['author_id' => 'id']);
    }

}