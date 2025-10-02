import { Card, CardContent } from '@/components/ui/card';
import { formatNumber } from '@/Utils';

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
}

interface BudgetsProps {
    budgets: Budget[];
}

export default function Budgets({ budgets }: BudgetsProps) {
    if (budgets.length === 0) return null;

    return (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {budgets.map((budget) => (
                    <Card key={budget.id}>
                        <CardContent>
                            <h3 className="mr-3 text-base text-gray-600">{budget.name}</h3>
                            <div className="mt-2">
                                <div className="w-full flex items-center h-6 bg-blue-50 rounded-full relative">
                                    <div 
                                        className="h-full text-center font-bold flex items-center justify-center text-white bg-blue-400 rounded-full" 
                                        style={{ width: budget.total_spent_percentage + '%' }}
                                    />
                                    <div className="w-full h-full text-center absolute m-auto font-bold flex items-center justify-center text-white drop-shadow">
                                        {budget.total_spent_percentage}%
                                    </div>
                                </div>
                                <div className="flex justify-between mt-2">
                                    <p className="text-xs text-gray-500">{budget.start_at_date}</p>
                                    <p className="text-xs text-gray-500">{budget.end_at_date}</p>
                                </div>
                            </div>
                            <p className="text-center">
                                <span className="font-bold">AED {formatNumber(budget.remaining_to_spend)}</span> left of AED {formatNumber(budget.amount)}
                            </p>
                            </CardContent>
                    </Card>
                ))}
            </div>
    );
}

