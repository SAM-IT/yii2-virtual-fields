<?php

declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SamIT\Yii2\VirtualFields\VirtualFieldBehavior;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class VirtualFieldBehaviorTest extends TestCase
{
    public function testBoolCast(): void
    {
        $subject = new VirtualFieldBehavior();

        $subject->virtualFields = [
            'bool' => [
                VirtualFieldBehavior::LAZY => fn (): int => 1,
                VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_BOOL,
            ],
            'false' => [
                VirtualFieldBehavior::LAZY => fn (): int => 0,
                VirtualFieldBehavior::CAST => VirtualFieldBehavior::CAST_BOOL,
            ]
        ];

        $this->assertIsBool($subject->getVirtualField('bool'));
        $this->assertTrue($subject->getVirtualField('bool'));
        $this->assertIsBool($subject->getVirtualField('false'));
        $this->assertFalse($subject->getVirtualField('false'));
    }
}
