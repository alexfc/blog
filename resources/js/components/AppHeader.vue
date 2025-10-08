<template>
    <header class="bg-gray-800 text-white">
        <nav class="container mx-auto px-4 py-2">
            <div class="flex items-center justify-between">
                <router-link to="/" class="text-2xl font-bold">Blog</router-link>
                <div v-if="auth.user" class="flex items-center space-x-4">
                    <router-link to="/posts/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Post</router-link>
                    <span>Welcome, {{ auth.user.name }}</span>
                    <button @click="logout" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</button>
                </div>
                <div v-else class="flex items-center space-x-4">
                    <router-link to="/login" class="hover:text-gray-400">Login</router-link>
                    <router-link to="/register" class="hover:text-gray-400">Register</router-link>
                </div>
            </div>
        </nav>
    </header>
</template>

<script setup>
import {useAuthStore} from '../stores/auth';
import {useRouter} from 'vue-router';

const auth = useAuthStore();
const router = useRouter();

const logout = () => {
    auth.logout();
    router.push('/');
};
</script>
