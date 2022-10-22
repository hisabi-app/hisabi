import React from 'react';
import { Head } from '@inertiajs/inertia-react';

import Wrapper from '@/Components/Global/Wrapper';
import { renderComponent } from '@/Components';
import Authenticated from '@/Layouts/Authenticated';
import NoContent from '@/Components/Global/NoContent';

export default function Dashboard({auth, metrics, hasData}) {
    return (
        <Authenticated auth={auth}>
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto flex flex-wrap md:px-6">
                    {! hasData && <NoContent body="No enough data to show reports 🧐" />}

                    {hasData && metrics.map( (metric, index) => {
                        return <Wrapper
                            key={index}
                            width={metric.width}
                            children={renderComponent(metric.component, metric)}
                            />
                    })}
                </div>
            </div>
        </Authenticated>
    );
}
