<?php
declare(strict_types=1);
return [
    'id' => 'yii2-virtualfields',
    'basePath' => dirname(dirname(__DIR__)),
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite::memory:'
        ]
    ]
];