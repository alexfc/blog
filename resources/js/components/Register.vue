<template>
  <div>
    <h1>Register</h1>
    <form @submit.prevent="register">
      <div>
        <label for="name">Name</label>
        <input type="text" id="name" v-model="name" required>
      </div>
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" v-model="email" required>
      </div>
      <div>
        <label for="password">Password</label>
        <input type="password" id="password" v-model="password" required>
      </div>
      <div>
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" id="password_confirmation" v-model="password_confirmation" required>
      </div>
      <button type="submit">Register</button>
    </form>
    <div v-if="error">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref(null);
const router = useRouter();
const auth = useAuthStore();

const register = async () => {
  error.value = null;
  try {
    const response = await fetch('/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: password_confirmation.value,
      }),
    });

    if (response.ok) {
      // After registration, log the user in
      const loginResponse = await fetch('/login', {
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

      if (loginResponse.ok) {
        const data = await loginResponse.json();
        auth.setToken(data.token);
        await auth.fetchUser();
        router.push('/');
      } else {
        error.value = 'Login after registration failed.';
      }
    } else {
      const data = await response.json();
      error.value = data.message || 'Registration failed.';
    }
  } catch (e) {
    error.value = 'Registration failed.';
    console.error('There was an error registering:', e);
  }
};
</script>
