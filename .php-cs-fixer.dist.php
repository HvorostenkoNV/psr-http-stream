<?php

declare(strict_types=1);

use PhpCsFixer\{
    Config,
    Finder,
};

$codeDirectory  = 'src';
$testsDirectory = 'tests';

return (new Config())
    ->setRules([
        '@PhpCsFixer'                                       => true,
        '@PHP81Migration'                                   => true,
        '@PHPUnit84Migration:risky'                         => true,

        'modernize_strpos'                                  => true,
        'no_alias_functions'                                => [
            'sets'                                              => ['@all'],
        ],
        'set_type_to_cast'                                  => true,

        'psr_autoloading'                                   => [
            'dir'                                               => "./$codeDirectory",
        ],

        'class_attributes_separation'                       => [
            'elements'                                          => [
                'const'                                             => 'none',
                'method'                                            => 'none',
                'property'                                          => 'none',
                'trait_import'                                      => 'none',
                'case'                                              => 'none',
            ],
        ],
        'class_definition'                                  => [
            'multi_line_extends_each_single_line'               => false,
            'single_item_single_line'                           => false,
            'single_line'                                       => false,
            'space_before_parenthesis'                          => true,
            'inline_constructor_arguments'                      => false,
        ],
        'no_php4_constructor'                               => true,
        'no_unneeded_final_method'                          => true,
        'ordered_interfaces'                                => [
            'order'                                             => 'alpha',
            'direction'                                         => 'ascend',
        ],
        'ordered_traits'                                    => true,
        'self_accessor'                                     => true,
        'self_static_accessor'                              => true,

        'date_time_immutable'                               => true,

        'empty_loop_body'                                   => [
            'style'                                             => 'braces',
        ],
        'simplified_if_return'                              => true,
        'yoda_style'                                        => false,

        'combine_nested_dirname'                            => true,
        'date_time_create_from_format_call'                 => true,
        'fopen_flag_order'                                  => true,
        'fopen_flags'                                       => [
            'b_mode'                                            => true,
        ],
        'implode_call'                                      => true,
        'no_unreachable_default_argument_value'             => true,
        'no_useless_sprintf'                                => true,
        'nullable_type_declaration_for_default_null_value'  => true,
        'phpdoc_to_param_type'                              => [
            'scalar_types'                                      => true,
        ],
        'phpdoc_to_property_type'                           => [
            'scalar_types'                                      => true,
        ],
        'phpdoc_to_return_type'                             => [
            'scalar_types'                                      => true,
        ],
        'regular_callable_call'                             => true,
        'single_line_throw'                                 => false,
        'use_arrow_functions'                               => true,
        'void_return'                                       => true,

        'global_namespace_import'                           => [
            'import_constants'                                  => true,
            'import_functions'                                  => true,
            'import_classes'                                    => true,
        ],
        'group_import'                                      => true,
        'ordered_imports'                                   => [
            'imports_order'                                     => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm'                                    => 'alpha',
        ],
        'single_import_per_statement'                       => false,

        'declare_parentheses'                               => true,
        'dir_constant'                                      => true,
        'function_to_constant'                              => [
            'functions'                                         => [
                'get_called_class',
                'get_class',
                'get_class_this',
                'php_sapi_name',
                'phpversion',
                'pi',
            ],
        ],
        'get_class_to_class_keyword'                        => true,
        'no_unset_on_property'                              => true,

        'binary_operator_spaces'                            => [
            'operators'                                         => [
                '='                                                 => 'align_single_space',
                '=>'                                                => 'align_single_space',
            ],
        ],
        'increment_style'                                   => [
            'style'                                             => 'post',
        ],
        'logical_operators'                                 => true,

        'php_unit_construct'                                => [
            'assertions'                                        => [
                'assertEquals',
                'assertSame',
                'assertNotEquals',
                'assertNotSame',
            ],
        ],
        'php_unit_fqcn_annotation'                          => false,
        'php_unit_mock_short_will_return'                   => true,
        'php_unit_set_up_tear_down_visibility'              => true,
        'php_unit_size_class'                               => [
            'group'                                             => 'small',
        ],
        'php_unit_strict'                                   => [
            'assertions'                                        => [
                'assertAttributeEquals',
                'assertAttributeNotEquals',
                'assertEquals',
                'assertNotEquals',
            ],
        ],
        'php_unit_test_annotation'                          => [
            'style'                                             => 'prefix',
        ],
        'php_unit_test_case_static_method_calls'            => [
            'call_type'                                         => 'static',
            'methods'                                           => [],
        ],

        'general_phpdoc_annotation_remove'                  => [
            'annotations'                                       => [
                'author',
            ],
        ],
        'no_superfluous_phpdoc_tags'                        => [
            'allow_mixed'                                       => true,
            'allow_unused_params'                               => false,
        ],

        'simplified_null_return'                            => true,

        'multiline_whitespace_before_semicolons'            => [
            'strategy'                                          => 'no_multi_line',
        ],

        'declare_strict_types'                              => true,
        'strict_comparison'                                 => true,
        'strict_param'                                      => true,

        'blank_line_before_statement'                       => [
            'statements'                                        => [
                'declare',
                'default',
                'exit',
                'goto',
                'return',
                'switch',
                'throw',
                'try',
            ],
        ],
        'blank_line_between_import_groups'                  => true,
    ])
    ->setFinder(
        Finder::create()
            ->in([
                $codeDirectory,
                $testsDirectory,
            ])
    );
