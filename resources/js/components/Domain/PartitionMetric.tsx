import { useEffect, useState } from 'react';
import { Chart, ArcElement, DoughnutController } from 'chart.js';
import { sumBy } from 'lodash';

import { metricEndpoints } from '@/Api/metrics';
import { Card } from '@/components/ui/card';
import LoadingView from "../Global/LoadingView";
import { colors, formatNumber, getTailwindColor, getAppCurrency } from '../../Utils';
import { useRange } from '@/contexts/RangeContext';

Chart.register(ArcElement, DoughnutController);

export default function PartitionMetric({ name, metric, relation, show_currency }) {
    const { selectedRange } = useRange();
    const [data, setData] = useState(null);
    const [chartRef, setChartRef] = useState(null);
    const [relationData, setRelationData] = useState([]);
    const [selectedRelationId, setSelectedRelationId] = useState(0);

    useEffect(() => {
        if(! relation) { return; }

        relation.fetcher()
            .then(({data}) => {
                setRelationData(data[relation.data_key])
                setSelectedRelationId(data[relation.data_key][0].id)
            })
            .catch(console.error)
    }, [])

    useEffect(() => {
        setData(null);

        const fetcher = metricEndpoints[metric];
        if (!fetcher) {
            console.error(`Unknown metric: ${metric}`);
            return;
        }

        if(relation) {
            if (selectedRelationId) {
                fetcher(selectedRange, selectedRelationId)
                    .then((response) => setData(response.data))
                    .catch(console.error)
            }
            return;
        }

        fetcher(selectedRange)
            .then((response) => setData(response.data))
            .catch(console.error)
    }, [selectedRelationId, selectedRange, metric])

    useEffect(() => {
        if(data == null) { return; }

        if(chartRef != null) {
            chartRef.destroy()
        }

        const ctx = document.getElementById(metric).getContext('2d');
        setChartRef(new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.label),
                datasets: [{
                  data: data.map(item => item.value),
                  backgroundColor: colors().map(color => color.hex),
                  cutout: '75%',
                  borderWidth: 0
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        enabled: false,
                    }
                }
            }
        }));
    }, [data]);

    if(data == null) {
        return (
            <Card className="relative">
                <LoadingView  />
            </Card>
        )
    }

    let total = sumBy(data, 'value');

    return (
        <Card className="relative h-48 overflow-hidden">
            <div className="px-6 flex flex-col h-full">
                <div className="flex justify-between items-center mb-2">
                    <div className="flex items-center">
                        <h3 className="mr-3 text-base text-gray-600">{ name }</h3>

                        {relation && relationData && <select className="ml-auto min-w-24 h-8 text-xs border-none appearance-none pl-2 pr-6 active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
                            name="relation"
                            value={selectedRelationId}
                            onChange={(e) => {setSelectedRelationId(e.target.value)}}>
                            {relationData.map(relationItem => <option key={relationItem.id} value={relationItem.id}>{relationItem[relation.display_using]}</option>)}
                        </select>}
                    </div>
                </div>

                <div className={`${data.length == 0 ? '' : 'pl-[100px]'} grow overflow-y-auto`}>
                    <ul className="list-reset">
                        {data.map((item, index) => <li key={index} className="text-xs text-gray-700 leading-normal">
                            <span className={`inline-block rounded-full w-2 h-2 mr-2 ${getTailwindColor(index)}`} />
                            {item.label} ({show_currency && <>{getAppCurrency()} </>}{formatNumber(item.value)} - {total > 0 && formatNumber(item.value * 100 / total) + "%"})
                        </li>)}
                    </ul>

                    {data.length == 0 && <p className="flex items-center text-gray-500">
                        No data found
                    </p>}
                </div>

                <div className="absolute w-16 h-16" style={{left: '30px', top: '40%'}}>
                    <canvas id={metric}></canvas>
                </div>
            </div>
        </Card>
    );
}
