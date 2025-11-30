import React, { useEffect, useState } from 'react';
import { TrendingUpIcon, TrendingDownIcon, InformationCircleIcon } from '@heroicons/react/solid';
import { DateRange } from 'react-day-picker';

import { metricEndpoints } from '@/Api/metrics';
import { Card } from '@/components/ui/card';
import LoadingView from "../Global/LoadingView";
import { formatNumber, getAppCurrency } from '../../Utils';
import { useInView } from '@/hooks/useInView';

interface ValueMetricProps {
    name: string;
    helpText?: string;
    metric: string;
    dateRange: DateRange | undefined;
}

export default function ValueMetric({ name, helpText, metric, dateRange }: ValueMetricProps) {
    const [value, setValue] = useState(null);
    const [previous, setPrevious] = useState(null);
    const [ref, isInView] = useInView();

    useEffect(() => {
        if (!isInView || !dateRange?.from || !dateRange?.to) return;

        const fetchData = async () => {
            const fetcher = metricEndpoints[metric];
            if (!fetcher) {
                console.error(`Unknown metric: ${metric}`);
                return;
            }

            const response = await fetcher(dateRange);
            setValue(response.data.value);
            setPrevious(response.data.previous);
        };

        fetchData();
    }, [dateRange, metric, isInView])

    if(value == null) {
        return (
            <div ref={ref}>
                <Card className="relative h-[118px]">
                    <LoadingView  />
                </Card>
            </div>
        )
    }

    const growthPercentage = () => {
        return Math.abs(increaseOrDecrease())
    }

    const increaseOrDecrease = () => {
        if (previous == 0 || previous == null)
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
            <div className="px-6 flex flex-col h-full gap-y-2">
                <div className="flex grow-0 justify-between items-center">
                    <h3 className="mr-3 text-base text-gray-600">{ name }</h3>
                </div>

                <p className="flex grow-1 items-center text-3xl">
                    { getAppCurrency() } { formatNumber(value) }
                </p>

                {increaseOrDecrease() !== 0 && <div className="flex grow-0">
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
