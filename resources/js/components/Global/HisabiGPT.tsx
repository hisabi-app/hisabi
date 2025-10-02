import { useEffect, useState } from 'react'
import { customQuery } from '../../Api';
import { XIcon } from '@heroicons/react/solid';
import { Message, MessageContent } from '@/components/ui/shadcn-io/ai/message';
import { Response } from '@/components/ui/shadcn-io/ai/response';
import { Conversation, ConversationContent } from '@/components/ui/shadcn-io/ai/conversation';
import { PromptInput, PromptInputTextarea, PromptInputToolbar, PromptInputSubmit } from '@/components/ui/shadcn-io/ai/prompt-input';
import { Suggestions, Suggestion } from '@/components/ui/shadcn-io/ai/suggestion';
import { Loader } from '@/components/ui/shadcn-io/ai/loader';

interface HisabiGPTProps {
  onClose: () => void;
}

interface ChatMessage {
  id: number;
  content: string;
  role: 'user' | 'assistant';
}

export default function HisabiGPT({ onClose }: HisabiGPTProps) {
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

    let messages = newChat.map((message) => `{role: "${message.role}" content: """${message.content.replace(/"/g, '\\"')}"""}`);
    let graphql_query = `hisabiGPT(messages: [${messages}])`;

    let { data } = await customQuery(graphql_query);
    let parsedData = JSON.parse(data['hisabiGPT']);

    setChatHistory([...chatHistory, parsedData]);
    setLoading(false);
  };

  const handleSuggestionClick = (suggestionText: string) => {
    setMessage(suggestionText);
  };

  return (
    <div className="h-full w-full flex flex-col overflow-hidden">
      {/* Header */}
      <div className="border-b p-4">
        <div className='flex justify-between items-center'>
          <h2 className='text-lg font-semibold'>Hisabi AI</h2>
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
              <p className="text-muted-foreground text-sm">Start a conversation...</p>
            </div>
          )}
          
          {chatHistory.map((msg) => (
            <Message key={msg.id} from={msg.role}>
              <MessageContent>
                <Response>{msg.content}</Response>
              </MessageContent>
            </Message>
          ))}
          
          {loading && (
            <div className="flex items-center gap-2 py-4">
              <Loader size={20} />
              <span className="text-sm text-muted-foreground">Thinking...</span>
            </div>
          )}
        </ConversationContent>
      </Conversation>

      {/* Input Area */}
      <div className="p-4 space-y-3">
        <Suggestions>
          <Suggestion
            suggestion="how much can I save money approximately?"
            onClick={handleSuggestionClick}
          />
          <Suggestion
            suggestion="what is my top expense last month?"
            onClick={handleSuggestionClick}
          />
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
