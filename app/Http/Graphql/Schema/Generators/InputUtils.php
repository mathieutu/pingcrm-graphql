<?php


namespace App\Http\Graphql\Schema\Generators;


use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Str;

class InputUtils
{
    public const WHERE_OPERATORS = [
        '_eq' => '=',
        '_neq' => '<>',
        '_gt' => '>',
        '_lt' => '<',
        '_gte' => '>=',
        '_lte' => '<=',
        '_is_null' => '_is_null',
        '_in' => '_in',
        '_nin' => '_nin',
    ];

    private const WHERE_OPERATORS_IN = ['_in', '_nin'];

    public const WHERE_OPERATORS_STRING = [
        '_like' => 'LIKE',
        '_nlike' => 'NOT LIKE',
        '_ilike' => 'ILIKE',
        '_nilike' => 'NOT ILIKE',
        '_similar' => 'SIMILAR TO',
        '_nsimilar' => 'NOT SIMILAR TO',
    ];

    public static function getEloquentInputTypesFor(string $prefix, array $fields): array
    {
        return [
            new InputObjectType([
                'name' => $prefix . '_where',
                'fields' => collect($fields)->mapWithKeys(
                    fn($type, $field) => [Str::camel($field) => ['type' => self::getComparisonExpressionTypeNameFor($type)]],
                )->all(),
            ]),

            new InputObjectType([
                'name' => $prefix . '_order_by',
                'fields' =>
                    collect($fields)->mapWithKeys(
                        fn($type, $field) => [Str::camel($field) => ['type' => self::getOrderByDirectionTypeName($type)]],
                    )->all(),
            ]),
            new InputObjectType([
                'name' => $prefix . '_input',
                'fields' =>
                    collect($fields)->mapWithKeys(
                        fn($type, $field) => [Str::camel($field) => ['type' => $type]],
                    )->all(),
            ]),
        ];
    }

    public static function getComparisonExpressionTypeNameFor(Type $type): string
    {
        return 'where_comparison_' . Str::lower($type->name);
    }

    public static function getOrderByDirectionTypeName(): string
    {
        return 'order_by_direction';
    }

    public function __invoke(): array
    {
        return collect(Type::getStandardTypes())
            ->map(fn(Type $type) => static::getComparisonExpressionTypeFor($type))
            ->merge([
                new EnumType([
                    'name' => self::getOrderByDirectionTypeName(),
                    'values' => ['asc', 'desc'],
                ]),
            ])->all();
    }

    private static function getComparisonExpressionTypeFor(Type $type): InputObjectType
    {
        return new InputObjectType([
            'name' => static::getComparisonExpressionTypeNameFor($type),
            'fields' => array_map(
                fn(string $operator) => in_array($operator, self::WHERE_OPERATORS_IN) ? Type::listOf($type) : $type,
                static::getOperatorsFor($type)
            ),
        ]);
    }

    public static function getOperatorsFor(Type $type): array
    {
        if ($type === Type::string()) {
            return array_merge(self::WHERE_OPERATORS, self::WHERE_OPERATORS_STRING);
        }

        return self::WHERE_OPERATORS;
    }
}
