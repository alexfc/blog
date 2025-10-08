<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800">Blog Posts</h1>
        <div class="flex space-x-2">
            <button @click="filter = 'all'" :class="{ 'bg-indigo-600 text-white': filter === 'all', 'bg-gray-200 text-gray-800': filter !== 'all' }" class="px-4 py-2 rounded-md transition-colors duration-300">All Posts</button>
            <button @click="filter = 'following'" :class="{ 'bg-indigo-600 text-white': filter === 'following', 'bg-gray-200 text-gray-800': filter !== 'following' }" class="px-4 py-2 rounded-md transition-colors duration-300">Following</button>
        </div>
    </div>
    <div v-if="loading" class="text-center text-gray-500">
      <p>Loading...</p>
    </div>
    <div v-if="error" class="text-center text-red-500">
      <p>An error occurred: {{ error }}</p>
    </div>
    <div v-if="posts" class="space-y-8">
      <div v-for="post in posts.data" :key="post.id" class="bg-white shadow-lg rounded-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 ease-in-out">
        <div class="p-6">
          <router-link :to="{ name: 'post', params: { slug: post.slug } }" class="block">
            <h2 class="text-2xl font-bold text-gray-900 hover:text-indigo-600 transition-colors duration-300">{{ post.title }}</h2>
          </router-link>
          <p v-if="post.can_view_content" class="text-gray-700 mt-4">{{ post.content }}</p>
          <p v-else class="text-gray-500 mt-4">This post is private.</p>
        </div>
      </div>
      <div class="flex justify-between items-center mt-8">
        <button 
          @click="loadPosts(posts.prev_page_url)" 
          :disabled="!posts.prev_page_url"
          class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors duration-300">
          Previous
        </button>
        <button 
          @click="loadPosts(posts.next_page_url)" 
          :disabled="!posts.next_page_url"
          class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors duration-300">
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { usePosts } from '../services/PostService';

const { posts, loading, error, fetchPosts } = usePosts();
const filter = ref('all');

const loadPosts = (url = null) => {
  if (url) {
      const urlObject = new URL(url);
      if (filter.value === 'following') {
          urlObject.searchParams.set('following', 'true');
      }
      fetchPosts(urlObject.toString());
      return;
  }
  
  let apiUrl = '/api/posts';
  if (filter.value === 'following') {
    apiUrl = '/api/posts?following=true';
  }
  fetchPosts(apiUrl);
};

watch(filter, () => loadPosts());

onMounted(() => {
  loadPosts();
});
</script>