<?php
return [
    'file_list' => [],

    'exclude_file_regex' => '@^vendor/.*/(tests|Tests|test|Test)/@',

    'exclude_file_list' => [],

    'directory_list' => [
        'src/',
        'vendor/symfony/console/',
        'vendor/symfony/filesystem/',
    ],

    "exclude_analysis_directory_list" => [
        'vendor/'
    ],
];
