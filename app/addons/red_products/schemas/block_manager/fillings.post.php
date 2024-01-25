<?php

$schema['category'] = array (
    'limit' => array (
        'type' => 'input',
        'default_value' => 3,
    ),
    'cid' => array (
        'type' => 'picker',
        'option_name' => 'filter_by_categories',
        'picker' => 'pickers/categories/picker.tpl',
        'picker_params' => array(
                'multiple' => true,
                'use_keys' => 'N',
                'view_mode' => 'table',
        ),
        'unset_empty' => true, // remove this parameter from params list if the value is empty
    ),
);

return $schema;
