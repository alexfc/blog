<template>
  <div>
    <h1>Edit Post</h1>
    <form @submit.prevent="updatePost">
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
      <button type="submit">Update</button>
    </form>
    <div v-if="error">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const post = ref({
  title: '',
  content: '',
  is_public: false,
  tags: [],
});
const tags = ref('');
const error = ref(null);
const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const fetchPost = async () => {
  try {
    const response = await fetch(`/api/posts/${route.params.slug}`);
    if (response.ok) {
      const fetchedPost = await response.json();
      if (auth.user && auth.user.id === fetchedPost.user_id) {
        post.value = fetchedPost;
        tags.value = fetchedPost.tags.map(tag => tag.name).join(', ');
      } else {
        router.push('/');
      }
    } else {
      error.value = 'Post not found.';
    }
  } catch (e) {
    error.value = 'Error fetching post.';
    console.error('There was an error fetching the post:', e);
  }
};

const updatePost = async () => {
  error.value = null;
  try {
    const response = await fetch(`/api/posts/${post.value.id}`,
      {
        method: 'PUT',
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
      const updatedPost = await response.json();
      router.push({ name: 'post', params: { slug: updatedPost.slug } });
    } else {
      const data = await response.json();
      error.value = data.message || 'Update failed.';
    }
  } catch (e) {
    error.value = 'Update failed.';
    console.error('There was an error updating the post:', e);
  }
};

onMounted(fetchPost);
</script>
