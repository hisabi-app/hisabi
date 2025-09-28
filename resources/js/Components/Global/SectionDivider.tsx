import React from 'react';

export default function NoContent({title}) {
    return ( 
        <div className="w-full border-b pb-3">
            <h2 className='text-lg text-gray-600'>{title}</h2>
        </div>
     );
}