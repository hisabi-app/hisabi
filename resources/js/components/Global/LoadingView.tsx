import React from 'react';

import Loader from './Loader';

function LoadingView() {
    return ( 
        <div data-testid="loading-view" className="rounded-lg flex items-center justify-center absolute left-0 right-0 top-0 bottom-0 z-50">
            <Loader />
        </div> 
    );
}

export default LoadingView;