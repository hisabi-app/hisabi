import React, { useEffect, useState } from 'react';

import { query } from '../../Api';
import Card from "../Global/Card";
import LoadingView from "../Global/LoadingView";

function ValueMetric({name, graphql_query, ranges}) {
    const [data, setData] = useState(null);
    const [selectedRange, setSelectedRange] = useState(ranges ? ranges[0].key : null);

    useEffect(() => {
        setData(null);

        query(graphql_query, selectedRange)
            .then(({data}) => setData(data[graphql_query]))
            .catch(console.error)
    }, [selectedRange])

    if(data == null) {
        return (
            <Card className="relative">
                <LoadingView  />
            </Card>
        )
    }

    return (
        <Card>
            <div className="px-6 py-4">
                <div className="flex justify-between items-center mb-2">
                    <h3 className="mr-3 text-base text-gray-700 font-bold">{ name }</h3>

                    {ranges && <select className="ml-auto min-w-24 h-8 text-xs border-none appearance-none bg-gray-100 pl-2 pr-6 rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
                        name="range"
                        value={selectedRange}
                        onChange={(e) => {setSelectedRange(e.target.value)}}>
                        {ranges.map(range => <option key={range.key} value={range.key}>{range.name}</option>)}
                    </select>}
                </div>

                <p className="flex items-center text-4xl mb-4">
                    { AppCurrency } { Engine.formatNumber(data) }
                </p>
            </div>
        </Card>
    );
}

export default ValueMetric;