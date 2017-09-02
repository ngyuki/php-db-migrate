<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->path('bin/')
    ->path('src/')
    ->path('tests/')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,

        // list の後のカッコまで消されるけど list はキーワードでは？
        'no_spaces_after_function_name' => false,
    ])
    ->setFinder($finder)
;
