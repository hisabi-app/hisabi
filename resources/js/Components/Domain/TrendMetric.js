import React, { useEffect, useState } from 'react';
import { Chart, LineElement, Tooltip, LineController, CategoryScale, LinearScale, PointElement, Filler } from 'chart.js';

import { query } from '../../Api';
import Card from "../Global/Card";
import LoadingView from "../Global/LoadingView";

Chart.register(LineElement, Tooltip, LineController, CategoryScale, LinearScale, PointElement, Filler);

export default function TrendMetric({ name, graphql_query, ranges, relation }) {
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
            type: 'line',
            data: {
                labels: data.map(item => item.label),
                datasets: [{
                  data: data.map(item => item.value),
                  borderColor: '#0ea5e9',
                  backgroundColor: 'rgba(14, 165, 233, 0.2)',
                  pointHoverRadius: 6,
                  pointRadius: 4,
                  pointBackgroundColor: '#0ea5e9',
                  fill: 'start',
                  tension: 0.4,
                }]
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 0,
                        right: 0,
                        bottom: 0,
                        top: 5
                    },
                    autoPadding: false
                },
                plugins: {
                    filler: {
                        propagate: false,
                    },
                    tooltip: {
                        displayColors: false,
                        backgroundColor: '#fff',
                        borderColor: '#0ea5e9',
                        borderWidth: 1,
                        titleColor: '#0ea5e9',
                        bodyColor: '#0ea5e9',
                        xAlign: 'center',
                        yAlign: 'center',
                    }
                },
                scales: {
                    y: {
                      display: false,
                      beginAtZero: true,
                      grid: {
                        display: false,
                      },
                    },
                    x: {
                        display: false,
                        grid: {
                          display: false
                        },
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
    
    return ( 
        <Card className="relative overflow-hidden">
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
            </div>

            <div className="absolute w-full left-0 right-0 bottom-0 h-20">
                <canvas id={graphql_query}></canvas>
            </div>
        </Card>
    );
}