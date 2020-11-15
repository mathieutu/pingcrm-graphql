<?php

namespace App\Http\Graphql\Resolvers;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class Mutation
{
    use GetEntitiesFromQuery;

    public function addOrganization($root, $args)
    {
        return User::first()->account->organizations()->create(
            Validator::make($args['object'], [
                'name' => ['required', 'max:100'],
                'email' => ['nullable', 'max:50', 'email'],
                'phone' => ['nullable', 'max:50'],
                'address' => ['nullable', 'max:150'],
                'city' => ['nullable', 'max:50'],
                'region' => ['nullable', 'max:50'],
                'country' => ['nullable', 'max:2'],
                'postal_code' => ['nullable', 'max:25'],
            ])->validate()
        );
    }
}
