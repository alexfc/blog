<template>
  <div>
    <h1>Login</h1>
    <form @submit.prevent="login">
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" v-model="email" required>
      </div>
      <div>
        <label for="password">Password</label>
        <input type="password" id="password" v-model="password" required>
      </div>
      <button type="submit">Login</button>
    </form>
    <div v-if="error">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const email = ref('');
const password = ref('');
const error = ref(null);
const router = useRouter();
const auth = useAuthStore();

const login = async () => {
  error.value = null;
  try {
    const response = await fetch('/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
      }),
    });

    if (response.ok) {
      const data = await response.json();
      auth.setToken(data.token);
      await auth.fetchUser();
      router.push('/');
    } else {
      const data = await response.json();
      error.value = data.message || 'Login failed.';
    }
  } catch (e) {
    error.value = 'Login failed.';
    console.error('There was an error logging in:', e);
  }
};
</script>
