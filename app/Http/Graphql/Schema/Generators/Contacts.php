<?php


namespace App\Http\Graphql\Schema\Generators;


use GraphQL\Type\Definition\Type;

class Contacts
{
    public function __invoke(): array
    {
        return InputUtils::getEloquentInputTypesFor('contacts', [
            'first_name' => Type::string(),
            'last_name' => Type::string(),
            'email' => Type::string(),
            'phone' => Type::string(),
            'address' => Type::string(),
            'city' => Type::string(),
            'region' => Type::string(),
            'country' => Type::string(),
            'postal_code' => Type::string(),
        ]);
    }
}
