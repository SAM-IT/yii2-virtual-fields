<?php

declare(strict_types=1);

namespace SamIT\Yii2\VirtualFields;

use yii\db\ExpressionInterface;

interface GetVirtualExpression
{
    public function getVirtualExpression(string $name): ExpressionInterface;
}
