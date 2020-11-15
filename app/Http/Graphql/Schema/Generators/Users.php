<?php


namespace App\Http\Graphql\Schema\Generators;


use GraphQL\Type\Definition\Type;

class Users
{
    public function __invoke(): array
    {
        return InputUtils::getEloquentInputTypesFor('users', [
            'firstName' => Type::string(),
            'lastName' => Type::string(),
            'email' => Type::string(),
        ]);
    }
}
