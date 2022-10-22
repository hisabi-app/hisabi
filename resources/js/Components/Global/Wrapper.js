import React from 'react';

export default function Wrapper({ children, width='1/3' }) {
  return ( 
    <div className={`px-3 mb-6 w-full md:w-${width}`}>
      {children}
    </div> 
  );
}