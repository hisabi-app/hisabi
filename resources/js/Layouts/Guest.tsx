import React from 'react';

export default function Guest({ children }) {
    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-muted">
            <img className="h-14 mb-4" src="/images/logo.svg" />
            
            <div className="w-full max-w-sm">
                {children}
            </div>
        </div>
    );
}
