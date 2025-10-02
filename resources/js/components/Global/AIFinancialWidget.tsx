import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ExclamationIcon, CheckCircleIcon, InformationCircleIcon } from '@heroicons/react/solid';

interface AIFinancialWidgetProps {
  widget: {
    type: string;
    data: any;
  };
}

export default function AIFinancialWidget({ widget }: AIFinancialWidgetProps) {
  const { type, data } = widget;
  
  const renderWidget = () => {
    switch (type) {
      case 'budget_alert':
        return <BudgetAlert data={data} />;
      case 'savings_card':
        return <SavingsCard data={data} />;
      case 'category_breakdown':
        return <CategoryBreakdown data={data} />;
      case 'spending_summary':
        return <SpendingSummary data={data} />;
      default:
        return <GenericWidget data={data} />;
    }
  };
  
  return renderWidget();
}

function BudgetAlert({ data }: { data: any }) {
  const getIcon = () => {
    if (data.status === 'exceeded' || data.status === 'critical') {
      return <ExclamationIcon className="w-5 h-5 text-destructive" />;
    }
    if (data.status === 'warning') {
      return <ExclamationIcon className="w-5 h-5 text-yellow-500" />;
    }
    return <CheckCircleIcon className="w-5 h-5 text-green-500" />;
  };
  
  const getBadgeVariant = () => {
    if (data.status === 'exceeded' || data.status === 'critical') return 'destructive';
    if (data.status === 'warning') return 'secondary';
    return 'default';
  };
  
  return (
    <Card className="border-l-4 border-l-primary">
      <CardHeader className="pb-2">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            {getIcon()}
            <CardTitle className="text-base">{data.title || 'Budget Alert'}</CardTitle>
          </div>
          <Badge variant={getBadgeVariant()}>{data.status}</Badge>
        </div>
      </CardHeader>
      <CardContent>
        <p className="text-sm text-muted-foreground">{data.message}</p>
        {data.amount && (
          <p className="text-lg font-semibold mt-2">{data.amount}</p>
        )}
      </CardContent>
    </Card>
  );
}

function SavingsCard({ data }: { data: any }) {
  return (
    <Card className="bg-green-50 dark:bg-green-950">
      <CardHeader className="pb-2">
        <CardTitle className="text-base flex items-center gap-2">
          <CheckCircleIcon className="w-5 h-5 text-green-600" />
          {data.title || 'Savings Opportunity'}
        </CardTitle>
      </CardHeader>
      <CardContent>
        <p className="text-sm text-muted-foreground mb-2">{data.message}</p>
        {data.potential && (
          <p className="text-lg font-semibold text-green-700 dark:text-green-400">
            Potential Savings: {data.potential}
          </p>
        )}
        {data.action && (
          <p className="text-xs text-muted-foreground mt-2">💡 {data.action}</p>
        )}
      </CardContent>
    </Card>
  );
}

function CategoryBreakdown({ data }: { data: any }) {
  const categories = data.categories || [];
  
  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-base">{data.title || 'Category Breakdown'}</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-2">
          {categories.map((category: any, index: number) => (
            <div key={index} className="flex justify-between items-center p-2 rounded bg-secondary">
              <div className="flex items-center gap-2">
                <div
                  className="w-3 h-3 rounded-full"
                  style={{ backgroundColor: category.color || '#666' }}
                />
                <span className="text-sm font-medium">{category.name}</span>
              </div>
              <div className="text-right">
                <p className="text-sm font-semibold">{category.amount}</p>
                {category.percentage && (
                  <p className="text-xs text-muted-foreground">{category.percentage}%</p>
                )}
              </div>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}

function SpendingSummary({ data }: { data: any }) {
  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-base">{data.title || 'Spending Summary'}</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-2 gap-4">
          {Object.entries(data.metrics || {}).map(([key, value]: [string, any]) => (
            <div key={key} className="space-y-1">
              <p className="text-xs text-muted-foreground capitalize">
                {key.replace(/_/g, ' ')}
              </p>
              <p className="text-lg font-semibold">{value}</p>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}

function GenericWidget({ data }: { data: any }) {
  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-base flex items-center gap-2">
          <InformationCircleIcon className="w-5 h-5 text-primary" />
          {data.title || 'Financial Insight'}
        </CardTitle>
      </CardHeader>
      <CardContent>
        {data.message && (
          <p className="text-sm text-muted-foreground">{data.message}</p>
        )}
        {data.value && (
          <p className="text-lg font-semibold mt-2">{data.value}</p>
        )}
      </CardContent>
    </Card>
  );
}

