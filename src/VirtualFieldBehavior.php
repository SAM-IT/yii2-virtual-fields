<?php

declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields;

use SamIT\Yii2\VirtualFields\exceptions\FieldNotFoundException;
use SamIT\Yii2\VirtualFields\exceptions\FieldNotGreedyException;
use SamIT\Yii2\VirtualFields\exceptions\FieldNotLoadedException;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\UnknownPropertyException;
use yii\db\ActiveRecord;
use yii\db\ExpressionInterface;

/**
 * @property ActiveRecord $owner
 * @phpstan-type LazyResolver callable(mixed $model):(int|bool|string|null|float)
 * @phpstan-type VirtualFieldConfig array{lazy?: callable, greedy?: ExpressionInterface, cast?: int|bool|string|null|float}
 */
final class VirtualFieldBehavior extends Behavior implements GetVirtualExpression
{
    public const LAZY = 'lazy';
    public const GREEDY = 'greedy';
    public const CAST = 'cast';
    public const CAST_INT = 'int';
    public const CAST_FLOAT = 'float';
    public const CAST_BOOL = 'float';
    public const CAST_STRING = 'string';

    /**
     * Example:
     * [
     *     'postCount' => [
     *         self::LAZY => function($model) { return $model->getPosts()->count; }
     *         self::GREEDY => Post::find()->limit(1)->select('count(*)')->where('author_id = author.id'),
     *         self::CAST => self::CAST_INT
     *
     *     ]
     * ]
     * @phpstan-var array<string, VirtualFieldConfig>
     */
    public array $virtualFields = [];

    /**
     * @var array<string, mixed> The values of the virtual fields indexed by field name
     */
    private array $values = [];

    /**
     * @return array<string, callable>
     */
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_AFTER_REFRESH => $this->resetValues(...),
            ActiveRecord::EVENT_AFTER_UPDATE => $this->resetValues(...)
        ];
    }

    public function getVirtualExpression(string $name): ExpressionInterface
    {
        if (!isset($this->virtualFields[$name])) {
            throw new FieldNotFoundException($name);
        } elseif (!isset($this->virtualFields[$name][self::GREEDY])) {
            throw new FieldNotGreedyException($name);
        }
        return $this->virtualFields[$name][self::GREEDY];
    }

    public function getVirtualField(string $name): mixed
    {
        return $this->resolveValue($name);
    }

    public function resetValues(): void
    {
        $this->values = [];
    }

    /**
     * @throws FieldNotLoadedException
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name): mixed
    {
        if (isset($this->virtualFields[$name])) {
            return $this->resolveValue($name);
        }
        throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    /**
     * This path is used during greedy loading
     */
    public function __set($name, $value): void
    {
        if (isset($this->virtualFields[$name]) && (is_scalar($value) || is_null($value))) {
            $this->setValue($name, $value);
        }
    }

    private function setValue(string $name, int|float|bool|string|null $value): void
    {
        $this->values[$name] = $value === null ? null : match ($this->virtualFields[$name][self::CAST] ?? null) {
            self::CAST_FLOAT => (float) $value,
            self::CAST_INT => (int) $value,
            self::CAST_BOOL => (bool) $value,
            self::CAST_STRING => (string) $value,
            default => $value,
        };
    }

    /**
     * @param string $name
     * @return mixed
     * @throws FieldNotLoadedException
     */
    private function resolveValue(string $name)
    {
        if (!array_key_exists($name, $this->values)) {
            if (!isset($this->virtualFields[$name][self::LAZY])) {
                throw new FieldNotLoadedException($name);
            }

            $closure = $this->virtualFields[$name][self::LAZY];
            if (!is_callable($closure)) {
                throw new InvalidConfigException('Lazy loader must be a callable');
            }

            /** @var mixed $value */
            $value = $closure($this->owner);
            if (is_scalar($value) || $value === null) {
                $this->setValue($name, $value);
            } else {
                throw new InvalidConfigException('Lazy loader must return a scalar or null');
            }
        }

        return $this->values[$name];
    }

    public function detach(): void
    {
        $this->resetValues();
    }


    public function canGetProperty($name, $checkVars = true): bool
    {
        return isset($this->virtualFields[$name]) || parent::canGetProperty($name, $checkVars);
    }

    /**
     * We only support writing once.
     * @param string $name
     * @param bool $checkVars
     * @return bool
     */
    public function canSetProperty($name, $checkVars = true): bool
    {
        return (isset($this->virtualFields[$name]) && !array_key_exists($name, $this->values))
            || parent::canSetProperty($name, $checkVars);
    }
}
