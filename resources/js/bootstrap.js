import Engine from './Engine';
import Api from './Api';

window._ = require('lodash');

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

window.Engine = new Engine
window.Api = new Api