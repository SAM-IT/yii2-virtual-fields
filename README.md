# yii2-virtual-fields
Implementation of virtual fields for Yii2 AR

To work this library needs to change 2 parts of the Yii2 ORM. 
- The model definitions
- The query implementation

# Installation

```
$ composer require sam-it/yii2-virtual-fields
```

# Configuration
The change to `ActiveQuery` are simple and can be applied using a trait or a behavior. In case you did not subclass
`ActiveQuery` you can choose to attach the behavior dynamically.
```php
use SamIT\Yii2\VirtualFields\VirtualFieldQueryBehavior;
use SamIT\Yii2\VirtualFields\VirtualFieldBehavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
class Author extends ActiveRecord 
{
    /**
     * Attach the behavior after constructing the query object  
     * @return ActiveQuery
     */
    public static function find()
    {
        $query = parent::find();
        $query->attachBehavior(VirtualFieldQueryBehavior::class, VirtualFieldQueryBehavior::class);
        return $query;
    }
    
    public function getPosts(): ActiveQuery
    {
        return $this->hasMany(Post::class, ['author_id' => 'id']);
    }

    public function behaviors() 
    {
        return [
            VirtualFieldBehavior::class => [
                'class' => VirtualFieldBehavior::class,
                'virtualFields' => [
                    'postCount' => [
                        VirtualFieldBehavior::LAZY => function(Author $author) { return $author->getPosts()->count(); },
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

}
```

Since Yii uses the DI container to create the object, it is also possible to add the behavior globally by defining it in the DI container.
Virtual fields allow you to define model attributes as SQL fragments. The advantage of this implementation is that it supports both lazy and greedy loading.

# Usage

The library will then take care of everything:
```php
    Author::findByPk(1)->postCount; // Lazy loaded 
    Author::find()->withField('postCount')->one()->postCount; // Greedy loaded
```
    
If lazy loading is not implemented and an attribute is used lazily, an exception will be thrown. If greedy loading is not implemented and the field is added to select the normal Yii2 / SQL exception is thrown.

We found these classes greatly helped us in reducing the number of queries (implementing `getPostCount()` on `Author` is not ideal).

To maximize compatibility and minimize issues we chose not to use joins, since they can potentially affect the number of records. In a number of cases the resulting query plan could be less than optimal.

We chose not to overload `ActiveQuery::select()` to support virtual fields. Reason for this is the fact that it changes the semantics of `*`; `*` would not by default include all virtual fields.

# Test

[Codeception](https://codeception.com/) is used for unit testing. To run tests:

```
$ ./vendor/bin/codecept run
```
