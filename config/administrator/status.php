<?php

use App\Models\Status;

return [

    'title' => '文章',
    'heading' => '文章管理',
    'single' => '文章',
    'model' => Status::class,

    'columns' => [
        'id' => [
            'title' => 'ID'
        ],
        'content' => [
            'title' => 'Content',
            'sortable' => false,
            'output' => function($value)
            {
                return str_limit($value);
            },
        ],
        'user_id' => [
            'title' => "User_id",
        ],
        'created_at',

        'operation' => [
            'title'  => '管理',
            'output' => function ($value, $model) {
                return $value;
            },
            'sortable' => false,
        ],
    ],

    'edit_fields' => [
        'content' => [
            'title' => '内容',
            'type' => 'textarea'
        ]
    ],

];
