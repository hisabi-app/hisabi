# Installation

> {success.fa-video} If you are a visual learner, please watch this [video](https://www.youtube.com/watch?v=kfwcMdlFn9o&list=PLw5MK6ws-o1_rNobmZCmnH5G11vwCiKKk&ab_channel=ILoveMathAcademy) instead.

---

- [Overview](#overview)
- [Installation](#installation)
- [Usage](#usage)
- [Story](#story)
- [Credits](#credits)
- [Licence](#licence)

<a name="overview"></a>
## Overview 

**FINANCE** is a simple yet powerful, self-hosted finance tracking web app with the ability to parse SMS transactions and generate very useful insights about your money.

![finance](/images/finance.jpg)
<p align="center"><a href="https://www.youtube.com/watch?v=kfwcMdlFn9o&list=PLw5MK6ws-o1_rNobmZCmnH5G11vwCiKKk&ab_channel=ILoveMathAcademy" target="__blank"><img style="height: 40px" src="/images/video.png" /></a></p>

<a name="installation"></a>
## Installation

>{info} Docker Installation

```bash
# step 1: clone the repo
git clone https://github.com/saleem-hadad/finance && cd finance

# step 2: create .env file
cp .env.sail.example .env

# step 3: install deps via composer
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs

# step 4: serve the app
./vendor/bin/sail up -d

# step 5: generate app key
./vendor/bin/sail artisan key:generate

# step 6: run migration
./vendor/bin/sail artisan migrate

# step 7: run install command and follow the instructions
./vendor/bin/sail artisan finance:install
```

Once done, visit the app on `http://localhost`


>{info} Normal Laravel App

If you wish installing the app using normal Laravel environment, make sure you have PHP, MySQL, and composer already installed and then run the following commands:

```bash
# step 1: clone the repo
git clone https://github.com/saleem-hadad/finance && cd finance

# step 2: create .env file
cp .env.example .env

# step 3: install deps via composer
composer install

# step 4: generate app key
php artisan key:generate

# step 5: run the migration
php artisan migrate

# step 6: run install command and follow the instructions
php artisan finance:install

# step 7: serve the app
php artisan serve
```

Once done, visit the app on `http://localhost:8000`

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

If the SMS provided to the parser does not match with any registered templates, the entry will be marked as invalid SMS. However, you can add the missing template in the `config/finance.php` and then try to parse the SMS again from the user interface.

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

This project is licensed under the MIT License - see the [LICENSE.md](https://github.com/saleem-hadad/finance/blob/main/LICENSE) file for details.