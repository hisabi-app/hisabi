import React from 'react';

const Card = ({children, height, className = ''}) => {
    return (
        <div className={`bg-white shadow rounded-lg ${height ?? 'min-h-[150px]'} ${className}`}>
            {children}
        </div>
    )
}

export default Card
