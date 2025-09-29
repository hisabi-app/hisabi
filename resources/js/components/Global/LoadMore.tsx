import React from 'react';

import Loader from "./Loader";
import NoContent from "./NoContent";

function LoadMore({hasContent, hasMorePages, loading, onClick}) {
    return ( 
        <div className="py-4 flex justify-center">
            {! hasContent && ! loading && <NoContent />}
            {hasContent && ! hasMorePages && ! loading && <p className='text-gray-600'>All resources loaded 🎉</p>}
            {hasMorePages && ! loading && <button className='text-blue-500 font-bold' onClick={onClick}>Load more</button>}
            {hasMorePages && loading && <Loader />}
        </div>
     );
}

export default LoadMore;