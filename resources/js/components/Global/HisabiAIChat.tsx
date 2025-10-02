import { useEffect, useState } from 'react'
import { customQuery } from '../../Api';
import { XIcon } from '@heroicons/react/solid';
import { Message, MessageContent } from '@/components/ui/shadcn-io/ai/message';
import { Response } from '@/components/ui/shadcn-io/ai/response';
import { Conversation, ConversationContent } from '@/components/ui/shadcn-io/ai/conversation';
import { PromptInput, PromptInputTextarea, PromptInputToolbar, PromptInputSubmit } from '@/components/ui/shadcn-io/ai/prompt-input';
import { Suggestions, Suggestion } from '@/components/ui/shadcn-io/ai/suggestion';
import { Loader } from '@/components/ui/shadcn-io/ai/loader';
import AIChartRenderer from './AIChartRenderer';
import AIFinancialWidget from './AIFinancialWidget';

interface HisabiAIChatProps {
  onClose: () => void;
}

interface ChatMessage {
  id: number;
  content: string;
  role: 'user' | 'assistant';
  charts?: any[];
  components?: any[];
  suggestions?: string[];
}

export default function HisabiAIChat({ onClose }: HisabiAIChatProps) {
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const [chatHistory, setChatHistory] = useState<ChatMessage[]>([]);

  const handleChange = (event: React.ChangeEvent<HTMLTextAreaElement>) => {
    setMessage(event.target.value);
  };

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (message.trim() === '' || loading) return;

    const newMessage: ChatMessage = {
      id: chatHistory.length + 1,
      content: message,
      role: 'user',
    };

    setChatHistory([...chatHistory, newMessage]);
    setMessage('');
  };

  useEffect(() => {
    if(chatHistory[chatHistory.length-1]?.role === 'user') {
      submit(chatHistory);
    }
  }, [chatHistory]);


  const submit = async (newChat: ChatMessage[]) => {
    setLoading(true);

    try {
      // Format messages for the new API
      let messages = newChat.map((msg) => 
        `{role: "${msg.role}" content: """${msg.content.replace(/"/g, '\\"')}"""}`
      );
      let graphql_query = `hisabiAIChat(messages: [${messages}]) {
        role
        content
        charts {
          type
          title
          data
          config
        }
        components {
          type
          data
        }
        suggestions
      }`;

      let { data } = await customQuery(graphql_query);
      let aiResponse = data['hisabiAIChat'];

      const assistantMessage: ChatMessage = {
        id: chatHistory.length + 1,
        role: 'assistant',
        content: aiResponse.content,
        charts: aiResponse.charts || [],
        components: aiResponse.components || [],
        suggestions: aiResponse.suggestions || []
      };

      setChatHistory([...chatHistory, assistantMessage]);
    } catch (error) {
      console.error('AI Chat Error:', error);
      const errorMessage: ChatMessage = {
        id: chatHistory.length + 1,
        role: 'assistant',
        content: 'I apologize, but I encountered an error. Please try again.',
        charts: [],
        components: [],
        suggestions: []
      };
      setChatHistory([...chatHistory, errorMessage]);
    } finally {
      setLoading(false);
    }
  };

  const handleSuggestionClick = (suggestionText: string) => {
    setMessage(suggestionText);
  };

  // Get suggestions from the last assistant message
  const lastAssistantMessage = [...chatHistory].reverse().find(msg => msg.role === 'assistant');
  const currentSuggestions = lastAssistantMessage?.suggestions || [
    'Show me my spending summary for this month',
    'What are my top expenses?',
    'How much can I save this month?'
  ];

  return (
    <div className="h-full w-full flex flex-col overflow-hidden">
      {/* Header */}
      <div className="border-b p-4">
        <div className='flex justify-between items-center'>
          <div>
            <h2 className='text-lg font-semibold'>Hisabi AI</h2>
          </div>
          <button
            onClick={onClose}
            className="text-muted-foreground hover:text-foreground transition-colors"
          >
            <XIcon className='w-5 h-5' />
          </button>
        </div>
      </div>
      
      {/* Conversation Area */}
      <Conversation className="flex-1 border-r">
        <ConversationContent>
          {chatHistory.length === 0 && (
            <div className="flex items-center justify-center h-full">
              <div className="text-center space-y-2">
                <p className="text-muted-foreground text-sm">Start a conversation...</p>
                <p className="text-xs text-muted-foreground">Ask me anything about your finances!</p>
              </div>
            </div>
          )}
          
          {chatHistory.map((msg) => (
            <Message key={msg.id} from={msg.role}>
              <MessageContent>
                <Response>{msg.content}</Response>
                
                {/* Render charts if present */}
                {msg.charts && msg.charts.length > 0 && (
                  <div className="mt-4 space-y-4">
                    {msg.charts.map((chart, index) => (
                      <AIChartRenderer key={index} chart={chart} />
                    ))}
                  </div>
                )}
                
                {/* Render components if present */}
                {msg.components && msg.components.length > 0 && (
                  <div className="mt-4 space-y-4">
                    {msg.components.map((component, index) => (
                      <AIFinancialWidget key={index} widget={component} />
                    ))}
                  </div>
                )}
              </MessageContent>
            </Message>
          ))}
          
          {loading && (
            <div className="flex items-center gap-2 py-4">
              <Loader size={20} />
              <span className="text-sm text-muted-foreground">Analyzing your finances...</span>
            </div>
          )}
        </ConversationContent>
      </Conversation>

      {/* Input Area */}
      <div className="p-4 space-y-3">
        <Suggestions>
          {currentSuggestions.slice(0, 3).map((suggestion, index) => (
            <Suggestion
              key={index}
              suggestion={suggestion}
              onClick={handleSuggestionClick}
            />
          ))}
        </Suggestions>

        <PromptInput onSubmit={handleSubmit}>
          <PromptInputTextarea
            value={message}
            onChange={handleChange}
            disabled={loading}
            placeholder="Ask about your finances..."
          />
          <PromptInputToolbar>
            <div />
            <PromptInputSubmit 
              disabled={loading || message.trim() === ''}
              status={loading ? 'streaming' : 'idle'}
            />
          </PromptInputToolbar>
        </PromptInput>
      </div>
    </div>
  )
}

