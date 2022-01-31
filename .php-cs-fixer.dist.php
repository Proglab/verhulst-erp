<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['node_modules', 'var'])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'no_unneeded_final_method' => false,
        'dir_constant' => true,
        'modernize_types_casting' => true,
        'echo_tag_syntax' => ['format' => 'long'],
        'no_useless_else' => true,
        'no_useless_return' => true,
        'native_function_invocation' => ['include' => ['@compiler_optimized']],
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'php_unit_construct' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'semicolon_after_instruction' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'declare_strict_types' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/var/.php_cs.cache')
    ;
