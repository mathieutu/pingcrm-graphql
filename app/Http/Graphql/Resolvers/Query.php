<?php

namespace App\Http\Graphql\Resolvers;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;

class Query
{
    use GetEntitiesFromQuery;

    public function now()
    {
        return now();
    }

    public function users(...$params)
    {
        return $this->listEntities(User::query(), ...$params);
    }

    public function contacts(...$params)
    {
        return $this->listEntities(Contact::query(), ...$params);
    }

    public function organizations(...$params)
    {
        return $this->listEntities(Organization::query(), ...$params);
    }
}
