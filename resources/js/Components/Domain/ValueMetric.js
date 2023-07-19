import React, { useEffect, useState } from 'react';
import { TrendingUpIcon, TrendingDownIcon, InformationCircleIcon } from '@heroicons/react/solid';

import { query } from '../../Api';
import Card from "../Global/Card";
import LoadingView from "../Global/LoadingView";
import { formatNumber, getAppCurrency } from '../../Utils';

export default function ValueMetric({name, helpText, graphql_query, ranges}) {
    const [value, setValue] = useState(null);
    const [previous, setPrevious] = useState(null);
    const [selectedRange, setSelectedRange] = useState(ranges ? ranges[0].key : null);

    useEffect(async () => {
        setValue(null);

        let { data } = await query(graphql_query, selectedRange);
        let parsedData = JSON.parse(data[graphql_query]);

        setValue(parsedData.value)
        setPrevious(parsedData.previous)
    }, [selectedRange])

    if(value == null) {
        return (
            <Card className="relative">
                <LoadingView  />
            </Card>
        )
    }

    const growthPercentage = () => {
        return Math.abs(increaseOrDecrease())
    }

    const increaseOrDecrease = () => {
        if (previous == 0 || previous == null || value == 0)
          return 0

        return (((value - previous) / previous) * 100).toFixed(2)
    }

    const increaseOrDecreaseLabel = () => {
        switch (Math.sign(increaseOrDecrease())) {
          case 1:
            return 'Increase'
          case 0:
            return 'Constant'
          case -1:
            return 'Decrease'
        }
    }

    const increaseColor = () => name.toLowerCase().includes("expense") ? 'text-red-500' : 'text-green-500'
    const decreaseColor = () => name.toLowerCase().includes("expense") ? 'text-green-500' : 'text-red-500'

    return (
        <Card className='relative'>
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
                    { getAppCurrency() } { formatNumber(value) }
                </p>

                {increaseOrDecrease() !== 0 && <div className="flex">
                    {increaseOrDecreaseLabel() === 'Increase' && <TrendingUpIcon className={["mr-2 h-5 w-5", increaseColor()].join(' ')} aria-hidden="true" />}
                    {increaseOrDecreaseLabel() === 'Decrease' && <TrendingDownIcon className={["mr-2 h-5 w-5", decreaseColor()].join(' ')} aria-hidden="true" />}

                    {growthPercentage() !== 0 && <p className="text-gray-500 font-bold">
                        {growthPercentage()}% {increaseOrDecreaseLabel()}
                    </p>}

                    {growthPercentage() === 0 && <p className="text-gray-500 font-bold">
                        No Change
                    </p>}
                </div>}
            </div>

            {helpText && <div className="absolute bottom-1 right-1 flex flex-col items-center group">
                <InformationCircleIcon className="h-4 w-4 text-gray-400" aria-hidden="true" />
                <div className="absolute bottom-0 flex flex-col items-center hidden mb-6 group-hover:flex w-48">
                    <span className="relative z-10 p-2 text-xs leading-none text-white whitespace-no-wrap bg-gray-800 rounded shadow-lg">{helpText}</span>
                    <div className="w-3 h-3 -mt-2 rotate-45 bg-gray-700"></div>
                </div>
            </div>}
        </Card>
    );
};
