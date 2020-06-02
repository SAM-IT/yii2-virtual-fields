<?php

namespace SamIT\Yii2\VirtualFields\Tests;

use SamIT\Yii2\VirtualFields\VirtualFieldBehavior;

class VirtualFieldBehaviorCest
{
    public function _before(FunctionalTester $I)
    {
        \tests\Author::createTable();
        \tests\Post::createTable();
    }

    // tests
    public function testLazy(FunctionalTester $I)
    {
        $author = new \tests\Author();
        $author->name = 'test';
        $I->assertTrue($author->save());
        $post= new \tests\Post();
        $post->name = 'test post';
        $post->author_id = $author->id;
        $I->assertTrue($post->save());
        $I->assertSame(1, $author->postCount);
        $I->assertSame("1", $author->postCountWithoutCast);
    }

    public function testRefresh(FunctionalTester $I)
    {
        $author = new \tests\Author();
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

    public function testDetach(FunctionalTester $I)
    {
        $author = new \tests\Author();
        $author->name = 'test';
        $I->assertTrue($author->save());
        $post= new \tests\Post();
        $post->name = 'test post';
        $post->author_id = $author->id;
        $I->assertTrue($post->save());
        $I->assertSame(1, $author->postCount);
        $behavior = $author->detachBehavior(VirtualFieldBehavior::class);
        $I->assertInstanceOf(VirtualFieldBehavior::class, $behavior);
        $I->expectThrowable(\yii\base\UnknownPropertyException::class, function () use ($author) {
            $author->postCount;
        });
        $I->assertTrue($behavior->canGetProperty('postCount'));
        $author->attachBehavior(VirtualFieldBehavior::class, $behavior);
    }
}
