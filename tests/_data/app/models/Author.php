<?php
declare(strict_types=1);

namespace tests;

use SamIT\Yii2\VirtualFields\VirtualFieldBehavior;
use yii\db\ActiveRecord;

/**
 * Class Author
 * @property int $id
 * @property string $name
 * @property int $postCount
 * @property string $postCountWithoutCast
 * @property int $greedyPostCount
 */
class Author extends ActiveRecord
{
    public function behaviors()
    {
        return [
            VirtualFieldBehavior::class => [
                'class' => VirtualFieldBehavior::class,
                'virtualFields' => [
                    'postCount' => [
                        VirtualFieldBehavior::LAZY => function (Author $author) {
                            return $author->getPosts()->count();
                        },
                        VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_INT,
                        VirtualFieldBehavior::GREEDY => Post::find()
                            ->andWhere('[[author_id]] = [[author]].[[id]]')
                            ->limit(1)
                            ->select('count(*)')
                    ],
                    'postCountFloat' => [
                        VirtualFieldBehavior::LAZY => function (Author $author) {
                            return $author->getPosts()->count();
                        },
                        VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_FLOAT,
                        VirtualFieldBehavior::GREEDY => Post::find()
                            ->andWhere('[[author_id]] = [[author]].[[id]]')
                            ->limit(1)
                            ->select('count(*) + 0.5')
                    ],
                    'postCountWithoutCast' => [
                        VirtualFieldBehavior::LAZY => function (Author $author) {
                            return $author->getPosts()->count();
                        },
                        VirtualFieldBehavior::GREEDY => Post::find()
                            ->andWhere('[[author_id]] = [[author]].[[id]]')
                            ->limit(1)
                            ->select('count(*)')
                    ],
                    'greedyPostCount' => [
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


    public function getPosts()
    {
        return $this->hasMany(Post::class, ['author_id' => 'id']);
    }
}
