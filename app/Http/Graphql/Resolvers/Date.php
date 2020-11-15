<?php


namespace App\Http\Graphql\Resolvers;


use Carbon\Carbon;

class Date
{
    public function serialize(Carbon $date): string
    {
        return $date->toDateString();
    }

    public function parse(string $value): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
    }
}
