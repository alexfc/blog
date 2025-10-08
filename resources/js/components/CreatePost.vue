<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8 text-center text-gray-800">Create Post</h1>
    <form @submit.prevent="createPost" class="max-w-2xl mx-auto bg-white p-8 shadow-lg rounded-lg">
      <div class="mb-6">
        <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title</label>
        <input type="text" id="title" v-model="post.title" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
      </div>
      <div class="mb-6">
        <label for="content" class="block text-gray-700 text-sm font-bold mb-2">Content</label>
        <textarea id="content" v-model="post.content" required rows="10" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
      </div>
      <div class="mb-6">
        <label for="tags" class="block text-gray-700 text-sm font-bold mb-2">Tags (comma separated)</label>
        <input type="text" id="tags" v-model="tags" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
      </div>
      <div class="mb-6 flex items-center">
        <input type="checkbox" id="is_public" v-model="post.is_public" class="mr-2 leading-tight">
        <label for="is_public" class="text-sm text-gray-700">Public</label>
      </div>
      <div class="flex items-center justify-between">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
          Create
        </button>
      </div>
    </form>
    <div v-if="error" class="mt-4 text-center text-red-500">
      <p>An error occurred: {{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { usePosts } from '../services/PostService';

const post = ref({
  title: '',
  content: '',
  is_public: false,
});
const tags = ref('');
const error = ref(null);
const router = useRouter();
const auth = useAuthStore();
const { createPost: createPostService } = usePosts();

const createPost = async () => {
  error.value = null;
  try {
    const newPost = await createPostService(post.value, tags.value, auth.token);
    router.push({ name: 'post', params: { slug: newPost.slug } });
  } catch (e) {
    error.value = e.message;
    console.error('There was an error creating the post:', e);
  }
};
</script>
