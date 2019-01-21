import Vue from 'vue';

import apiFetch from '@wordpress/api-fetch';
import App from './App.vue';

// App
const rootEl = document.getElementById('vue-app');

if (rootEl) {

    apiFetch.use(apiFetch.createNonceMiddleware(rootEl.getAttribute('data-nonce')));
    apiFetch.use(apiFetch.createRootURLMiddleware(rootEl.getAttribute('data-rest-url')));

    new Vue({
        el: rootEl,
        data: {apiFetch},
        components: {App},
        template: `<App :fetch="apiFetch" />`
    });

}
