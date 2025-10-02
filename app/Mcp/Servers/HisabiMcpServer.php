<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use App\Mcp\Tools\GetTransactionsTool;
use App\Mcp\Tools\GetFinancialSummaryTool;
use App\Mcp\Tools\GetCategoryBreakdownTool;
use App\Mcp\Tools\GetSpendingTrendsTool;
use App\Mcp\Tools\GetBudgetStatusTool;
use App\Mcp\Tools\SearchBrandsTool;

class HisabiMcpServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Hisabi Finance MCP Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<MARKDOWN
        # Hisabi Finance MCP Server

        Welcome to the Hisabi Finance MCP Server! This server provides comprehensive access to personal finance data through a set of powerful tools.

        ## Available Tools

        ### Financial Overview
        - **get_financial_summary**: Get comprehensive financial metrics including income, expenses, savings, investments, net worth, and cash flow
        - **get_budget_status**: Check current budget status with spending analysis and recommendations

        ### Transaction Analysis
        - **get_transactions**: Fetch detailed transaction history with flexible filtering options
        - **search_brands**: Search for specific brands and analyze spending patterns
        - **get_category_breakdown**: Analyze spending or income by categories with detailed breakdowns

        ### Trends & Insights
        - **get_spending_trends**: View time-series data of financial trends grouped by day, week, or month

        ## Usage Guidelines

        1. **Always specify time periods**: Most tools accept period parameters to focus analysis on specific timeframes
        2. **Combine tools for insights**: Use multiple tools together to build comprehensive financial analyses
        3. **Respect user privacy**: All data is user-specific and should be handled securely
        4. **Provide actionable advice**: Use the data to help users make informed financial decisions

        ## Example Workflows

        **Monthly Financial Review:**
        1. Use `get_financial_summary` with period "current-month"
        2. Follow with `get_category_breakdown` for detailed expense analysis
        3. Check `get_budget_status` to see if on track

        **Spending Pattern Analysis:**
        1. Use `get_spending_trends` to identify patterns over time
        2. Drill down with `get_category_breakdown` for specific categories
        3. Use `search_brands` to find specific merchants

        **Budget Planning:**
        1. Start with `get_financial_summary` for "last-3-months"
        2. Analyze trends with `get_spending_trends`
        3. Review `get_budget_status` for current budget performance

        Remember: All amounts are in the user's configured currency (typically AED).
        MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        GetTransactionsTool::class,
        GetFinancialSummaryTool::class,
        GetCategoryBreakdownTool::class,
        GetSpendingTrendsTool::class,
        GetBudgetStatusTool::class,
        SearchBrandsTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
