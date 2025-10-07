import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import { useAuthStore } from './stores/auth';
import AppHeader from './components/AppHeader.vue';
import PrimeVue from 'primevue/config';
import Lara from '@primeuix/themes/lara';

const pinia = createPinia();
const app = createApp({});

app.component('app-header', AppHeader);

app.use(pinia);
app.use(router);
app.use(PrimeVue, {
    theme: {
        preset: Lara
    }
});

const authStore = useAuthStore();
authStore.fetchUser().then(() => {
    app.mount('#app');
});
