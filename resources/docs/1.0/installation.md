# Installation

> {success.fa-video} If you are a visual learner, please watch this [video](https://www.youtube.com/watch?v=kfwcMdlFn9o&list=PLw5MK6ws-o1_rNobmZCmnH5G11vwCiKKk&ab_channel=ILoveMathAcademy) instead.

---

- [Installation](#installation)
  - [Overview](#overview)
  - [Installation](#installation-1)
  - [Usage](#usage)
  - [Story (optional)](#story-optional)
  - [Credits](#credits)
  - [Licence](#licence)

<a name="overview"></a>
## Overview 

**hisabi** is a simple yet powerful, self-hosted personal finance tracking web app with the ability to parse SMS transactions and generate very useful insights about your money. It's also powered by ChatGPT!

<br/>
![finance](/images/showcase.png)
<p align="center"><a href="https://www.youtube.com/watch?v=kfwcMdlFn9o&list=PLw5MK6ws-o1_rNobmZCmnH5G11vwCiKKk&ab_channel=ILoveMathAcademy" target="__blank"><img style="height: 40px" src="/images/video.png" /></a></p>

<a name="installation"></a>
## Installation

1. Clone the repository and navigate to the project directory:

```bash
git clone https://github.com/hisabi-app/hisabi && cd hisabi && make build
```

Run the app:

```bash
make run # the same as docker-compose up -d 
```

Then wait for a few seconds to allow the DB to finish the setup then run:

```bash
make install # only for the first time
```

Once done, visit the app on `http://localhost:8000` and login with your account you just created in the installation step.

<a name="usage"></a>
## Usage

>{success} STEP 1: Create a category

A category is used to describe a group of brands and it has a unique type either INCOME or EXPENSES.

![create-category](/images/create-category.png)

>{success} Create a brand

A brand must belong to a specific category, for example, IKEA can belong to the Shopping category.

![create-brand](/images/create-brand.png)

>{success} Create a transaction

A transaction must be associated with a specific brand.

![create-transaction](/images/create-transaction.png)

>{success} SMS Parser

Another useful way to create transactions automatically is to read and parse transactions SMS that contains at least two information, the `amount` and `brand`.

For example, try to parse the following SMS message:

```text
Purchase of AED 125.50 with Credit Card ending 9048 at IKEA, DUBAI.
```

This SMS will create a new transaction with AED 125.5 amount and brand is IKEA. If the brand is unknown to the system, it will be highlighted in red in order for you to link it with the correct category and it's a one-time job only.

![parse-sms](/images/parse-sms.png)

>{warning} Note

If the SMS provided to the parser does not match with any registered templates, the entry will be marked as invalid SMS. However, you can add the missing template in the `config/hisabi.php` and then try to parse the SMS again from the user interface.

>{success} View reports

Once you have a few transactions, you'll be able to view the amazing data provided by the reports. You can write build own reports too!

![view-reports](/images/view-reports.png)

<a name="story"></a>
## Story (optional)

I made extensive research (before building this app) on the market and I found several apps and open-source projects but none of them fulfilled my need as I need something very simple that can automate the process of finance tracking by reading from the SMS messages that I receive from my bank. 


The main goal for this project is to use the analytics data of money transactions (income & expenses) as a reference for any future decision whether to invest in or buy something. 

If personal finance is causing mental health for you, try to track it and reduce that stress. It's never too late.

Finally, I hope you find this app very useful.

<a name="credits"></a>
## Credits

This app uses few open-source libraries and packages, many thanks to the web community:

- Laravel
- InertiaJs & ReactJs
- TailwindCSS
- GraphQL & LighthousePHP
- LaRcipe
- Docker
- MySQL

<a name="licence"></a>
## Licence

This project is licensed under the MIT License - see the [LICENSE.md](https://github.com/hisabi-app/hisabi/blob/main/LICENSE) file for details.
