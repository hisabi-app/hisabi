import { subMonths } from 'date-fns';

import CountUp from '@/components/Wallet/CountUp';
import Panel from '@/components/Wallet/Panel';
import { amount } from '@/components/Wallet/format';
import { getAppCurrency } from '@/Utils';
import { monthName, shortDate } from './dates';
import MonthChart from './MonthChart';
import type { HomeData } from './types';

export default function MonthCard({ month, index }: { month: HomeData['month']; index: number }) {
    const now = new Date();
    const difference = Math.round(month.spent - month.spent_last_month_to_date);

    let comparison: React.ReactNode = null;
    if (month.started_at) {
        comparison = <>since you started on {shortDate(month.started_at)}</>;
    } else if (month.has_last_month && difference === 0) {
        comparison = <>the same as this time last month</>;
    } else if (month.has_last_month) {
        comparison = (
            <>
                <span className="mr-1 inline-block rounded-full bg-secondary px-2 font-bold text-foreground font-rounded tabular">
                    {difference < 0 ? '↓' : '↑'} <CountUp value={Math.abs(difference)} format={(value) => amount(value)} />
                </span>
                {difference < 0 ? 'less' : 'more'} than this time last month
            </>
        );
    }

    // In the first month the chart starts on the first transaction's day
    const firstDay = month.started_at ? Number(month.started_at.slice(8, 10)) : 1;
    const daily = month.daily.slice(firstDay - 1);
    const showChart = daily.length >= 3;

    return (
        <Panel index={index}>
            <p className="font-medium text-muted-foreground">Spent so far</p>
            <p className="mt-0.5 text-[40px] leading-[1.1] font-bold tracking-[-0.03em] font-rounded">
                <small className="mr-1 text-[0.45em] font-semibold tracking-normal text-muted-foreground">{getAppCurrency()}</small>
                <CountUp value={month.spent} format={(value) => amount(value)} />
            </p>
            {comparison && <p className="mt-1 text-[13px] text-muted-foreground">{comparison}</p>}

            {showChart && (
                <div className="mt-3">
                    <MonthChart
                        daily={daily}
                        firstDay={firstDay}
                        lastMonth={month.has_last_month ? month.last_month_daily : []}
                        label="Spending so far this month against last month"
                    />
                    <div className="mt-1.5 flex gap-3.5 text-[11.5px] text-muted-foreground">
                        <span className="inline-flex items-center gap-1.5">
                            <i className="w-3.5 border-t-2 border-foreground" />
                            {monthName(now)}
                        </span>
                        {month.has_last_month && (
                            <span className="inline-flex items-center gap-1.5">
                                <i className="w-3.5 border-t-2 border-dashed border-[oklch(0.75_0.005_286)]" />
                                {monthName(subMonths(now, 1))}
                            </span>
                        )}
                    </div>
                </div>
            )}
        </Panel>
    );
}
