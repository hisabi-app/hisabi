import React from 'react';

const Card = ({children, className = ''}) => {
    return (
        <div className={`bg-white shadow rounded-lg h-[160px] ${className}`}>
            {children}
        </div>
    )
}

export default Card
