<?php

namespace App\Http\Controllers;

use App\Http\Graphql\GraphQL;
use Butler\Graphql\Concerns\HandlesGraphqlRequests;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;
use Illuminate\Support\Str;

class GraphqlController extends Controller
{
    use HandlesGraphqlRequests;

    private GraphQL $graphQL;

    public function __construct(GraphQL $graphQL)
    {
        $this->graphQL = $graphQL;
    }

    public function schema(): Schema
    {
        return $this->graphQL->getSchema();
    }

    public function resolveFieldMethodName(ResolveInfo $info): string
    {
        return Str::camel($info->fieldName);
    }

    protected function resolveClassName(ResolveInfo $info): string
    {
        return $this->graphQL->resolveClassName($info->parentType->name);
    }
}
