<?php


namespace App\Http\Graphql\Resolvers;


use App\Http\Graphql\Schema\Generators\InputUtils;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait GetEntitiesFromQuery
{
    private function listEntities(Builder $query, $root, array $args, array $context, ResolveInfo $info)
    {
        $this->loadRelations($query, $info);
        $this->addWhere($query, $args);
        $this->addOrderBy($query, $args);

        return $query->get();
    }

    private function loadRelations(Builder $query, ResolveInfo $info)
    {
        $fields = $info->getFieldSelection(10);

        $relations = collect(Arr::dot($fields))
            ->keys()
            ->filter(fn(string $field) => Str::contains($field, '.'))
            ->map(fn(string $field) => Str::beforeLast($field, '.'))
            ->unique()
            ->toArray();

        // Avec select auto des colonnes (mais pb avec les getters)
        // $relations = collect(Arr::dot($fields))
        //     ->keys()
        //     ->filter(fn(string $field) => Str::contains($field, '.'))
        //     ->mapToGroups(fn(string $field) => [Str::beforeLast($field, '.') => Str::afterLast($field, '.')])
        //     ->map(fn(Collection $selectFields) => $selectFields->join(','))
        //     ->map(fn(string $select, string $relation) => "$relation:$select")
        //     ->values()
        //     ->toArray()
        // ;

        $query->with($relations);
    }

    private function addWhere(Builder $query, array $args)
    {
        if (!isset($args['where'])) {
            return;
        }

        collect($args['where'])
            ->flatMap(fn($cond, $attr) => collect($cond)
                ->map(fn($value, $op) => [Str::snake($attr), $op, $value])
                ->values())
            ->eachSpread(function ($attr, $op, $value) use ($query) {
                if ($op === InputUtils::WHERE_OPERATORS['_is_null']) {
                    return $query->whereNull($attr, 'and', !$value);
                }

                if ($op === InputUtils::WHERE_OPERATORS['_in']) {
                    return $query->whereIn($attr, $value);
                }

                if ($op === InputUtils::WHERE_OPERATORS['_nin']) {
                    return $query->whereNotIn($attr, $value);
                }

                return $query->where($attr, InputUtils::WHERE_OPERATORS[$op], $value);
            });
    }

    private function addOrderBy(Builder $query, array $args)
    {
        if (!isset($args['orderBy'])) {
            return;
        }

        collect($args['orderBy'])
            ->flatMap(fn($orderObj) => collect($orderObj)->map(fn($direction, $attr) => [$attr, $direction])->values())
            ->eachSpread(function ($attr, $direction) use ($query) {
                $query->orderBy(Str::snake($attr), $direction);
            });
    }
}
