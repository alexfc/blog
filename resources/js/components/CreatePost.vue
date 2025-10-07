<template>
  <div>
    <h1>Create Post</h1>
    <form @submit.prevent="createPost">
      <div>
        <label for="title">Title</label>
        <input type="text" id="title" v-model="post.title" required>
      </div>
      <div>
        <label for="content">Content</label>
        <textarea id="content" v-model="post.content" required></textarea>
      </div>
      <div>
        <label for="tags">Tags (comma separated)</label>
        <input type="text" id="tags" v-model="tags">
      </div>
      <div>
        <label for="is_public">Public</label>
        <input type="checkbox" id="is_public" v-model="post.is_public">
      </div>
      <button type="submit">Create</button>
    </form>
    <div v-if="error">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const post = ref({
  title: '',
  content: '',
  is_public: false,
});
const tags = ref('');
const error = ref(null);
const router = useRouter();
const auth = useAuthStore();

const createPost = async () => {
  error.value = null;
  try {
    const response = await fetch('/api/posts', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${auth.token}`,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        ...post.value,
        tags: tags.value.split(',').map(tag => tag.trim()),
      }),
    });

    if (response.ok) {
      const newPost = await response.json();
      router.push({ name: 'post', params: { slug: newPost.slug } });
    } else {
      const data = await response.json();
      error.value = data.message || 'Create failed.';
    }
  } catch (e) {
    error.value = 'Create failed.';
    console.error('There was an error creating the post:', e);
  }
};
</script>
