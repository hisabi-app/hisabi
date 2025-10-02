# Hisabi AI Rebuild - Architecture Design

## Overview
Rebuild of Hisabi's AI functionality using modern tools: Prism PHP for LLM integration and Laravel MCP for exposing financial data to AI clients.

## Technology Stack

### Backend
- **Prism PHP**: Modern Laravel package for LLM integration with support for structured outputs, streaming, and multiple providers
- **Laravel MCP**: Model Context Protocol server implementation for exposing financial tools to AI clients
- **Laravel Sanctum**: API authentication for MCP connections (already installed)
- **GraphQL (Lighthouse)**: Existing API layer, extended for AI features

### Frontend
- **React + Inertia.js**: Existing stack
- **shadcn/ui AI components**: Already integrated for chat UI
- **Chart libraries**: For rendering financial visualizations in AI responses

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Frontend (React)                         │
├──────────────────────┬──────────────────────────────────────────┤
│   Hisabi AI Chat     │         MCP Connection Tab               │
│                      │                                           │
│  - Text responses    │  - Connect with personal token           │
│  - Charts            │  - Display MCP server status              │
│  - Custom widgets    │  - Browse available tools                 │
│  - Suggestions       │  - Test tools manually                    │
└──────────────────────┴──────────────────────────────────────────┘
           ↓                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                      GraphQL API Layer                           │
│  - hisabiGPT query (enhanced with streaming)                    │
│  - All existing financial queries                                │
└─────────────────────────────────────────────────────────────────┘
           ↓                              ↓
┌────────────────────────┐    ┌──────────────────────────────────┐
│   Prism AI Service     │    │      Laravel MCP Server          │
│                        │    │                                  │
│ - Conversation memory  │    │ Tools:                           │
│ - Structured outputs   │    │  - get_transactions              │
│ - Tool calling         │    │  - get_financial_summary         │
│ - Chart generation     │    │  - get_category_breakdown        │
│ - Streaming support    │    │  - get_spending_trends           │
└────────────────────────┘    │  - get_budget_status             │
                              │  - search_brands                 │
                              │  - calculate_savings_potential   │
                              │                                  │
                              │ Resources:                       │
                              │  - transactions_feed             │
                              │  - monthly_reports               │
                              │                                  │
                              │ Prompts:                         │
                              │  - financial_analysis            │
                              │  - budget_recommendations        │
                              └──────────────────────────────────┘
```

## Components Breakdown

### 1. AI Chat Service (Prism-based)

**Location**: `app/Services/AI/HisabiAIService.php`

**Responsibilities**:
- Manage conversation context with Prism
- Generate structured responses (text, charts, components)
- Handle tool calling for financial data
- Stream responses to frontend
- Parse and format chart data

**Key Features**:
```php
class HisabiAIService
{
    public function chat(array $messages): StreamedResponse
    public function generateFinancialInsight(string $query): StructuredOutput
    public function createChart(string $type, array $data): ChartOutput
    public function analyzeSpendings(string $range): Analysis
}
```

### 2. MCP Server

**Location**: `app/Mcp/Servers/HisabiMcpServer.php`

**Tools** (expose existing GraphQL queries as MCP tools):
- `get_transactions`: Fetch user transactions with filters
- `get_financial_summary`: Get income, expenses, savings, net worth
- `get_category_breakdown`: Expenses/income per category
- `get_spending_trends`: Time-series data for spending patterns
- `get_budget_status`: Current budget usage and recommendations
- `search_brands`: Find specific brands and their spending
- `calculate_savings_potential`: AI-driven savings suggestions
- `get_transaction_statistics`: Averages, highest, lowest transactions

**Resources**:
- `transactions://recent`: Stream of recent transactions
- `reports://monthly/{month}`: Monthly financial reports

**Prompts**:
- `analyze_finances`: Template for comprehensive financial analysis
- `budget_recommendation`: Template for budget suggestions

**Authentication**:
- Uses Sanctum bearer tokens
- Token-based access to user's financial data
- Per-tool authorization

### 3. Enhanced GraphQL Schema

