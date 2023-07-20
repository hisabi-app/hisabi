import React from 'react';
import { CogIcon } from '@heroicons/react/outline';

const FloatingButton = ({onClick}) => {
  return (
    <div className="fixed bottom-4 left-0 w-full flex justify-center">
      <button
        onClick={onClick}
        className='bg-teal-500 hover:bg-teal-600 text-white p-2 rounded-full shadow-xl flex items-center'
      >
        <CogIcon className="w-6 h-6 animate-spin-slow" />
        <p>FinanceGPT</p>
      </button>
    </div>
  );
};

export default FloatingButton;
