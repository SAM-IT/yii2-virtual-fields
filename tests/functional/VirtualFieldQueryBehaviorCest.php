<?php
declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields\Tests;

use SamIT\Yii2\VirtualFields\exceptions\FieldNotFoundException;
use SamIT\Yii2\VirtualFields\VirtualFieldQueryBehavior;
use tests\Author;
use yii\base\InvalidConfigException;
use yii\base\UnknownMethodException;

/**
 * @covers \SamIT\Yii2\VirtualFields\VirtualFieldQueryBehavior
 */
final class VirtualFieldQueryBehaviorCest
{
    public function _before(FunctionalTester $I): void
    {
        \tests\Author::createTable();
        \tests\Post::createTable();
        $author = new \tests\Author();
        $author->name = 'test';
        $author->id = 15;
        $I->assertTrue($author->save());
        $post = new \tests\Post();
        $post->name = 'test post';
        $post->author_id = $author->id;
        $I->assertTrue($post->save());
    }

    // tests
    public function testQuery(FunctionalTester $I): void
    {

        $query = \tests\Author::find();
        $behavior = new VirtualFieldQueryBehavior();
        $I->expectThrowable(UnknownMethodException::class, fn(): mixed => $query->withFields('postCount'));



        $query->attachBehavior(VirtualFieldQueryBehavior::class, $behavior);

        $I->expectThrowable(FieldNotFoundException::class, fn(): mixed => $query->withFields('Invalid'));
        $query->withFields('postCount', 'postCountWithoutCast', 'postCountFloat');
        $I->assertSame([[
            'id' => '15',
            'name' => 'test',
            'postCount' => '1',
            'postCountWithoutCast' => '1',
            'postCountFloat' => '1.5'
        ]], $query->asArray()->all());

        $author = $query->asArray(false)->one();
        $I->assertInstanceOf(\tests\Author::class, $author);
        /**
         * @var Author $author
         */

        $post = new \tests\Post();
        $post->name = 'test post';
        $post->author_id = 15;
        $I->assertTrue($post->save());

        $I->assertSame(1, $author->postCount);
        $I->assertIsFloat($author->postCountFloat);
        $I->assertEqualsWithDelta(1.5, $author->postCountFloat, 0.0001);
        $I->assertSame("1", $author->postCountWithoutCast);
    }

    public function testAttachingToWrongClass(FunctionalTester $I): void
    {
        $author = new Author();
        $I->expectThrowable(InvalidConfigException::class, fn(): mixed => $author->attachBehaviors([
            'test' => [
                'class' => VirtualFieldQueryBehavior::class
            ]
        ]));
    }
}
