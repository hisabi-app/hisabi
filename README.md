<h1 align="center">
<img width="300" src="./public/images/logo.svg" />    
</h1>

<p align="center">
  <b>Hisabi is a simple yet powerful, self-hosted personal finance tracking web app with the ability to parse SMS transactions, generate very useful insights about your money, and power AI!</b>
</p>

<p align="center"><a href="https://www.youtube.com/watch?v=kfwcMdlFn9o&list=PLw5MK6ws-o1_rNobmZCmnH5G11vwCiKKk&ab_channel=ILoveMathAcademy" target="__blank"><img src="https://raw.githubusercontent.com/hisabi-app/hisabi/refs/heads/main/public/images/showcase.png" /></a></p>

## 💰 Sponsors
Support this project by becoming a sponsor ❤️. Your logo will show up here with a link to your website. [Become a sponsor](https://github.com/sponsors/saleem-hadad)

Follow me on [LinkedIn](https://www.linkedin.com/in/saleem-hadad/) for updates and latest news.

## 🛠 Features

- [x] 🔐 Self-hosted — Full control over your data
- [x] 📩 SMS Parser — Auto-detect bank transactions
- [x] 📊 Reports & Visualization — Clear finance insights
- [x] 🤖 HisabAI — AI-powered finance assistance
- [ ] Multiple Accounts (Coming Soon)
- [ ] API Support (Coming Soon)
- [x] 🆓 MIT Licensed — Fully open-source


## 🎮 Demo

Try the app with [live demo](https://hisabi.on-forge.com/).

## ▶️ Installation 

> Docker Installation

1. Method one (recommended)
   
```bash
git clone https://github.com/hisabi-app/hisabi && cd hisabi

make build # build the docker image
make run # the same as docker-compose up -d

# wait for a few seconds to allow the DB to finish the setup then run
make install # only for the first time
```

<details>
<summary>2. Method two (using docker-compose public hosted docker image)</summary>

First, create a `docker-compose.yml` file
```yml
version: '3'
services:
    app:
        image: 'salee2m1/hisabi:2.0.1'
        ports:
            - "80:80"
        networks:
            - hisabi
        depends_on:
            - mysql
        environment:
            OPENAI_API_KEY: 'YOUR_OPENAI_API_KEY'
    mysql:
        image: 'mysql/mysql-server:8.0'
        ports:
            - '3306:3306'
        environment:
            MYSQL_ROOT_PASSWORD: 'root'
            MYSQL_ROOT_HOST: "%"
            MYSQL_DATABASE: 'hisabi'
            MYSQL_USER: 'hisabi'
            MYSQL_PASSWORD: 'hisabi'
            MYSQL_ALLOW_EMPTY_PASSWORD: 1
        volumes:
            - 'hisabimysql:/var/lib/mysql'
        networks:
            - hisabi
        healthcheck:
            test: ["CMD", "mysqladmin", "ping", "-proot"]
            retries: 3
            timeout: 5s
networks:
    hisabi:
        driver: bridge
volumes:
    hisabimysql:
        driver: local
```

Then, inside the same directory run

```bash
docker-compose up -d
# wait for a few seconds to run the DB then run
docker-compose run app php artisan migrate
docker-compose run app php artisan hisabi:install
```

</details>

Once done, visit the app on `http://localhost`

## 🔖 License

This project is licensed under the MIT License - see the [LICENSE.md](https://github.com/hisabi-app/hisabi/blob/main/LICENSE) file for details.
