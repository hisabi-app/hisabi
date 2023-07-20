import { Fragment, useState } from 'react'
import { Dialog, Transition } from '@headlessui/react'
import { ArrowRightIcon } from '@heroicons/react/outline';

export default function Example() {
  const [message, setMessage] = useState('');
  const [open, setOpen] = useState(true);
  const [chatHistory, setChatHistory] = useState([]);

  const handleChange = (event) => {
    setMessage(event.target.value);
  };

  const handleSubmit = (event) => {
    event.preventDefault();
    if (message.trim() === '') return;

    const newMessage = {
      id: chatHistory.length + 1,
      text: message,
      sender: 'user',
    };

    setChatHistory([...chatHistory, newMessage]);
    setMessage('');
  };

  return (
    <Transition.Root show={open} as={Fragment} afterLeave={() => setQuery('')} appear>
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
                        <p>FinanceGPT</p>
                        <p className='text-xs text-gray-500'>The information provided by FinanceGPT is for general informational purposes only. When using FinanceGPT, please be aware that we use large language model technology provided by OpenAl, located in the United States.</p>
                    </div>
                    <div className="mb-4">
                        {chatHistory.map((msg) =>
                        msg.sender === 'user' ? (
                            <div
                            key={msg.id}
                            className="flex justify-end mb-2 items-center"
                            >
                            <div className="bg-teal-500 text-white py-2 px-4 rounded-lg">
                                {msg.text}
                            </div>
                            </div>
                        ) : (
                            <div key={msg.id} className="flex mb-2 items-center">
                            <div className="bg-gray-200 py-2 px-4 rounded-lg">
                                {msg.text}
                            </div>
                            </div>
                        )
                        )}
                    </div>
                    <div className='flex gap-x-1 mb-2'>
                        <button type="button" className="text-gray-500 border rounded-full px-1 text-xs focus:outline-none">
                            how can I save money?
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
                        className="flex-grow border border-gray-300 rounded-l-lg p-2 focus:outline-none"
                        placeholder="Type your message..."
                        />
                        <button
                        type="submit"
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
  )
}
