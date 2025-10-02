<?php

use Laravel\Mcp\Facades\Mcp;
use App\Mcp\Servers\HisabiMcpServer;

// Register the Hisabi MCP Server with authentication
Mcp::web('/mcp/hisabi', HisabiMcpServer::class)
    ->middleware(['auth:sanctum']);
