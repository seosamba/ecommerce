import { createApp } from 'vue';
//if you have web history mode
import { createRouter, createWebHistory } from 'vue-router';

import App from './components/app/index.vue';
import {store} from './config/store/';
import "regenerator-runtime/runtime";
import routes from './config/routing/routes';

//if you have localization block start
import { createI18n } from 'vue-i18n';
import messages from './localization';
//if you have localization block ends

const userWebApp =  createApp(App);

const router = createRouter({
    history: createWebHistory(window.location.pathname),
    routes
})

//if you have localization block start
const i18n = createI18n({
    locale: 'en', // set default locale
    messages,
})
userWebApp.use(i18n);

userWebApp.use(store);
userWebApp.use(router);
userWebApp.mount('#customer-groups-config-block-part');

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requiresAuth)) {
        store.commit('checkToken');
        if (store.getters.isLoggedIn) {
            console.log(store.getters.isLoggedIn);
            next()
            return
        } else {
            next({path: '/login'})
        }
    } else {
        console.log('no auth required');
        next()
    }
});
