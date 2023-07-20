import { Fragment, useEffect, useState } from 'react'
import { Dialog, Transition } from '@headlessui/react'
import { ArrowRightIcon } from '@heroicons/react/outline';
import { customQuery } from '../../Api';
import {AtSymbolIcon, XIcon} from '@heroicons/react/solid';
import FloatingButton from './FloatingButton';

export default function FinanceGPT() {
  const [message, setMessage] = useState('');
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [chatHistory, setChatHistory] = useState([]);

  const handleChange = (event) => {
    setMessage(event.target.value);
  };

  const handleSubmit = (event) => {
    event.preventDefault();
    if (message.trim() === '') return;

    const newMessage = {
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


  const submit = async (newChat) => {
    setLoading(true);

    let messages = newChat.map((message) => `{role: "${message.role}" content: """${message.content.replace(/"/g, '\\"')}"""}`);
    let graphql_query = `financeGPT(messages: [${messages}])`;

    let { data } = await customQuery(graphql_query);
    let parsedData = JSON.parse(data['financeGPT']);

    setChatHistory([...chatHistory, parsedData]);
    setLoading(false);
  };

  return (
    <div>
      <Transition.Root show={open} as={Fragment} appear>
      <Dialog as="div" className="relative z-10" onClose={setOpen}>
        <Transition.Child
          as={Fragment}
          enter="ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className="fixed inset-0 bg-gray-500 bg-opacity-50 transition-opacity" />
        </Transition.Child>

        <div className="fixed inset-0 z-10 overflow-y-auto p-4 pb-0">
          <Transition.Child
            as={Fragment}
            enter="ease-out duration-300"
            enterFrom="opacity-0 scale-95"
            enterTo="opacity-100 scale-100"
            leave="ease-in duration-200"
            leaveFrom="opacity-100 scale-100"
            leaveTo="opacity-0 scale-95"
          >
            <Dialog.Panel className="mx-auto max-w-xl transform divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 transition-all">
                <div className="p-4">
                    <div>
                        <div className='flex justify-between'>
                            <p>FinanceGPT (Beta)</p>
                            <button>
                                <XIcon onClick={() => setOpen(false)} className='text-gray-500 w-4 h-4' />
                            </button>
                        </div>
                        <p className='text-xs text-gray-500'>The information provided by FinanceGPT is for general informational purposes only. When using FinanceGPT, please be aware that we use large language model technology provided by OpenAl, located in the United States.</p>
                    </div>
                    <div className="mb-4">
                        {chatHistory.map((msg, index) =>
                        msg.role === 'user' ? (
                            <div
                            key={index}
                            className="flex justify-end mb-2 items-center"
                            >
                            <div className="bg-teal-500 text-sm text-white py-2 px-4 rounded-lg">
                                {msg.content}
                            </div>
                            </div>
                        ) : (
                            <div key={index} className="flex mb-2 items-center">
                            <div className="bg-gray-200 text-sm py-2 px-4 rounded-lg">
                                {msg.content.split('\n').map((line, index) => (
                                    <span key={index}>
                                        {line}
                                        <br />
                                    </span>
                                ))}
                            </div>
                            </div>
                        )
                        )}
                        {loading &&
                            <div className="flex mb-2 items-center">
                              <AtSymbolIcon className="animate-spin text-teal-500 h-5 w-5 mr-2 inline-block" />
                            </div>
                        }
                    </div>
                    <div className='flex gap-x-1 mb-2'>
                        <button type="button"
                        onClick={() => setMessage('how much can I save money approximately?')}
                        className="text-gray-500 border rounded-full px-1 text-xs focus:outline-none">
                            how much can I save money approximately?
                        </button>
                        <button type="button"
                        onClick={() => setMessage('what is my top expense last month?')}
                            className="text-gray-500 border rounded-full px-1 text-xs focus:outline-none">
                            what is my top expense last month?
                        </button>
                    </div>
                    <form onSubmit={handleSubmit} className="flex">
                        <input
                        type="text"
                        value={message}
                        onChange={handleChange}
                        disabled={loading}
                        className="flex-grow border border-gray-300 rounded-l-lg p-2 focus:outline-none"
                        placeholder="Type your message..."
                        />
                        <button
                        type="submit"
                        disabled={loading}
                        className="bg-teal-500 hover:bg-teal-600 text-white py-2 px-4 rounded-r-lg focus:outline-none"
                        >
                        <ArrowRightIcon className='text-white w-4 h-4 -rotate-45' />
                        </button>
                    </form>
                    <p className='mt-1 text-xs text-gray-500'>Powered by OpenAI</p>
                </div>
            </Dialog.Panel>
          </Transition.Child>
        </div>
      </Dialog>
    </Transition.Root>

    <Transition.Root show={!open} appear>
      <FloatingButton onClick={() => setOpen(true)}/>
    </Transition.Root>
    </div>
  )
}
