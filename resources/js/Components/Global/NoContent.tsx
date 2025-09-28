import React from 'react';

export default function NoContent({body = 'No resources found 🤔'}) {
    return ( 
        <div className="flex justify-center w-full">
            <p className='text-gray-600'>{body}</p>
        </div>
     );
}