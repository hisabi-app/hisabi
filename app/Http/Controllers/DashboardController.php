<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = config('finance.reports');
        
        $graphqlQueries = array_map(function($metric) {
            return $metric['graphql_query'];
        }, $metrics);

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'graphqlQueries' => implode("\n", $graphqlQueries)
        ]);
    }
}
