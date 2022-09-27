# Reports

> {success.fa-video} If you are a visual learner, please watch this [video](https://www.youtube.com/watch?v=eYp1cYMTiTs&list=PLw5MK6ws-o1_rNobmZCmnH5G11vwCiKKk&index=4&ab_channel=ILoveMathAcademy) instead.

---

- [Overview](#overview)
- [Available Reports](#reports)
- [Custom Report](#custom-report)

<a name="overview"></a>
## Overview

FINANCE comes with many built-in reports(metrics) to generate useful financial data and understand the distribution and trends of expenses vs income.

Any report must be registered in the config file `config/finance.php` under `reports`.

```php
return [
    ...
    'reports' => [
        new TotalIncome,
        new TotalExpenses,
        new IncomePerCategory,
        new ExpensesPerCategory,
        new TotalIncomeTrend,
        new TotalExpensesTrend,
        new TotalPerCategoryTrend,
        new TotalPerBrandTrend,
        new TotalPerBrand,
    ],
]
```

A report simply is a GraphQL query represented as a PHP class. you can find all the reports under namespace `App\GraphQL\Queries`.

<a name="reports"></a>
## Available Reports

Total Income
![image](/images/total-income.png)

Total Expenses
![image](/images/total-expenses.png)

Income Per Category
![image](/images/income-per-category.png)

Expenses Per Category
![image](/images/expenses-per-category.png)

Total Income Trend
![image](/images/total-income-trend.png)

Total Expenses Trend
![image](/images/total-expenses-trend.png)

Total Per Category Trend
![image](/images/total-per-category-trend.png)

Total Per Brand Trend
![image](/images/total-per-brand-trend.png)

Total Per Brand
![image](/images/total-per-brand.png)

<a name="custom-report"></a>
## Custom Report

To write a custom report, you need to create a class that extends one of the available metrics (you can build a new metric type too):

1. `ValueMetric`: displays a single value and, if desired, its change compared to a previous time interval.
2. `PartitionMetric`: displays a pie chart of values. For example, a partition metric might display the total amount for each expenses category.
3. `TrendMetric`: displays values over time via a line chart


```php
<?php

namespace App\GraphQL\Queries;

use App\Models\Transaction;
use App\Domain\Metrics\ValueMetric;

class TotalIncome extends ValueMetric // <-- Required
{
    public function __invoke($_, array $args) // <-- Executed by GraphQL query
    {
        $rangeData = app('findRangeByKey', ["key" => $args['range']]);

        $query = Transaction::query()->income();

        if($rangeData) {
            $query->whereBetween('created_at', [$rangeData->start(), $rangeData->end()]);
        }

        return ['value' => $query->sum('amount')];
    }
}
```

Once you created the report, add the reference to it in the config file:

```php
// config/finance.php

return [
    ...
    'reports' => [
        new TotalIncome,
        ...
    ],
]
```

Finally, register the GraphQL query in the `graphql/schema.graphql`:

>{info} Note the name of the query is the same as the class name written in camelCase.

```graphql
type Query {
    ..
    totalExpenses(range: String!): Json
    ..
}
```