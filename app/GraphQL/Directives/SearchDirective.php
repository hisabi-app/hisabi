<?php

namespace App\GraphQL\Directives;

use App\Contracts\Searchable;
use Illuminate\Database\Eloquent\Builder;
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
directive @search(within: String) on ARGUMENT_DEFINITION
GRAPHQL;
    }

    /**
     * Add additional constraints to the builder based on the given argument value.
     *
     * @param  Builder $builder
     * @param  mixed  $value
     * @return Builder
     */
    public function handleBuilder($builder, $value): Builder
    {
        if($builder->getModel() instanceof Searchable) {
            return $builder->getModel()::search($value);
        }

        return $builder;
    }
}
