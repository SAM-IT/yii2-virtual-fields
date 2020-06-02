<?php
declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields;

use SamIT\Yii2\VirtualFields\exceptions\FieldNotFoundException;
use SamIT\Yii2\VirtualFields\exceptions\FieldNotLoadedException;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\ExpressionInterface;

class VirtualFieldBehavior extends Behavior
{
    public const LAZY = 'lazy';
    public const GREEDY = 'greedy';
    public const CAST = 'cast';
    public const CAST_INT = 'int';

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
     *
     * @var array Virtual field definitions.
     *
     */
    public $virtualFields = [];

    /**
     * @var array|mixed[string] The values of the virtual fields indexed by field name
     */
    private $values = [];

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_REFRESH => 'resetValues',
            ActiveRecord::EVENT_AFTER_UPDATE => 'resetValues'
        ];
    }

    public function getVirtualExpression($name): ExpressionInterface
    {
        if (!isset($this->virtualFields[$name])) {
            throw new FieldNotFoundException($name);
        }
        return $this->virtualFields[$name][self::GREEDY];
    }

    public function resetValues()
    {
        $this->values = [];
    }

    /**
     * @param string $name
     * @return mixed
     * @throws FieldNotLoadedException
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        if (isset($this->virtualFields[$name])) {
            return $this->resolveValue($name);
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (isset($this->virtualFields[$name])) {
            $this->setValue($name, $value);
        } else {
            parent::__set($name, $value);
        }
    }

    private function setValue($name, $value)
    {
        switch ($this->virtualFields[$name][self::CAST] ?? null) {
            case self::CAST_INT:
                $this->values[$name] = (int) $value;
                break;
            default:
                $this->values[$name] = $value;
        }
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
            $this->setValue($name, $this->virtualFields[$name][self::LAZY]($this->owner));
        }

        return $this->values[$name];
    }

    public function detach()
    {
        $this->resetValues();
    }


    public function canGetProperty($name, $checkVars = true)
    {
        return isset($this->virtualFields[$name]) || parent::canGetProperty($name, $checkVars);
    }

    /**
     * We only support writing once.
     * @param string $name
     * @param bool $checkVars
     * @return bool
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return (isset($this->virtualFields[$name]) && !array_key_exists($name, $this->values))
            || parent::canSetProperty($name, $checkVars);
    }
}
