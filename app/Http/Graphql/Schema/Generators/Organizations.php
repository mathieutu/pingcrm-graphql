<?php


namespace App\Http\Graphql\Schema\Generators;


use GraphQL\Type\Definition\Type;

class Organizations
{
    public function __invoke(): array
    {
        return InputUtils::getEloquentInputTypesFor('organizations', [
            'name' => Type::string(),
            'email' => Type::string(),
            'phone' => Type::string(),
            'address' => Type::string(),
            'city' => Type::string(),
            'region' => Type::string(),
            'country' => Type::string(),
            'postal_code' => Type::string(),
            // 'created_at',
            // 'updated_at',
            // 'deleted_at',
        ]);
    }
}
