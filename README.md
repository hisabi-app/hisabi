<h1 align="center">
<img width="300" src="./public/images/logo.svg" />    
</h1>

<p align="center">
  <b>Hisabi is a simple yet powerful, self-hosted personal finance tracking web app with the ability to parse SMS transactions, generate very useful insights about your money, and power AI!</b>
</p>

<p align="center"><a href="https://www.youtube.com/watch?v=kfwcMdlFn9o&list=PLw5MK6ws-o1_rNobmZCmnH5G11vwCiKKk&ab_channel=ILoveMathAcademy" target="__blank"><img src="https://raw.githubusercontent.com/hisabi-app/hisabi/refs/heads/main/public/images/showcase.png" /></a></p>


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
   
```bash
git clone https://github.com/hisabi-app/hisabi && cd hisabi

make build # build the docker image
make run # the same as docker-compose up -d

# wait for a few seconds to allow the DB to finish the setup then run
make install # only for the first time
```

Once done, visit the app on `http://localhost`

## 🔖 License

This project is licensed under the MIT License - see the [LICENSE.md](https://github.com/hisabi-app/hisabi/blob/main/LICENSE) file for details.
