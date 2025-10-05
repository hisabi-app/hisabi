import { Card, CardContent } from '@/components/ui/card';
import { formatNumber } from '@/Utils';
import { ChartLineIcon } from '@phosphor-icons/react';

interface Budget {
    id: number;
    name: string;
    amount: number;
    total_spent_percentage: number;
    start_at_date: string;
    end_at_date: string;
    remaining_to_spend: number;
    total_margin_per_day: number;
    remaining_days: number;
    elapsed_days_percentage: number;
}

interface BudgetsProps {
    budgets: Budget[];
}

export default function Budgets({ budgets }: BudgetsProps) {
    if (budgets.length === 0) return null;

    return (
        <div className="flex overflow-x-auto gap-4"  style={{
            overflowX: 'scroll',
            msOverflowStyle: 'none',
            scrollbarWidth: 'none',
          }}>
            {budgets.map((budget) => (
                <Card key={budget.id}>
                    <CardContent className='w-84 grid gap-8'>
                        <div className='flex items-center gap-3'>
                            <div className={`size-10 rounded-full flex items-center justify-center badge badge-blue`}>
                                <ChartLineIcon size={20} weight="regular" className="text-current" />
                            </div>
                            <div>
                                <p className="font-medium">{budget.name}</p>
                                <p className='text-xs'><span>AED {formatNumber(budget.remaining_to_spend, null)}</span> left of AED {formatNumber(budget.amount, null)}</p>
                            </div>
                        </div>


                        <div>
                            <div className="relative mb-2">
                                <div
                                    className="absolute z-50 flex flex-col items-center -mt-5 -translate-x-1/2"
                                    style={{ left: budget.elapsed_days_percentage + '%' }}
                                >
                                    <p className="bg-gray-600 text-white text-xs px-2 py-1 rounded-md whitespace-nowrap">
                                        Today
                                    </p>
                                    <div className="w-0.5 h-5 bg-gray-600 opacity-50"></div>
                                </div>
                            </div>

                            <div className="w-full flex items-center h-4 bg-gray-200 rounded-full relative">
                                <div
                                    className="h-full text-center text-xs font-bold flex items-center justify-center text-blue-500 bg-blue-200 rounded-full"
                                    style={{ width: budget.total_spent_percentage + '%' }}
                                >
                                    <span className={budget.total_spent_percentage < 7 ? 'ml-6' : ''}>{budget.total_spent_percentage}%</span>
                                </div>
                            </div>

                            <div className="flex justify-between">
                                <p className="text-xs text-gray-400">{budget.start_at_date}</p>
                                <p className="text-xs text-gray-400">{budget.end_at_date}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}

