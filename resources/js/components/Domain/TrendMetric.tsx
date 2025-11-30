import React, { useEffect, useState } from 'react';
import { Chart, LineElement, Tooltip, LineController, CategoryScale, LinearScale, PointElement, Filler } from 'chart.js';
import AnnotationPlugin from 'chartjs-plugin-annotation';

import { metricEndpoints } from '@/Api/metrics';
import { Card } from '@/components/ui/card';
import LoadingView from "../Global/LoadingView";
import { formatNumber } from '@/Utils';
import { useRange } from '@/contexts/RangeContext';
import { useInView } from '@/hooks/useInView';

Chart.register(LineElement, Tooltip, LineController, CategoryScale, LinearScale, PointElement, Filler, AnnotationPlugin);

export default function TrendMetric({ name, metric, relation, show_standard_deviation }) {
    const { selectedRange } = useRange();
    const [data, setData] = useState(null);
    const [chartRef, setChartRef] = useState(null);
    const [relationData, setRelationData] = useState([]);
    const [selectedRelationId, setSelectedRelationId] = useState(0);
    const [ref, isInView] = useInView();

    useEffect(() => {
        if (!isInView) return;
        if (!relation) { return; }

        if (relation.data) {
            setRelationData(relation.data);
            if (relation.data.length > 0) {
                setSelectedRelationId(relation.data[0].id);
            }
            return;
        }

        relation.fetcher()
            .then(({ data }) => {
                setRelationData(data[relation.data_key])
                setSelectedRelationId(data[relation.data_key][0].id)
            })
            .catch(console.error)
    }, [relation, isInView])

    useEffect(() => {
        if (!isInView) return;

        const fetcher = metricEndpoints[metric];
        if (!fetcher) {
            console.error(`Unknown metric: ${metric}`);
            return;
        }

        if (relation) {
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
    }, [selectedRelationId, selectedRange, metric, isInView])

    useEffect(() => {
        if (data == null) { return; }

        if (chartRef != null) {
            chartRef.destroy()
        }

        const average = (ctx) => {
            const values = ctx.chart.data.datasets[0].data;
            if (values.length == 0) return 0;

            return values.reduce((a, b) => a + b, 0) / values.length;
        }

        const standardDeviation = (ctx) => {
            const values = ctx.chart.data.datasets[0].data;
            if (values.length == 0) return 0;

            const n = values.length;
            const mean = average(ctx);
            return Math.sqrt(values.map(x => Math.pow(x - mean, 2)).reduce((a, b) => a + b) / (n - 1)) - mean;
        }

        const standardDeviationAnnotations = () => {
            if (!show_standard_deviation) return [];

            return [
                {
                    type: 'line',
                    borderColor: 'rgba(102, 102, 102, 0.5)',
                    borderDash: [6, 6],
                    borderDashOffset: 0,
                    borderWidth: 2,
                    label: {
                        display: true,
                        backgroundColor: 'rgba(102, 102, 102, 0.5)',
                        color: 'black',
                        content: (ctx) => (average(ctx) + standardDeviation(ctx)).toFixed(2),
                        position: 'start',
                    },
                    scaleID: 'y',
                    value: (ctx) => average(ctx) + standardDeviation(ctx)
                },
                {
                    type: 'line',
                    borderColor: 'rgba(102, 102, 102, 0.5)',
                    borderDash: [6, 6],
                    borderDashOffset: 0,
                    borderWidth: 2,
                    label: {
                        display: true,
                        backgroundColor: 'rgba(102, 102, 102, 0.5)',
                        color: 'black',
                        content: (ctx) => (average(ctx) - standardDeviation(ctx)).toFixed(2),
                        position: 'end',
                    },
                    scaleID: 'y',
                    value: (ctx) => average(ctx) - standardDeviation(ctx)
                },
                {
                    type: 'line',
                    borderColor: '#3b82f6',
                    borderDash: [6, 6],
                    borderDashOffset: 0,
                    borderWidth: 2,
                    label: {
                        display: true,
                        backgroundColor: '#3b82f6',
                        content: (ctx) => 'Average: ' + average(ctx).toFixed(2)
                    },
                    scaleID: 'y',
                    value: (ctx) => average(ctx)
                }
            ];
        }

        const drawLinearRegressionLine = (data) => {
            let regressor = {};

            let x_values = Array.from({ length: data.length }, (_, index) => index + 1);;
            let y_values = data.map(item => item.value);

            let x_mean = x_values.reduce((a, b) => a + b, 0) / x_values.length;
            let y_mean = y_values.reduce((a, b) => a + b, 0) / y_values.length;

            let slope = 0, slope_numerator = 0, slope_denominator = 0;
            for (let i = 0; i < x_values.length; i++) {
                slope_numerator += (x_values[i] - x_mean) * (y_values[i] - y_mean);
                slope_denominator += Math.pow((x_values[i] - x_mean), 2);
            }

            slope = slope_numerator / slope_denominator;

            regressor['slope'] = slope;
            let intercept = y_mean - x_mean * slope;

            regressor['intercept'] = intercept;

            let y_hat = [];
            for (let i = 0; i < x_values.length; i++) {
                y_hat.push(x_values[i] * regressor['slope'] + regressor['intercept']);
            }

            regressor['y_hat'] = y_hat;

            let residual_sum_of_squares = 0, total_sum_of_squares = 0, r2 = 0;

            for (let i = 0; i < y_values.length; i++) {
                residual_sum_of_squares += Math.pow((y_hat[i] - y_values[i]), 2);
                total_sum_of_squares += Math.pow((y_hat[i] - y_mean), 2);
            }

            r2 = 1 - residual_sum_of_squares / total_sum_of_squares;

            regressor['r2'] = r2;

            return {
                type: 'line',
                label: 'Line of Best Fit (r2: ' + String(r2) + ')',
                data: y_hat,
                borderColor: '#eaeaea',
                pointRadius: 0,
                borderWidth: 2,
            }
        }

        const ctx = document.getElementById(metric).getContext('2d');

        const gradient = ctx.createLinearGradient(0, 0, 0, 150);
        gradient.addColorStop(0, 'rgba(14, 165, 233, 0.4)');
        gradient.addColorStop(1, 'rgba(255, 255, 255, 0.8)');

        setChartRef(new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.label),
                datasets: [
                    {
                        data: data.map(item => item.value),
                        borderColor: '#0ea5e9',
                        backgroundColor: gradient,
                        pointHoverRadius: 8,
                        pointRadius: 6,
                        pointBackgroundColor: '#0ea5e9',
                        fill: 'start',
                        tension: 0.4,
                    },
                    drawLinearRegressionLine(data)
                ]
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
                    },
                    annotation: {
                        annotations: {
                            ...standardDeviationAnnotations()
                        }
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
                },
            }
        }));
    }, [data]);

    if (data == null) {
        return (
            <div ref={ref}>
                <Card className="relative h-48">
                    <LoadingView />
                </Card>
            </div>
        )
    }

    return (
        <Card className="relative h-48 overflow-hidden">
            <div className="px-6">
                <div className="flex justify-between items-center mb-2">
                    <div className="flex items-center">
                        <h3 className="mr-2 text-base text-gray-600">{name}</h3>

                        {relation && relationData && <select className="ml-auto min-w-24 h-8 text-xs border-none appearance-none pl-2 pr-6 active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
                            name="relation"
                            value={selectedRelationId}
                            onChange={(e) => { setSelectedRelationId(e.target.value) }}>
                            {relationData.map(relationItem => <option key={relationItem.id} value={relationItem.id}>{relationItem[relation.display_using]}</option>)}
                        </select>}
                    </div>
                </div>

                <div className="text-2xl font-semibold text-gray-800">
                    {data[data.length - 1] && data[data.length - 1].value > 0
                        ? formatNumber(data[data.length - 1].value)
                        : '-'
                    }
                </div>
            </div>


            <div className="absolute w-full left-0 right-0 bottom-0 h-24">
                <canvas id={metric}></canvas>
            </div>
        </Card>
    );
}
