import axios from 'axios';

// broadcast descomentar essa linha
// import './echo';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

