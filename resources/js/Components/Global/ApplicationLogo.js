import React from 'react';

export default function ApplicationLogo() {
    return (
        <p data-testid="application-logo">
            <img className="h-10 pl-1" src="/images/logo.svg" />
        </p>
    );
}
