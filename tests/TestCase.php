<?php

namespace Tests;

use App\Models\User;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use MakesGraphQLRequests;

    protected function graphQL(
        string $query,
        array $variables = [],
        array $extraParams = [],
        array $headers = []
    ) {
        $params = ['query' => $query];

        if ([] !== $variables) {
            $params += ['variables' => $variables];
        }

        $params += $extraParams;

        $user = User::factory()->create();

        return $this->actingAs($user)->postGraphQL($params, $headers);
    }
}
