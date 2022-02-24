<?php
declare(strict_types=1);

namespace tests;

use SamIT\Yii2\VirtualFields\VirtualFieldBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class Author
 * @property int $id
 * @property string $name
 * @property int $postCount
 * @property float $postCountFloat
 * @property string $postCountWithoutCast
 * @property int $greedyPostCount
 *
 * @method static ActiveQuery&\SamIT\Yii2\VirtualFields\VirtualFieldBehavior find()
 * @mixin \SamIT\Yii2\VirtualFields\VirtualFieldBehavior
 */
class Author extends ActiveRecord
{
    private static null|array $virtualFields;
    private static function virtualFields(): array
    {
        if (!isset(self::$virtualFields)) {
            self::$virtualFields = [
                'postCount' => [
                    VirtualFieldBehavior::LAZY => fn(Author $author): int|null|string => $author->getPosts()->count(),
                    VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_INT,
                    VirtualFieldBehavior::GREEDY => Post::find()
                        ->andWhere('[[author_id]] = [[author]].[[id]]')
                        ->limit(1)
                        ->select('count(*)')
                ],
                'postCountFloat' => [
                    VirtualFieldBehavior::LAZY => fn(Author $author): int|null|string => $author->getPosts()->count(),
                    VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_FLOAT,
                    VirtualFieldBehavior::GREEDY => Post::find()
                        ->andWhere('[[author_id]] = [[author]].[[id]]')
                        ->limit(1)
                        ->select('count(*) + 0.5')
                ],
                'postCountWithoutCast' => [
                    VirtualFieldBehavior::LAZY => fn(Author $author):  int|null|string => $author->getPosts()->count(),
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
                ],
                'lazyPostCount' => [
                    VirtualFieldBehavior::LAZY => fn(Author $author): int|null|string =>$author->getPosts()->count(),
                    VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_INT,
                ],
            ];
        }
        return self::$virtualFields;
    }
    public function behaviors(): array
    {
        return [
            VirtualFieldBehavior::class => [
                'class' => VirtualFieldBehavior::class,
                'virtualFields' => self::virtualFields()
            ]
        ];
    }
    public static function createTable(): void
    {
        $schema = self::getDb()->schema;
        self::getDb()->createCommand()->createTable(self::tableName(), [
            'id' => $schema->createColumnSchemaBuilder('pk'),
            'name' => $schema->createColumnSchemaBuilder('string'),
        ])->execute();
    }


    public function getPosts(): ActiveQuery
    {
        return $this->hasMany(Post::class, ['author_id' => 'id']);
    }
}
