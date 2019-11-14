<?php

use SamIT\Yii2\VirtualFields\exceptions\FieldNotFoundException;
use SamIT\Yii2\VirtualFields\VirtualFieldQueryBehavior;
use yii\base\UnknownMethodException;

class VirtualFieldQueryBehaviorCest
{
    public function _before(FunctionalTester $I)
    {
        \tests\Author::createTable();
        \tests\Post::createTable();
        $author = new \tests\Author();
        $author->name = 'test';
        $author->id = 15;
        $I->assertTrue($author->save());
        $post= new \tests\Post();
        $post->name = 'test post';
        $post->author_id = $author->id;
        $I->assertTrue($post->save());
    }

    // tests
    public function testQuery(FunctionalTester $I)
    {

        $query = \tests\Author::find();
        $behavior = new VirtualFieldQueryBehavior();
        $I->expectThrowable(UnknownMethodException::class, function() use ($query) {
            $query->withFields('postCount');
        });


        $query->attachBehavior(VirtualFieldQueryBehavior::class, $behavior);
        $I->expectThrowable(FieldNotFoundException::class, function() use ($query) {
            $query->withFields('Invalid');
        });
        $query->withFields('postCount', 'postCountWithoutCast');
        $I->assertSame([[
            'id' => '15',
            'name' => 'test',
            'postCount' => '1',
            'postCountWithoutCast' => '1'
        ]], $query->asArray()->all());


        $author = $query->asArray(false)->one();
        $I->assertInstanceOf(\tests\Author::class, $author);

        $post = new \tests\Post();
        $post->name = 'test post';
        $post->author_id = 15;
        $I->assertTrue($post->save());

        $I->assertSame(1, $author->postCount);
        $I->assertSame("1", $author->postCountWithoutCast);
    }
}
