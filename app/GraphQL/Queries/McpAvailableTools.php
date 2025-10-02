<?php

namespace App\GraphQL\Queries;

use App\Mcp\Tools\GetTransactionsTool;
use App\Mcp\Tools\GetFinancialSummaryTool;
use App\Mcp\Tools\GetCategoryBreakdownTool;
use App\Mcp\Tools\GetSpendingTrendsTool;
use App\Mcp\Tools\GetBudgetStatusTool;
use App\Mcp\Tools\SearchBrandsTool;

class McpAvailableTools
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args): array
    {
        $toolClasses = [
            GetTransactionsTool::class,
            GetFinancialSummaryTool::class,
            GetCategoryBreakdownTool::class,
            GetSpendingTrendsTool::class,
            GetBudgetStatusTool::class,
            SearchBrandsTool::class,
        ];
        
        $tools = [];
        
        foreach ($toolClasses as $toolClass) {
            $tool = new $toolClass();
            
            // Access protected properties via reflection
            $reflection = new \ReflectionClass($tool);
            
            $nameProperty = $reflection->getProperty('name');
            $nameProperty->setAccessible(true);
            $name = $nameProperty->getValue($tool);
            
            $descriptionProperty = $reflection->getProperty('description');
            $descriptionProperty->setAccessible(true);
            $description = $descriptionProperty->getValue($tool);
            
            // Get input schema
            $inputSchemaMethod = $reflection->getMethod('inputSchema');
            $inputSchemaMethod->setAccessible(true);
            $inputSchema = $inputSchemaMethod->invoke($tool);
            
            $tools[] = [
                'name' => $name,
                'description' => $description,
                'inputSchema' => $inputSchema,
            ];
        }
        
        return $tools;
    }
}

