<?php

$schema['products']['content']['items']['fillings']['category'] = [
    'params' => [
        'sort_by' => 'timestamp',
        'sort_order' => 'desc',
        'request' => [
            'cid' => '%CATEGORY_ID%'
        ],
    ],
];

return $schema;