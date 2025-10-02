import { useMemo } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface AIChartRendererProps {
  chart: {
    type: string;
    title: string;
    data: any;
    config?: any;
  };
}

export default function AIChartRenderer({ chart }: AIChartRendererProps) {
  // For now, render as a simple data display
  // In a production app, you'd integrate Chart.js, Recharts, or similar
  
  const renderSimpleChart = () => {
    const { type, data } = chart;
    
    if (type === 'bar' || type === 'line') {
      // Simple bar/line representation
      const items = Array.isArray(data) ? data : [];
      const maxValue = Math.max(...items.map((item: any) => item.value || 0));
      
      return (
        <div className="space-y-2">
          {items.map((item: any, index: number) => {
            const percentage = maxValue > 0 ? (item.value / maxValue) * 100 : 0;
            return (
              <div key={index} className="space-y-1">
                <div className="flex justify-between text-sm">
                  <span className="font-medium">{item.label || item.name}</span>
                  <span className="text-muted-foreground">{item.value}</span>
                </div>
                <div className="w-full bg-secondary rounded-full h-2">
                  <div
                    className="bg-primary rounded-full h-2 transition-all"
                    style={{ width: `${percentage}%` }}
                  />
                </div>
              </div>
            );
          })}
        </div>
      );
    }
    
    if (type === 'pie') {
      // Simple pie representation as a list with percentages
      const items = Array.isArray(data) ? data : [];
      const total = items.reduce((sum: number, item: any) => sum + (item.value || 0), 0);
      
      return (
        <div className="grid grid-cols-2 gap-4">
          {items.map((item: any, index: number) => {
            const percentage = total > 0 ? ((item.value / total) * 100).toFixed(1) : 0;
            return (
              <div key={index} className="flex items-center gap-2">
                <div
                  className="w-4 h-4 rounded-full"
                  style={{ backgroundColor: item.color || `hsl(${(index * 360) / items.length}, 70%, 50%)` }}
                />
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium truncate">{item.label || item.name}</p>
                  <p className="text-xs text-muted-foreground">{percentage}% ({item.value})</p>
                </div>
              </div>
            );
          })}
        </div>
      );
    }
    
    // Fallback: render as JSON
    return (
      <pre className="text-xs bg-secondary p-2 rounded overflow-auto max-h-40">
        {JSON.stringify(data, null, 2)}
      </pre>
    );
  };
  
  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-base">{chart.title}</CardTitle>
      </CardHeader>
      <CardContent>
        {renderSimpleChart()}
      </CardContent>
    </Card>
  );
}

