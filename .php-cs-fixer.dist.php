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
        '@PSR1' => true,
        '@PSR2' => true,
        'lowercase_cast' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_leading_import_slash' => true,
        'no_whitespace_in_blank_line' => true,
        'return_type_declaration' => true,
        'short_scalar_cast' => true,
        'blank_lines_before_namespace' => true,
        'ternary_operator_spaces' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'native_function_invocation' => ['include' => ['@compiler_optimized']],
        'method_argument_space' => ['on_multiline' => 'ignore'],
        'ordered_class_elements' => true,
        'concat_space' => ['spacing' => 'one'],
        'strict_comparison' => true,
        'strict_param' => true,
        'declare_strict_types' => true,
        'class_attributes_separation' => [
            'elements' => ['method' => 'one'],
        ],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/var/.php_cs.cache')
;
