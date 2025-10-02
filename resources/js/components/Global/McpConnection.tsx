import { useState, useEffect } from 'react';
import { XIcon, CheckCircleIcon, XCircleIcon, InformationCircleIcon } from '@heroicons/react/solid';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { customQuery } from '../../Api';

interface McpConnectionProps {
  onClose: () => void;
}

export default function McpConnection({ onClose }: McpConnectionProps) {
  const [serverInfo, setServerInfo] = useState<any>(null);
  const [tools, setTools] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [copied, setCopied] = useState(false);

  useEffect(() => {
    loadMcpInfo();
  }, []);

  const loadMcpInfo = async () => {
    setLoading(true);
    try {
      const [serverResponse, toolsResponse] = await Promise.all([
        customQuery('mcpServerInfo { name version endpoint toolsCount }'),
        customQuery('mcpAvailableTools { name description inputSchema }')
      ]);

      setServerInfo(serverResponse.data.mcpServerInfo);
      setTools(toolsResponse.data.mcpAvailableTools || []);
    } catch (error) {
      console.error('Failed to load MCP info:', error);
    } finally {
      setLoading(false);
    }
  };

  const copyEndpoint = () => {
    if (serverInfo?.endpoint) {
      navigator.clipboard.writeText(serverInfo.endpoint);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };

  const generateToken = () => {
    // Link to Laravel Sanctum token generation page
    window.open('/user/tokens', '_blank');
  };

  return (
    <div className="h-full w-full flex flex-col overflow-hidden">
      {/* Header */}
      <div className="border-b p-4">
        <div className='flex justify-between items-center'>
          <div>
            <h2 className='text-lg font-semibold'>MCP Connection</h2>
          </div>
          <button
            onClick={onClose}
            className="text-muted-foreground hover:text-foreground transition-colors"
          >
            <XIcon className='w-5 h-5' />
          </button>
        </div>
      </div>

      {/* Content */}
      <div className="flex-1 overflow-y-auto p-4 space-y-4">
        {loading ? (
          <div className="flex items-center justify-center h-full">
            <p className="text-sm text-muted-foreground">Loading MCP server info...</p>
          </div>
        ) : (
          <>
            {/* Server Info */}
            <Card>
              <CardHeader>
                <CardTitle className="text-base flex items-center gap-2">
                  <CheckCircleIcon className="w-5 h-5 text-green-500" />
                  Server Status
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div>
                  <p className="text-sm font-medium">Name</p>
                  <p className="text-sm text-muted-foreground">{serverInfo?.name}</p>
                </div>
                <div>
                  <p className="text-sm font-medium">Version</p>
                  <p className="text-sm text-muted-foreground">{serverInfo?.version}</p>
                </div>
                <div>
                  <p className="text-sm font-medium">Available Tools</p>
                  <p className="text-sm text-muted-foreground">{serverInfo?.toolsCount} tools</p>
                </div>
                <div>
                  <p className="text-sm font-medium mb-1">Endpoint</p>
                  <div className="flex gap-2">
                    <Input
                      value={serverInfo?.endpoint || ''}
                      readOnly
                      className="text-xs font-mono"
                    />
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={copyEndpoint}
                    >
                      {copied ? 'Copied!' : 'Copy'}
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Authentication */}
            <Card>
              <CardHeader>
                <CardTitle className="text-base">Authentication</CardTitle>
                <CardDescription>
                  Generate a personal access token to connect AI clients
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-3">
                <Button onClick={generateToken} className="w-full">
                  Generate Access Token
                </Button>
                <p className="text-xs text-muted-foreground">
                  Use this token as a Bearer token in the Authorization header when connecting to the MCP server.
                </p>
              </CardContent>
            </Card>

            {/* How to Connect */}
            <Card>
              <CardHeader>
                <CardTitle className="text-base flex items-center gap-2">
                  <InformationCircleIcon className="w-5 h-5 text-blue-500" />
                  How to Connect
                </CardTitle>
              </CardHeader>
              <CardContent>
                <ol className="text-sm space-y-2 list-decimal list-inside">
                  <li>Generate a personal access token above</li>
                  <li>Copy the server endpoint</li>
                  <li>In your AI client (e.g., Claude Desktop), add this MCP server:</li>
                </ol>
                <pre className="mt-3 text-xs bg-secondary p-3 rounded overflow-auto">
{`{
  "mcpServers": {
    "hisabi": {
      "url": "${serverInfo?.endpoint || 'YOUR_ENDPOINT'}",
      "headers": {
        "Authorization": "Bearer YOUR_TOKEN_HERE"
      }
    }
  }
}`}
                </pre>
              </CardContent>
            </Card>

            {/* Available Tools */}
            <Card>
              <CardHeader>
                <CardTitle className="text-base">Available Tools</CardTitle>
                <CardDescription>
                  {tools.length} tools available for AI clients
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-2">
                {tools.map((tool, index) => (
                  <Collapsible key={index}>
                    <CollapsibleTrigger className="w-full">
                      <div className="flex items-center justify-between p-3 rounded border hover:bg-secondary transition-colors">
                        <div className="text-left">
                          <p className="text-sm font-medium">{tool.name}</p>
                          <p className="text-xs text-muted-foreground line-clamp-1">
                            {tool.description}
                          </p>
                        </div>
                        <Badge variant="secondary">Tool</Badge>
                      </div>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                      <div className="p-3 mt-1 border rounded bg-secondary/50">
                        <p className="text-xs font-medium mb-2">Description:</p>
                        <p className="text-xs text-muted-foreground mb-3">{tool.description}</p>
                        
                        <p className="text-xs font-medium mb-2">Input Schema:</p>
                        <pre className="text-xs bg-background p-2 rounded overflow-auto max-h-40">
                          {JSON.stringify(tool.inputSchema, null, 2)}
                        </pre>
                      </div>
                    </CollapsibleContent>
                  </Collapsible>
                ))}
              </CardContent>
            </Card>

            {/* Usage Examples */}
            <Card>
              <CardHeader>
                <CardTitle className="text-base">Usage Examples</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div>
                  <p className="text-sm font-medium mb-1">Example 1: Get Financial Summary</p>
                  <code className="text-xs bg-secondary p-2 rounded block">
                    "Show me my financial summary for the current month"
                  </code>
                </div>
                <div>
                  <p className="text-sm font-medium mb-1">Example 2: Analyze Spending</p>
                  <code className="text-xs bg-secondary p-2 rounded block">
                    "Get my spending breakdown by category for the last 3 months"
                  </code>
                </div>
                <div>
                  <p className="text-sm font-medium mb-1">Example 3: Check Budget</p>
                  <code className="text-xs bg-secondary p-2 rounded block">
                    "What's my budget status?"
                  </code>
                </div>
              </CardContent>
            </Card>
          </>
        )}
      </div>
    </div>
  );
}

