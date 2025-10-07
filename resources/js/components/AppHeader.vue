<template>
    <header>
        <nav>
            <div class="flex justify-between bg-gray-800">
                <div>
                    <router-link to="/">Blog</router-link>
                </div>
                <div v-if="auth.user">
                    <span>Welcome, {{ auth.user.name }}</span>
                    <button @click="logout">Logout</button>
                </div>
                <div class="flex gap-6" v-else>
                    <div>
                        <router-link to="/login">Login</router-link>
                    </div>
                    <div>
                        <router-link to="/register">Register</router-link>
                    </div>
                </div>
            </div>
        </nav>
    </header>
</template>

<script setup>
import {useAuthStore} from '../stores/auth';
import {useRouter} from 'vue-router';
import {onMounted} from "vue";

const auth = useAuthStore();
const router = useRouter();

onMounted(() => {
    auth.fetchUser()
        .then(user => {
            console.log(user);
        })
        .catch(error => {
            console.log(error);
        })
})

const logout = () => {
    auth.logout();
    router.push('/');
};
</script>
