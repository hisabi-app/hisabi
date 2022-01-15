# Installation

> {success.fa-video} If you are a visual learner, please watch this video instead.

---

- [Overview](#overview)
- [Installing Finance](#installing-finance)
- [Usage](#usage)
- [Credits](#credits)
- [Licence](#licence)

<a name="overview"></a>
## Overview (optional)

I made extensive research (before building this app) on the market and I found several apps and open-source projects but none of them fulfilled my need as I need something very simple that can automate the process of finance tracking by reading from the SMS messages that I receive from my bank. 

The main goal for this project is to use the analytics data of money transactions (income & expenses) as a reference for any future decision whether to invest in or buy something. 

If personal finance is causing mental health for you, try to track it and reduce that stress. It's never too late.

Finally, I hope you find this app very useful.


<a name="installing-finance"></a>
## Installing Finance

>{info} Docker Installation

```bash
# step 0: clone the app
git clone https://github.com/saleem-hadad/finance && cd finance

# step 1: run migration
./vendor/bin/sail artisan migrate

# step 2: install command
./vendor/bin/sail artisan finance:install

# step 3: serve the app
./vendor/bin/sail up
```

Once done, visit the app on `http://localhost`


>{info} Normal Laravel App

If you wish installing the app using normal Laravel environment, make sure you have PHP, MySQL, and composer already installed and then run the following commands:

```bash
# step 0: clone the app
git clone https://github.com/saleem-hadad/finance && cd finance

# step 1: install deps
composer install

# step 2: 
php artisan migrate

# step 3: install command
php artisan finance:install

# step 4: serve the app
php artisan serve
```

Once done, visit the app on `http://localhost:8000`

<a name="usage"></a>
## Usage

After you create the user, run t

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

This project is licensed under the MIT License - see the [LICENSE.md]() file for details.