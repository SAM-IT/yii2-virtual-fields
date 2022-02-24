<?php

namespace SamIT\Yii2\VirtualFields\Tests;

use SamIT\Yii2\VirtualFields\exceptions\FieldNotFoundException;
use SamIT\Yii2\VirtualFields\exceptions\FieldNotGreedyException;
use SamIT\Yii2\VirtualFields\exceptions\FieldNotLoadedException;
use SamIT\Yii2\VirtualFields\VirtualFieldBehavior;
use tests\Author;
use yii\base\UnknownPropertyException;

final class VirtualFieldBehaviorCest
{
    public function _before(FunctionalTester $I): void
    {
        Author::createTable();
        \tests\Post::createTable();
    }

    // tests
    public function testLazy(FunctionalTester $I): void
    {
        $author = new Author();
        $author->name = 'test';
        $I->assertTrue($author->save());
        $post= new \tests\Post();
        $post->name = 'test post';
        $post->author_id = $author->id;
        $I->assertTrue($post->save());
        $I->assertSame(1, $author->postCount);
        $I->assertSame(1, $author->getVirtualField('postCount'));
        $I->assertSame("1", $author->postCountWithoutCast);
    }

    public function testRefresh(FunctionalTester $I): void
    {
        $author = new Author();
        $author->name = 'test';
        $I->assertTrue($author->save());
        $I->assertSame(0, $author->postCount);
        $post= new \tests\Post();
        $post->name = 'test post';
        $post->author_id = $author->id;
        $I->assertTrue($post->save());
        $I->assertSame(0, $author->postCount);
        $author->refresh();
        $I->assertSame(1, $author->postCount);
    }

    public function testDetach(FunctionalTester $I): void
    {
        $author = new Author();
        $author->name = 'test';
        $I->assertTrue($author->save());
        $post= new \tests\Post();
        $post->name = 'test post';
        $post->author_id = $author->id;
        $I->assertTrue($post->save());
        $I->assertSame(1, $author->postCount);

        $behavior = $author->detachBehavior(VirtualFieldBehavior::class);


        $I->assertInstanceOf(VirtualFieldBehavior::class, $behavior);
        /**
         * @var VirtualFieldBehavior $behavior
         */
        $I->expectThrowable(\yii\base\UnknownPropertyException::class, function () use ($author) {
            /** @phpstan-ignore-next-line */
            $author->postCount;
        });
        $I->assertTrue($behavior->canGetProperty('postCount'));
        $author->attachBehavior(VirtualFieldBehavior::class, $behavior);
    }

    public function testGreedyField(FunctionalTester $I): void
    {
        $author = new Author();
        $author->name = 'test';
        $I->assertTrue($author->save());

        $I->expectThrowable(FieldNotLoadedException::class, fn() => $author->greedyPostCount);
    }

    public function testFloatCast(FunctionalTester $I): void
    {
        $author = new Author();

        $I->assertIsFloat($author->postCountFloat);
    }

    public function testWriteOnce(FunctionalTester $I): void
    {
        $author = new Author();
        $author->postCountFloat = 1.0;
        $I->expectThrowable(UnknownPropertyException::class, fn() => $author->postCountFloat = 1.0);
    }

    public function testGetVirtualExpression(FunctionalTester $I): void
    {
        $author = new Author();

        $behavior = $author->getBehavior(VirtualFieldBehavior::class);
        $I->assertInstanceOf(VirtualFieldBehavior::class, $behavior);
        /**
         * @var VirtualFieldBehavior $behavior
         */
        $I->assertSame($behavior->virtualFields['postCount'][VirtualFieldBehavior::GREEDY], Author::instance()->getVirtualExpression('postCount'));

        $I->expectThrowable(FieldNotFoundException::class, fn() => $author->getVirtualExpression('someOtherCount'));
        ;

        $I->expectThrowable(FieldNotGreedyException::class, fn() => $author->getVirtualExpression('lazyPostCount'));
    }
}
