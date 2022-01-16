<h1 align="center">FINANCE</h1>

<p align="center">
  <b>FINANCE is a simple yet powerful, self-hosted finance tracking web app with the ability to parse SMS transactions and generate very useful insights about your money</b>
</p>

<p align="center"><img src="./public/images/finance.jpg" /></p>
<p align="center"><a href="#" target="__blank"><img height="40" src="./public/images/video.png" /></a></p>

## 🛠 Features

1. [x] Self-hosted - full control over your data privacy 
2. [x] Parse SMS bank transactions
3. [x] Detailed analysis of income and expenses 

## 🎮 Demo

Try the app with [live demo](https://finance-demo.saleem.dev/).

## ▶️ Installation 

> Docker Installation

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


> Normal Laravel App

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


Read [full documentation](https://finance-demo.saleem.dev/docs)

## 🪚 Built with

1. Laravel
2. Inertia & ReactJs
3. GraphQL
4. MySQL
5. Docker

## 🔖 License

This project is licensed under the MIT License - see the [LICENSE.md](https://github.com/saleem-hadad/finance/blob/main/LICENSE) file for details.
