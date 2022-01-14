import axios from 'axios';
import Wrapper from '@/Components/Wrapper';
import { renderComponent } from '@/Components';
import { Head } from '@inertiajs/inertia-react';
import React, { useEffect, useState } from 'react';
import Authenticated from '@/Layouts/Authenticated';

export default function Dashboard({auth, errors, metrics, graphqlQueries}) {
    const [metricsData, setMetricsData] = useState([])

    useEffect(() => {
        axios.post('/graphql', {query: `query { ${graphqlQueries} }`})
            .then(({data}) => setMetricsData(data.data))
            .catch(console.error);
    }, []);

    return (
        <Authenticated auth={auth} errors={errors}>
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto flex flex-wrap md:px-6">
                    {metrics.map( metric => {
                        metric.data = metricsData[metric.graphql_query]
                        return <Wrapper 
                            key={metric.graphql_query}
                            width={metric.width} 
                            children={renderComponent(metric.component, metric)}
                            />
                    })}
                </div>
            </div>
        </Authenticated>
    );
}
