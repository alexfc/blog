import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('token') || null);

  function setUser(newUser) {
    user.value = newUser;
  }

  function setToken(newToken) {
    token.value = newToken;
    if (newToken) {
      localStorage.setItem('token', newToken);
    } else {
      localStorage.removeItem('token');
    }
  }

  async function fetchUser() {
    if (!token.value) {
      return;
    }

    try {
      const response = await fetch('/api/user', {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Accept': 'application/json',
        },
      });
      if (response.ok) {
        const userData = await response.json();
        setUser(userData);
      } else {
        setToken(null);
        setUser(null);
      }
    } catch (error) {
      console.error('Error fetching user:', error);
      setToken(null);
      setUser(null);
    }
  }

  function logout() {
    setToken(null);
    setUser(null);
  }

  return { user, token, setUser, setToken, fetchUser, logout };
});
