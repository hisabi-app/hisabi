import React from 'react';

const Card = ({children, className = ''}) => {
    return (
        <div className={`bg-white shadow rounded-lg min-h-[150px] ${className}`}>
            {children}
        </div>
    )
}

export default Card
