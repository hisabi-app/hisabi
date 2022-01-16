# SMS Parser

> {success.fa-video} If you are a visual learner, please watch this [video](https://www.youtube.com/watch?v=U1GU9cGqvq4&list=PLw5MK6ws-o1_rNobmZCmnH5G11vwCiKKk&index=2&ab_channel=ILoveMathAcademy) instead.

---

- [Overview](#overview)
- [Custom SMS Template](#sms-template)

<a name="overview"></a>
## Overview

A fast and automatic way of tracking financial data is by readying the transactions' SMS messages if you have them from your bank. For me, I sit down every week to copy-past the SMS messages that I had received and let this app parse the needed information from each SMS.

Each bank might send a different SMS template and, therefore, you need to register the needed SMS templates in the `config/finance.php` under `sms_templates`.

Examples from the registered templates are:

```text
# Template
Payment of AED {amount} to {brand} with {card}.

# Example of Actual SMS
Payment of AED 102.0 to Amazon.ae with Creidt Card ending with XXXX.
```

As you can see, the template inherit the original SMS structure with dynamic variables

1. `{amount}`: The amount of the transaction will be extracted from this needle.
1. `{brand}`: The name of the brand will be extracted from this needle.

>{info} For the case of Salary as being Income, the template (in my case) will be. Feel free to add yours as well.

```text
# Template
{brand} of AED {amount} has been credited 

# Example of Actual SMS
Salary of AED 1000.0 has been credited 
```

<a name="sms-template"></a>
## Custom SMS Template

If you wish to register a custom SMS template, you can register it under `config/finance.php` file under `sms_templates` as a string. However, please make sure to include the two required placeholders: `{amount}` and `{brand}`.

For example, let's say the message you receive for any offline purchase as follow:

```text
Dear Customer, You have made a payment of USD 200.0 using Debit Card to IKEA from your Account.
```

The corresponding SMS template will be:

```text
Dear Customer, You have made a payment of USD {amount} using {card} to {brand} from your Account.
```

```php
# config/finance.php

return [
    ...
    'sms_templates' => [
        ...,
        'Dear Customer, You have made a payment of USD {amount} using {card} to {brand} from your Account.'
    ],
    ...
]
```    