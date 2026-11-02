import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { Toaster } from 'vue-sonner';
import router from './router';
import App from './App.vue';
import { useAuthStore } from './stores/auth';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);

const auth = useAuthStore();
const init = async () => {
    if (auth.token) {
        await auth.fetchUser();
    }

    app.use(router);
    app.component('Toaster', Toaster);
    app.mount('#app');
};

init();
