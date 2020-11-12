<?php

namespace App\Http\Graphql;

use GraphQL\Type\Definition\ResolveInfo;

interface QueryInterface
{
    public function __invoke($root, array $args, array $context, ResolveInfo $info);
}
