import React from 'react';
import { Head } from '@inertiajs/react';

import Wrapper from '@/components/Global/Wrapper';
import { renderComponent } from '@/components';
import Authenticated from '@/Layouts/Authenticated';
import NoContent from '@/components/Global/NoContent';

export default function Dashboard({auth, metrics, budgets, hasData}) {
    return (
        <Authenticated auth={auth}>
            <Head title="Hisabi Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto flex flex-wrap md:px-6">

                    {/* BETA Stuff */}
                    <div className={'w-full flex flex-wrap'}>
                        {budgets.length > 0 && budgets.map( (budget, index) => {
                            return <Wrapper key={index} width={'1/3'}>
                                <div className={'bg-white shadow rounded-lg w-full min-h-[170]'}>
                                    <div className="p-4 h-full">
                                        <h3 className="mr-3 text-base text-gray-600">{budget.name}</h3>
                                        <div className="mt-2">
                                            <div className="w-full flex items-center h-6 bg-blue-50 rounded-full relative">
                                                <div className="h-full text-center font-bold flex items-center justify-center text-white bg-blue-400 rounded-full" style={{width: budget.total_spent_percentage + '%'}}></div>
                                                <div className="w-full h-full text-center absolute m-auto font-bold flex items-center justify-center text-white drop-shadow">{budget.total_spent_percentage}%</div>
                                            </div>
                                            <div className="flex justify-between mt-2">
                                                <p className="text-xs text-gray-500">{budget.start_at_date}</p>
                                                <p className="text-xs text-gray-500">{budget.end_at_date}</p>
                                            </div>
                                        </div>
                                        <p className="text-center"><span className={'font-bold'}>AED {budget.remaining_to_spend}</span> left of AED {budget.amount}</p>

                                        <p className="text-xs text-gray-500 text-center mt-1">You can spend AED {budget.total_margin_per_day} per day for {budget.remaining_days} more days</p>
                                    </div>
                                </div>
                            </Wrapper>
                        })}
                    </div>

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
