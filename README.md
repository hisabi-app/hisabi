<h1 align="center">
<img width="300" src="./public/images/logo.svg" />    
</h1>

<p align="center">
  <b>Hisabi is a self-hosted personal finance app that turns your bank SMS into a calm, clear picture of your money: what needs you, how the month is going, and where it all went.</b>
</p>

<p align="center"><a href="https://hisabi.on-forge.com/" target="__blank"><img src="https://raw.githubusercontent.com/hisabi-app/hisabi/refs/heads/main/public/images/showcase.png" /></a></p>


## 🛠 Features

- [x] 📩 **Paste a bank SMS:** Hisabi picks out the amount, the card and the shop, and remembers each shop's category for next time
- [x] ✅ **Needs you:** new brands to categorize, budgets over or ahead of pace, and messages it couldn't read, at the top of the home, with actions right there
- [x] 📈 **This month at a glance:** what you've spent so far against the same point last month, plus a few insights written as plain sentences
- [x] 🎯 **Budgets:** monthly, weekly, yearly or custom, shown as rings, with history to compare periods
- [x] 📊 **Insights:** net worth, where your income went, spending by category, by month and by brand, with the detailed charts one tap away
- [x] 📺 **Kitchen display:** a full-screen view for a tablet on the wall that goes dark at night
- [x] 📱 **Phone friendly:** a bottom dock on phones, a floating sidebar on laptops
- [x] 🔐 **Self-hosted:** your data stays on your own server
- [x] 🆓 **MIT licensed:** fully open source
- [ ] Exporting your data (coming soon)
- [ ] Multiple accounts (coming soon)
- [ ] API support (coming soon)

New to Hisabi? The home starts with just the SMS box and fills in as your transactions come in, so you never stare at empty charts.

## 🎮 Demo

Try the app with the [live demo](https://hisabi.on-forge.com/), or watch the [first alpha version](https://www.youtube.com/watch?v=kfwcMdlFn9o).

## ▶️ Installation 

> Docker Installation
   
```bash
git clone https://github.com/hisabi-app/hisabi && cd hisabi

make build # build the docker image
make run # the same as docker-compose up -d

# wait for a few seconds to allow the DB to finish the setup then run
make install # only for the first time
```

Once done, visit the app on `http://localhost`

### Upgrading

Pull the latest code, rebuild, and run the migrations:

```bash
git pull
make build && make run
docker-compose exec app php artisan migrate --force
```

## 💬 Your bank's SMS

Hisabi reads SMS using the templates in `config/hisabi.php` (`sms_templates`), with placeholders such as `{amount}`, `{brand}`, `{card}`, `{date}`, `{time}` and `{ignore}`. If your bank's messages aren't picked up, add a template that matches them. The currency is set in the same file.

Messages Hisabi couldn't read show up under **Needs you**, so you can fix or delete them.

## 📺 Kitchen display

`/kiosk` is a full-screen dashboard for a tablet on the wall: what you've spent this month compared with the same time last month, what's left in each budget, and the latest transaction. It's white by day and switches to a dark version from 10pm to 6am. It refreshes itself every hour and reloads once a day at 4am.

1. Keep the tablet logged in by setting `SESSION_LIFETIME=525600` (a year, in minutes) in `.env`, then run `php artisan config:clear`.
2. On the tablet, install a kiosk browser such as [Fully Kiosk Browser](https://www.fully-kiosk.com), set its start URL to `http://<your-hisabi>/kiosk`, turn on *Keep Screen On* and *Launch on Boot*, and log in once.

## 🧑‍💻 Contributing

Hisabi is Laravel 12 with React, Inertia and Tailwind.

```bash
composer install && npm install
composer dev        # server, queue, logs and Vite together
php artisan test    # PHP tests
npm run test        # frontend tests
```

Before changing the UI, read [`PRODUCT.md`](PRODUCT.md) (who Hisabi is for and how it should feel) and [`DESIGN.md`](DESIGN.md) (colors, type, components and motion). [`AGENT.md`](AGENT.md) explains how the code is laid out.

## 🔖 License

This project is licensed under the MIT License - see the [LICENSE.md](https://github.com/hisabi-app/hisabi/blob/main/LICENSE) file for details.
