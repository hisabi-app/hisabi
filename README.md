<h1 align="center">
<img width="300" src="./public/images/logo.svg" />    
</h1>

<p align="center">
  <b>Hisabi is a simple yet powerful, self-hosted personal finance tracking web app with the ability to parse SMS transactions and generate very useful insights about your money. It's also powered by ChatGPT!</b>
</p>

<p align="center"><a href="https://www.youtube.com/watch?v=kfwcMdlFn9o&list=PLw5MK6ws-o1_rNobmZCmnH5G11vwCiKKk&ab_channel=ILoveMathAcademy" target="__blank"><img src="./public/images/showcase.png" /></a></p>
<p align="center"><a href="https://www.youtube.com/watch?v=kfwcMdlFn9o&list=PLw5MK6ws-o1_rNobmZCmnH5G11vwCiKKk&ab_channel=ILoveMathAcademy" target="__blank"><img height="40" src="./public/images/video.png" /></a></p>

## 💰 Sponsors
Support this project by becoming a sponsor ❤️. Your logo will show up here with a link to your website. [Become a sponsor](https://github.com/sponsors/saleem-hadad)

Follow me on [LinkedIn](https://www.linkedin.com/in/saleem-hadad/) for updates and latest news.

## 🛠 Features

|<picture><img width="100%" alt="Self hosted" src="./public/images/features/privacy.png"></picture>|<picture><img width="100%" alt="SMS parser" src="./public/images/features/sms.png"></picture>|<picture><img width="100%" alt="HisabiGPT" src="./public/images/features/gpt.png"></picture>|
|:---:|:---:|:---:|
|<picture><img width="100%" alt="Reporting" src="./public/images/features/reporting.png"></picture>|<picture><img width="100%" alt="API" src="./public/images/features/api.png"></picture>|<picture><img width="100%" alt="Mobile app" src="./public/images/features/mobie.png"></picture>|


## 🎮 Demo

Try the app with [live demo](https://hisabi.saleem.dev/).

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

Read [full documentation](https://hisabi.saleem.dev/docs)

## JetBrains Sponsorship
Thank you, JetBrains for sponsoring the license ❤️

<a href="https://www.jetbrains.com/community/opensource/#support" target="__blank">
<img src="https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.png?_gl=1*18f1z4q*_ga*MTI4MDYwODYzNy4xNjUyMzU3ODM3*_ga_9J976DJZ68*MTY2MTg3NDM2NC4xMi4xLjE2NjE4NzUxNTAuMC4wLjA.&_ga=2.85008921.1685901777.1661797034-1280608637.1652357837" width="250px" />
</a>

## Get $200 DigitalOcean Credit

[![DigitalOcean Referral Badge](https://web-platforms.sfo2.cdn.digitaloceanspaces.com/WWW/Badge%201.svg)](https://www.digitalocean.com/?refcode=64aee93d49da&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge)


## 🪚 Built with

1. Laravel
2. Inertia & ReactJs
3. GraphQL
4. MySQL
5. Docker

## 🔖 License

This project is licensed under the MIT License - see the [LICENSE.md](https://github.com/hisabi-app/hisabi/blob/main/LICENSE) file for details.
