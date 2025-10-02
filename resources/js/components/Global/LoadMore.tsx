import React from 'react';

import Loader from "./Loader";
import NoContent from "./NoContent";

function LoadMore({ hasContent, hasMorePages, loading, onClick }) {
    return (
        <div className="py-4 flex justify-center">
            {!hasContent && !loading && <NoContent />}
            {hasContent && !hasMorePages && !loading && <div>
            </div>}
            {hasMorePages && !loading && <button className='text-blue-500 hover:underline' onClick={onClick}>Load more</button>}
            {hasMorePages && loading && <Loader />}
        </div>
    );
}

export default LoadMore;