**New/Updated Types**:
```graphql
type AIMessage {
    role: String!
    content: String!
    components: [AIComponent]
    charts: [AIChart]
}

type AIComponent {
    type: String!  # "budget_alert", "savings_card", "category_breakdown"
    data: Json
}

type AIChart {
    type: String!  # "line", "bar", "pie", "area"
    title: String!
    data: Json
    config: Json
}

type AIResponse {
    message: AIMessage!
    suggestions: [String!]
}

extend type Query {
    hisabiAIChat(messages: [Message!]!): AIResponse!
    streamHisabiAIChat(messages: [Message!]!): AIResponse! @stream
    
    # MCP related
    mcpServerInfo: McpServerInfo!
    mcpAvailableTools: [McpTool!]!
    testMcpTool(name: String!, arguments: Json!): Json!
}

type McpServerInfo {
    name: String!
    version: String!
    status: String!
    toolsCount: Int!
    resourcesCount: Int!
}

type McpTool {
    name: String!
    description: String!
    inputSchema: Json!
}
```

### 4. Frontend Components

#### Enhanced AI Chat (`resources/js/components/Global/HisabiAIChat.tsx`)
- Stream responses in real-time
- Render markdown with syntax highlighting
- Display interactive charts (using Chart.js or Recharts)
- Show custom financial widgets
- Handle tool calling indicators

**New Components**:
- `AIChartRenderer.tsx`: Renders different chart types
- `AIFinancialWidget.tsx`: Custom widgets (budget alerts, savings cards)
- `AISuggestions.tsx`: Smart suggestions based on context
- `AIStreamingIndicator.tsx`: Visual indicator for streaming

#### MCP Connection Tab (`resources/js/components/Global/McpConnection.tsx`)
- Token input and validation
- Server connection status
- List of available tools
- Tool testing interface
- Connection management

**Structure**:
```tsx
<McpConnection>
  <McpTokenInput onConnect={handleConnect} />
  <McpServerStatus status={serverStatus} />
  <McpToolsList tools={availableTools} />
  <McpToolTester onTest={handleToolTest} />
</McpConnection>
```

## Data Flow

### AI Chat Flow
1. User sends message in chat
2. Frontend calls GraphQL `hisabiAIChat` mutation
3. Backend `HisabiAIService` processes with Prism:
   - Adds financial context from user's data
   - Optionally calls MCP tools for fresh data
   - Generates structured response with charts/components
4. Response streamed back to frontend
5. Frontend renders text, charts, and widgets progressively

### MCP Connection Flow
1. User generates personal token in settings (Sanctum)
2. User enters token in MCP Connection tab
3. Frontend validates token via GraphQL
4. MCP client (e.g., Claude Desktop) connects to `/mcp/hisabi` endpoint
5. MCP server authenticates via Sanctum token
6. AI assistant can call tools to fetch user's financial data
7. Results used to enhance AI responses

## Security Considerations

1. **Authentication**: 
   - Sanctum tokens with appropriate scopes
   - Token expiration and refresh mechanisms
   - Per-tool permission checks

2. **Rate Limiting**:
   - Limit AI requests per user
   - Throttle MCP tool calls
   - Prevent abuse of expensive operations

3. **Data Privacy**:
   - Never expose raw transaction details unnecessarily
   - Aggregate data when possible
   - Audit log for AI and MCP access

4. **Input Validation**:
   - Sanitize all user inputs
   - Validate date ranges and parameters
   - Prevent injection attacks

## File Structure

