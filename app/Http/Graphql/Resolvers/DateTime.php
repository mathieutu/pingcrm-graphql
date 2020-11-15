<?php


namespace App\Http\Graphql\Resolvers;


use Carbon\Carbon;

class DateTime
{
    public function serialize(Carbon $date): string
    {
        return $date->toIso8601String();
    }

    public function parse(string $value): Carbon
    {
        return Carbon::createFromFormat(Carbon::ISO8601, $value)->startOfDay();
    }
}
