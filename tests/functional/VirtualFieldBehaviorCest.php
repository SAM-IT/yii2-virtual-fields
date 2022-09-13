<?php

declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields\Tests;

use SamIT\Yii2\VirtualFields\exceptions\FieldNotFoundException;
use SamIT\Yii2\VirtualFields\exceptions\FieldNotGreedyException;
use SamIT\Yii2\VirtualFields\exceptions\FieldNotLoadedException;
use SamIT\Yii2\VirtualFields\VirtualFieldBehavior;
use tests\Author;
use tests\Post;
use yii\base\InvalidConfigException;
use yii\base\UnknownPropertyException;
use yii\db\Expression;

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
        $post = new \tests\Post();
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
        $post = new \tests\Post();
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
        $post = new \tests\Post();
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

        $I->expectThrowable(FieldNotLoadedException::class, fn () => $author->greedyPostCount);
    }

    public function testFloatCast(FunctionalTester $I): void
    {
        $author = new Author();

        $I->assertIsFloat($author->postCountFloat);
    }
    public function testStringCast(FunctionalTester $I): void
    {
        $author = new Author();

        $I->assertIsString($author->postCountString);
    }

    public function testWriteOnce(FunctionalTester $I): void
    {
        $author = new Author();
        $author->postCountFloat = 1.0;
        $I->expectThrowable(UnknownPropertyException::class, fn () => $author->postCountFloat = 1.0);
    }

    public function testSetNull(FunctionalTester $I): void
    {
        $subject = new VirtualFieldBehavior();
        $subject->virtualFields = [
            'test' => [
                VirtualFieldBehavior::LAZY => fn (): float => 15.5,
                VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_FLOAT,
            ]
        ];
        /**
         * @psalm-suppress UndefinedMagicPropertyAssignment
         * @phpstan-ignore-next-line
         */
        $subject->test = null;
        $I->assertNull($subject->test);
    }

    public function testNotCallableLazyField(FunctionalTester $I): void
    {
        $subject = new VirtualFieldBehavior();
        /**
         * @psalm-suppress InvalidPropertyAssignmentValue
         * @phpstan-ignore-next-line
         */
        $subject->virtualFields = [
            'test' => [
                VirtualFieldBehavior::LAZY => 15.5,
                VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_FLOAT,
            ]
        ];
        /**
         * @psalm-suppress UndefinedMagicPropertyFetch
         * @phpstan-ignore-next-line
         */
        $I->expectThrowable(InvalidConfigException::class, fn (): mixed => $subject->test);
    }

    public function testNotScalarLazyField(FunctionalTester $I): void
    {
        $subject = new VirtualFieldBehavior();
        /**
         * @psalm-suppress InvalidPropertyAssignmentValue
         * @phpstan-ignore-next-line
         */
        $subject->virtualFields = [
            'test' => [
                VirtualFieldBehavior::LAZY => fn (): array => [],
            ]
        ];
        /**
         * @psalm-suppress UndefinedMagicPropertyFetch
         * @phpstan-ignore-next-line
         */
        $I->expectThrowable(InvalidConfigException::class, fn (): mixed => $subject->test);
    }

    public function testGetUnknownProperty(FunctionalTester $I): void
    {
        $subject = new VirtualFieldBehavior();
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress UndefinedMagicPropertyFetch
         */
        $I->expectThrowable(UnknownPropertyException::class, fn (): mixed => $subject->abc);
    }

    public function testGetVirtualExpression(FunctionalTester $I): void
    {
        $subject = new VirtualFieldBehavior();

        $expression = new Expression('dummy');
        $subject->virtualFields = [
            'postCountFloat' => [
                VirtualFieldBehavior::LAZY => fn (): float => 15.5,
                VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_FLOAT,
                VirtualFieldBehavior::GREEDY => $expression
            ],
            'postCount' => [
                VirtualFieldBehavior::LAZY => fn (): int => 15,
                VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_INT,
                VirtualFieldBehavior::GREEDY => new Expression('SELECT 15')
            ],
            'greedyPostCount' => [
                VirtualFieldBehavior::GREEDY => Post::find()
                    ->andWhere('[[author_id]] = [[author]].[[id]]')
                    ->limit(1)
                    ->select('count(*)')
            ],
            'postCountWithoutCast' => [
                VirtualFieldBehavior::LAZY => fn (Author $author): int|null|string => $author->getPosts()->count(),
                VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_INT,
                VirtualFieldBehavior::GREEDY => Post::find()
                    ->andWhere('[[author_id]] = [[author]].[[id]]')
                    ->limit(1)
                    ->select('count(*)')
            ],
            'lazyPostCount' => [
                VirtualFieldBehavior::LAZY => fn (Author $author): int|null|string => $author->getPosts()->count(),
                VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_INT,
            ]
        ];





        $I->assertSame($expression, $subject->getVirtualExpression('postCountFloat'));

        $I->expectThrowable(FieldNotFoundException::class, fn () => $subject->getVirtualExpression('someOtherCount'));

        $I->expectThrowable(FieldNotGreedyException::class, fn () => $subject->getVirtualExpression('lazyPostCount'));
    }
}