```
app/
├── Services/
│   └── AI/
│       ├── HisabiAIService.php
│       ├── ChartGenerator.php
│       ├── FinancialAnalyzer.php
│       └── ConversationMemory.php
├── Mcp/
│   ├── Servers/
│   │   └── HisabiMcpServer.php
│   ├── Tools/
│   │   ├── GetTransactionsTool.php
│   │   ├── GetFinancialSummaryTool.php
│   │   ├── GetCategoryBreakdownTool.php
│   │   ├── GetSpendingTrendsTool.php
│   │   ├── GetBudgetStatusTool.php
│   │   ├── SearchBrandsTool.php
│   │   └── CalculateSavingsPotentialTool.php
│   ├── Resources/
│   │   ├── TransactionsFeedResource.php
│   │   └── MonthlyReportsResource.php
│   └── Prompts/
│       ├── AnalyzeFinancesPrompt.php
│       └── BudgetRecommendationPrompt.php
├── GraphQL/
│   ├── Queries/
│   │   ├── HisabiAIChat.php
│   │   ├── McpServerInfo.php
│   │   ├── McpAvailableTools.php
│   │   └── TestMcpTool.php
│   └── Types/
│       ├── AIMessageType.php
│       ├── AIComponentType.php
│       └── AIChartType.php

resources/js/
├── components/
│   └── Global/
│       ├── HisabiAIChat.tsx (enhanced)
│       ├── AIChartRenderer.tsx
│       ├── AIFinancialWidget.tsx
│       ├── AISuggestions.tsx
│       ├── AIStreamingIndicator.tsx
│       ├── McpConnection.tsx
│       ├── McpTokenInput.tsx
│       ├── McpServerStatus.tsx
│       ├── McpToolsList.tsx
│       └── McpToolTester.tsx

routes/
└── ai.php (MCP routes)

tests/
├── Feature/
│   ├── AI/
│   │   └── HisabiAIServiceTest.php
│   └── Mcp/
│       ├── HisabiMcpServerTest.php
│       └── ToolsTest.php
```

## Implementation Phases

### Phase 1: Backend Setup (Prism & MCP)
1. Install Prism PHP
2. Install Laravel MCP
3. Create `HisabiAIService` with basic Prism integration
4. Create MCP server structure
5. Implement core MCP tools

### Phase 2: AI Service Enhancement
1. Add structured output support
2. Implement chart generation
3. Add conversation memory
4. Create financial analyzer
5. Implement tool calling

### Phase 3: Frontend Enhancement
1. Enhance AI chat component
2. Create chart renderer
3. Build custom financial widgets
4. Add streaming support
5. Implement suggestions

### Phase 4: MCP Integration
1. Create MCP connection tab
2. Build token management
3. Implement tool testing UI
4. Add server status monitoring
5. Create documentation

### Phase 5: Testing & Polish
1. Unit tests for all services
2. Integration tests for MCP
3. E2E tests for AI chat
4. Performance optimization
5. Security audit

## Environment Configuration

```env
# Prism Configuration
PRISM_PROVIDER=openai
PRISM_API_KEY=your-openai-key
PRISM_MODEL=gpt-4o

# Alternative: Anthropic
# PRISM_PROVIDER=anthropic
# PRISM_API_KEY=your-anthropic-key
# PRISM_MODEL=claude-3-5-sonnet-20241022

# MCP Configuration
MCP_SERVER_NAME="Hisabi Finance MCP"
MCP_SERVER_VERSION=1.0.0
MCP_ENABLE_AUTH=true
MCP_RATE_LIMIT=60

# AI Configuration
AI_MAX_CONTEXT_MESSAGES=20
AI_ENABLE_STREAMING=true
AI_ENABLE_CHARTS=true
```

## API Examples

### AI Chat Request
```graphql
mutation {
  hisabiAIChat(messages: [
    {role: "user", content: "Show me my spending trends for the last 3 months"}
  ]) {
    message {
      role
      content
      charts {
        type
        title
        data
      }
    }
    suggestions
  }
}
```

### MCP Tool Call (from AI client)
```json
{
  "method": "tools/call",
  "params": {
    "name": "get_spending_trends",
    "arguments": {
      "range": "last-3-months",
      "groupBy": "category"
    }
  }
}
```

## Benefits of This Architecture

1. **Modularity**: Clear separation between AI service, MCP server, and existing codebase
2. **Scalability**: Easy to add new tools, prompts, and resources
3. **Flexibility**: Support multiple LLM providers via Prism
4. **Interoperability**: MCP allows external AI clients to access Hisabi data
5. **Rich UX**: Structured outputs enable charts and custom components
6. **Maintainability**: Well-organized code with clear responsibilities
7. **Testability**: Each component can be tested independently

## Future Enhancements

1. Voice interaction with AI
2. Predictive analytics for spending
3. Automated budget recommendations
4. Integration with banking APIs for real-time data
5. Multi-language support
6. Collaborative financial planning with shared MCP access
7. Mobile app with same AI capabilities

