<template>
  <header>
    <nav>
      <router-link to="/">Blog</router-link>
      <div v-if="auth.user">
        <router-link to="/posts/create">Create Post</router-link>
        <span>Welcome, {{ auth.user.name }}</span>
        <button @click="logout">Logout</button>
      </div>
      <div v-else>
        <router-link to="/login">Login</router-link>
        <router-link to="/register">Register</router-link>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const auth = useAuthStore();
const router = useRouter();

const logout = () => {
  auth.logout();
  router.push('/');
};
</script>