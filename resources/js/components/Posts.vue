<template>
  <div>
    <h1>Blog Posts</h1>
    <div v-if="loading">Loading...</div>
    <div v-if="error">{{ error }}</div>
    <div v-if="posts">
      <div v-for="post in posts.data" :key="post.id">
        <router-link :to="{ name: 'post', params: { slug: post.slug } }">
          <h2>{{ post.title }}</h2>
        </router-link>
        <p>{{ post.content }}</p>
      </div>
      <button @click="fetchPosts(posts.prev_page_url)" :disabled="!posts.prev_page_url">Previous</button>
      <button @click="fetchPosts(posts.next_page_url)" :disabled="!posts.next_page_url">Next</button>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { usePosts } from '../services/PostService';

const { posts, loading, error, fetchPosts } = usePosts();

onMounted(() => {
  fetchPosts('/api/posts');
});
</script>