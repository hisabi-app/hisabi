<?php

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgDirectiveForArray;

final class SearchDirective extends BaseDirective implements ArgDirectiveForArray, ArgBuilderDirective
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Perform search operation.
"""
directive @search() on ARGUMENT_DEFINITION
GRAPHQL;
    }

    /**
     * Add additional constraints to the builder based on the given argument value.
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function handleBuilder($builder, $value)
    {
        if($builder->getModel() instanceof \App\Models\Transaction) {
            return $builder->where('amount', 'LIKE', "%$value%")
                ->orWhere('note', 'LIKE', "%$value%")
                ->orWhereHas('brand', function($builder) use($value) {
                    return $builder->where('name', 'LIKE', "%$value%");
                });
        }

        return $builder;
    }
}
