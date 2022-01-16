# Ranges

> {success.fa-video} If you are a visual learner, please watch this [video](https://www.youtube.com/watch?v=35TLPjXVuOI&list=PLw5MK6ws-o1_rNobmZCmnH5G11vwCiKKk&index=5&ab_channel=ILoveMathAcademy) instead.

---

- [Overview](#overview)
- [Custom Range](#custom-range)

<a name="overview"></a>
## Overview

For each of the reports, you see normally a dropdown that you can select the date range of fetching the transactions, for example, you want to know how much was your entire expenses for **the current month**.  

![ranges-example](/images/ranges-example.png)

There are few available ranges that you can use (Let's say today's date is 2022-05-15):
 

| Name | Start | End |
| : |   :   |  :  |
| CurrentMonth | 2022-05-01 | 2022-05-31 |
| LastMonth | 2022-04-01 | 2022-04-30 |
| CurrentYear | 2022-01-01 | 2022-12-31 |
| LastYear | 2021-01-01 | 2021-12-31 |
| LastTwelveMonths | 2021-05-15 | 2022-05-15 |

<a name="custom-range"></a>
## Custom Range

To define a new range, create a class under namespace `App\Domain\Ranges` and provide the start and end dates as follow:

```php
<?php

namespace App\Domain\Ranges;

class LastSixMonths extends Range
{
    public function start()
    {
        return now()->subMonths(6)->format("Y-m-d");
    }
    
    public function end()
    {
        return now()->format("Y-m-d");
    }
}
```

Then, if you want to register this range with all reports, add the following line to the base class `Metric` under `App\Domain\Metrics`:

```php
<?php

...

abstract class Metric extends Element
{
    ...
    public function ranges()
    {
        return [
            ...
            new LastSixMonths,
        ];
    }
    ...
}
```

If you wish to register this custom range with a specific metric only, then override this method with any metric class.