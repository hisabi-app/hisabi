import React, {useEffect, useRef, useState} from 'react';
import CirclePack from "circlepack-chart";

import { query } from '../../Api';
import Card from "../Global/Card";
import LoadingView from "../Global/LoadingView";

export default function CirclePackMetric({name, graphql_query, ranges}) {
    const [value, setValue] = useState(null);
    const refContainer = useRef(null);
    const [selectedRange, setSelectedRange] = useState(ranges ? ranges[0].key : null);
    const myChart = CirclePack();
    myChart.tooltipContent((d, node) => `Total: <i>${node.value}</i>`)
        .excludeRoot(true)
        .label(d => d.label)
        .color(d => d.color ?? 'white')
        .borderWidth('2px');

    useEffect(async () => {
        setValue(null);

        let { data } = await query(graphql_query, selectedRange);
        let parsedData = JSON.parse(data[graphql_query]);

        setValue(parsedData);
        console.log(parsedData);
        myChart.data(parsedData)(refContainer.current)
    }, [selectedRange])

    if(value == null) {
        return (
            <Card className="relative">
                <LoadingView  />
            </Card>
        )
    }

    return (
        <div className={"bg-white shadow rounded-lg"}>
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

                <div className="w-full flex justify-center items-center overflow-hidden">
                    <div ref={refContainer}></div>
                </div>
            </div>
        </div>
    );
};
