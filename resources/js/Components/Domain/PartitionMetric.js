import React, { useEffect, useState } from 'react';
import { Chart, ArcElement, DoughnutController } from 'chart.js';
import { sumBy } from 'lodash';

import { query } from '../../Api';
import Card from "../Global/Card";
import LoadingView from "../Global/LoadingView";
import { colors, formatNumber, getTailwindColor } from '../../Utils';

Chart.register(ArcElement, DoughnutController);

export default function PartitionMetric({ name, graphql_query, ranges, relation, show_currency }) {
    const [data, setData] = useState(null);
    const [selectedRange, setSelectedRange] = useState(ranges[0].key);
    const [chartRef, setChartRef] = useState(null);
    const [relationData, setRelationData] = useState([]);
    const [selectedRelationId, setSelectedRelationId] = useState(0);

    useEffect(() => {
        if(! relation) { return; }

        query(relation.graphql_query + `{ id ${relation.display_using} }`, null, 'CustomQuery')
            .then(({data}) => {
                setRelationData(data[relation.graphql_query])
                setSelectedRelationId(data[relation.graphql_query][0].id)
            })
            .catch(console.error)
    }, [])

    useEffect(() => {
        setData(null);

        if(relation) {
            if (selectedRelationId) {
                query(graphql_query + `(range: """${selectedRange}""" ${relation.foreign_key}: ${selectedRelationId})`, null, 'CustomQuery')
                    .then(({data}) => setData(JSON.parse(data[graphql_query])))
                    .catch(console.error)
            }

            return;
        }
        
        query(graphql_query, selectedRange)
            .then(({data}) => setData(JSON.parse(data[graphql_query])))
            .catch(console.error)
    }, [selectedRelationId, selectedRange])

    useEffect(() => {
        if(data == null) { return; }
        
        if(chartRef != null) {
            chartRef.destroy()
        }

        const ctx = document.getElementById(graphql_query).getContext('2d');
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
        <Card className="relative">
            <div className="px-6 py-4">
                <div className="flex justify-between items-center mb-2">
                    <div className="flex items-center">
                        <h3 className="mr-2 text-base text-gray-700 font-bold">{ name }</h3>

                        {relation && relationData && <select className="ml-auto min-w-24 h-8 text-xs border-none appearance-none pl-2 pr-6 active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
                            name="relation"
                            value={selectedRelationId}
                            onChange={(e) => {setSelectedRelationId(e.target.value)}}>
                            {relationData.map(relationItem => <option key={relationItem.id} value={relationItem.id}>{relationItem[relation.display_using]}</option>)}
                        </select>}
                    </div>

                    <select className="ml-auto min-w-24 h-8 text-xs border-none appearance-none bg-gray-100 pl-2 pr-6 rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
                        name="range"
                        value={selectedRange}
                        onChange={(e) => {setSelectedRange(e.target.value)}}>
                        {ranges.map(range => <option key={range.key} value={range.key}>{range.name}</option>)}
                    </select>
                </div>

                <div className="absolute w-20 h-20" style={{right: '35px', top: '40%'}}>
                    <canvas id={graphql_query}></canvas>
                </div>

                <div className="min-h-22">
                    <div className="overflow-hidden overflow-y-auto max-h-22">
                        <ul className="list-reset">
                            {data.map((item, index) => <li key={index} className="text-xs text-gray-700 leading-normal">
                                <span className={`inline-block rounded-full w-2 h-2 mr-2 ${getTailwindColor(index)}`} />
                                {item.label} ({show_currency && <>{AppCurrency} </>}{formatNumber(item.value)} - {total > 0 && formatNumber(item.value * 100 / total) + "%"})
                            </li>)}
                        </ul>

                        {data.length == 0 && <p className="flex items-center text-gray-500">
                            No data found
                        </p>}
                    </div>
                </div>
            </div>
        </Card>
    );
}