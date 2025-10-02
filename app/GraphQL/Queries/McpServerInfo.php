<?php

namespace App\GraphQL\Queries;

use App\Mcp\Servers\HisabiMcpServer;
use Illuminate\Support\Facades\URL;

class McpServerInfo
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args): array
    {
        $server = new HisabiMcpServer();
        
        return [
            'name' => 'Hisabi Finance MCP Server',
            'version' => '1.0.0',
            'endpoint' => URL::to('/mcp/hisabi'),
            'toolsCount' => count($server->tools ?? []),
        ];
    }
}

