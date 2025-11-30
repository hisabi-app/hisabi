import React, {useEffect, useRef, useState} from 'react';
import { metricEndpoints } from '@/Api/metrics';
import LoadingView from "../Global/LoadingView";
import { Card } from '@/components/ui/card';
import { useRange } from '@/contexts/RangeContext';
import { useInView } from '@/hooks/useInView';

export default function CirclePackMetric({name, metric}) {
    const { selectedRange } = useRange();
    const [value, setValue] = useState(null);
    const refContainer = useRef<HTMLDivElement>(null);
    const [ref, isInView] = useInView();

    useEffect(() => {
        if (!isInView) return;

        let isCancelled = false;

        const fetchData = async () => {
            const fetcher = metricEndpoints[metric];
            if (!fetcher) {
                console.error(`Unknown metric: ${metric}`);
                return;
            }

            try {
                const response = await fetcher(selectedRange);

                // Check if the component is still mounted and this is the latest request
                if (!isCancelled) {
                    setValue(response.data);
                }
            } catch (error) {
                // Ignore AbortError as it's expected when component unmounts or range changes
                if (!isCancelled && error.name !== 'AbortError') {
                    console.error('Error fetching circle pack data:', error);
                }
            }
        };

        fetchData();

        // Cleanup function to cancel the request when component unmounts or range changes
        return () => {
            isCancelled = true;
        };
    }, [selectedRange, metric, isInView])

    // Chart initialization effect - only runs on client side
    useEffect(() => {
        // Ensure we're on the client side and have the necessary dependencies
        if (!value || !refContainer.current || typeof window === 'undefined') {
            return;
        }

        // Clear the container before rendering
        refContainer.current.innerHTML = '';

        const initChart = async () => {
            try {
                // Dynamic import to avoid SSR issues
                const { default: CirclePack } = await import('circlepack-chart');
                
                if (refContainer.current && typeof window !== 'undefined') {
                    // Initialize and render the chart
                    const myChart = CirclePack();
                    myChart.tooltipContent((d, node) => `Total: <i>${node.value}</i>`)
                        .excludeRoot(true)
                        .label(d => d.label)
                        .color(d => d.color ?? 'white')
                        .borderWidth('2px')
                        // .width(400)
                        .height(500);

                    // Render the chart
                    myChart.data(value)(refContainer.current);
                    
                    console.log('Chart rendered successfully');
                }
            } catch (error) {
                console.error('Error loading or rendering circle pack chart:', error);
            }
        };

        // Small delay to ensure DOM is fully ready
        const timeoutId = setTimeout(initChart, 100);

        // Cleanup function
        return () => {
            clearTimeout(timeoutId);
        };
    }, [value])

    if(value == null) {
        return (
            <div ref={ref}>
                <Card className="relative h-[550px]">
                    <LoadingView  />
                </Card>
            </div>
        )
    }

    return (
        <Card className={"bg-white shadow rounded-lg w-full overflow-hidden"}>
            <div className="px-6 py-4">
                <div className="flex justify-between items-center mb-2">
                    <h3 className="mr-3 text-base text-gray-600">{ name }</h3>
                </div>

                <div className="w-full flex justify-center items-center overflow-hidden">
                    <div className="w-full flex justify-center" ref={refContainer}></div>
                </div>
            </div>
        </Card>
    );
};
