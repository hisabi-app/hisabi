import { useEffect, useState } from 'react'
import { ArrowRightIcon } from '@heroicons/react/outline';
import { customQuery } from '../../Api';
import { AtSymbolIcon, XIcon } from '@heroicons/react/solid';

interface HisabiGPTProps {
  onClose: () => void;
}

interface ChatMessage {
  id: number;
  content: string;
  role: string;
}

export default function HisabiGPT({ onClose }: HisabiGPTProps) {
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const [chatHistory, setChatHistory] = useState<ChatMessage[]>([]);

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setMessage(event.target.value);
  };

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (message.trim() === '') return;

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

  return (
    <div className="h-full w-full flex flex-col overflow-hidden">
      <div className="border-r p-4">
        <div className='flex justify-between items-center mb-2'>
          <h2 className='text-lg font-semibold'>Hisabi AI</h2>
          <button
            onClick={onClose}
            className="text-muted-foreground hover:text-foreground"
          >
            <XIcon className='w-5 h-5' />
          </button>
        </div>
        <p className='text-xs text-muted-foreground'>
          Ask questions about your finances and get AI-powered insights.
        </p>
      </div>
      
      <div className="flex-1 flex flex-col p-4 overflow-hidden border-r">

          <div className="flex-1 overflow-y-auto mb-4 space-y-2">
            {chatHistory.length === 0 && (
              <div className="flex items-center justify-center h-full">
                <p className="text-muted-foreground text-sm">Start a conversation...</p>
              </div>
            )}
            {chatHistory.map((msg, index) =>
              msg.role === 'user' ? (
                <div
                  key={index}
                  className="flex justify-end"
                >
                  <div className="bg-primary text-primary-foreground text-sm py-2 px-4 rounded-lg max-w-[80%]">
                    {msg.content}
                  </div>
                </div>
              ) : (
                <div key={index} className="flex">
                  <div className="bg-muted text-sm py-2 px-4 rounded-lg max-w-[80%]">
                    {msg.content.split('\n').map((line: string, lineIndex: number) => (
                      <span key={lineIndex}>
                        {line}
                        <br />
                      </span>
                    ))}
                  </div>
                </div>
              )
            )}
            {loading && (
              <div className="flex items-center">
                <AtSymbolIcon className="animate-spin text-primary h-5 w-5 mr-2" />
                <span className="text-sm text-muted-foreground">Thinking...</span>
              </div>
            )}
          </div>

          <div className='flex gap-2 mb-3 flex-wrap'>
            <button
              type="button"
              onClick={() => setMessage('how much can I save money approximately?')}
              className="bg-muted hover:bg-muted/80 text-foreground border rounded-full px-3 py-1 text-xs transition-colors"
            >
              how much can I save money approximately?
            </button>
            <button
              type="button"
              onClick={() => setMessage('what is my top expense last month?')}
              className="bg-muted hover:bg-muted/80 text-foreground border rounded-full px-3 py-1 text-xs transition-colors"
            >
              what is my top expense last month?
            </button>
          </div>

          <form onSubmit={handleSubmit} className="flex gap-2">
            <input
              type="text"
              value={message}
              onChange={handleChange}
              disabled={loading}
              className="flex-grow border bg-background rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-ring"
              placeholder="Type your message..."
            />
            <button
              type="submit"
              disabled={loading}
              className="bg-primary hover:bg-primary/90 disabled:opacity-50 text-primary-foreground py-2 px-4 rounded-lg transition-colors"
            >
              <ArrowRightIcon className='w-4 h-4 -rotate-45' />
            </button>
          </form>
          <p className='mt-2 text-xs text-muted-foreground text-center'>Powered by OpenAI</p>
        </div>
    </div>
  )
}
