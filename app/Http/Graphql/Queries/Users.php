<?php

namespace App\Http\Graphql\Queries;

use App\Http\Graphql\QueryInterface;
use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;

class Users implements QueryInterface
{
    public function __invoke($root, array $args, array $context, ResolveInfo $info)
    {
        return User::all();
    }
}
