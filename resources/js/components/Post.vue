<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="loading" class="text-center text-gray-500">
      <p>Loading...</p>
    </div>
    <div v-if="error" class="text-center text-red-500">
      <p>An error occurred: {{ error }}</p>
    </div>
    <div v-if="post" class="max-w-4xl mx-auto bg-white p-8 shadow-lg rounded-lg">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h1 class="text-4xl font-bold text-gray-900">{{ post.title }}</h1>
          <p class="text-gray-600 mt-2">By {{ post.user.name }}</p>
        </div>
        <router-link v-if="auth.user && auth.user.id === post.user_id" :to="{ name: 'edit-post', params: { slug: post.slug } }" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Edit Post</router-link>
      </div>
      
      <button v-if="auth.user && auth.user.id !== post.user_id" @click="toggleFollow" class="mb-4 px-4 py-2 rounded-md text-white transition-colors duration-300" :class="post.user.is_following ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-500 hover:bg-blue-600'">
        {{ post.user.is_following ? 'Unfollow' : 'Follow' }}
      </button>

      <div v-if="post.can_view_content" class="prose max-w-none text-gray-800">
        <p>{{ post.content }}</p>
      </div>
      <div v-else>
        <div v-if="!auth.user" class="text-center text-gray-600">
            <p>This is a private post. Please <router-link to="/login" class="text-indigo-600 hover:underline">log in</router-link> to request access.</p>
        </div>
        <div v-else-if="post.access_request_status === 'none'" class="text-center">
            <p class="text-gray-600">This post is private. You can request access from the author.</p>
            <button @click="requestAccess" class="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Request Access</button>
        </div>
        <div v-else-if="post.access_request_status === 'pending'" class="text-center text-gray-600">
            <p>Your access request is pending approval from the author.</p>
        </div>
        <div v-else-if="post.access_request_status === 'denied'" class="text-center text-red-500">
            <p>Your access request has been denied by the author.</p>
        </div>
      </div>

      <div v-if="post.tags && post.tags.length > 0" class="mt-6">
        <strong class="text-gray-800">Tags:</strong>
        <ul class="flex space-x-2 mt-2">
          <li v-for="tag in post.tags" :key="tag.id" class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm">{{ tag.name }}</li>
        </ul>
      </div>

      <hr class="my-8">

      <div v-if="post.can_view_content">
        <h3 class="text-2xl font-bold text-gray-900 mb-4">Comments</h3>
        <div v-if="auth.user" class="mb-6">
          <textarea v-model="newComment" placeholder="Add a comment" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="4"></textarea>
          <button @click="addComment" class="mt-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add Comment</button>
        </div>
        <div v-else class="text-center text-gray-600">
          <p>You must be logged in to comment. <router-link to="/login" class="text-indigo-600 hover:underline">Log in</router-link></p>
        </div>

        <ul class="space-y-4">
          <li v-for="comment in comments" :key="comment.id" class="bg-gray-50 p-4 rounded-lg shadow">
            <p class="text-gray-800"><strong>{{ comment.user.name }}</strong>: {{ comment.content }}</p>
          </li>
        </ul>
      </div>
    </div>
    <PostAccessManagement v-if="auth.user && post && auth.user.id === post.user_id" :post="post" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import PostAccessManagement from './PostAccessManagement.vue';
import { useAuthStore } from '../stores/auth';

const post = ref(null);
const loading = ref(false);
const error = ref(null);
const comments = ref([]);
const newComment = ref('');
const route = useRoute();
const auth = useAuthStore();

const postId = computed(() => post.value ? post.value.id : null);

const fetchPost = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await fetch(`/api/posts/${route.params.slug}`, {
        headers: {
            'Authorization': `Bearer ${auth.token}`,
            'Accept': 'application/json',
        },
    });
    if (response.ok) {
      post.value = await response.json();
      if (post.value.can_view_content) {
        await fetchComments();
      }
    } else {
      error.value = 'Post not found.';
    }
  } catch (e) {
    error.value = 'Error fetching post.';
    console.error('There was an error fetching the post:', e);
  } finally {
    loading.value = false;
  }
};

const fetchComments = async () => {
  if (!postId.value) return;
  try {
    const response = await fetch(`/api/posts/${postId.value}/comments`);
    if (response.ok) {
      comments.value = await response.json();
    }
  } catch (e) {
    console.error('Error fetching comments:', e);
  }
};

const addComment = async () => {
  if (!newComment.value.trim() || !postId.value) return;

  try {
    const response = await fetch(`/api/posts/${postId.value}/comments`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${auth.token}`,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ content: newComment.value }),
    });

    if (response.ok) {
      const newCommentData = await response.json();
      comments.value.unshift(newCommentData);
      newComment.value = '';
    } else {
      console.error('Error adding comment:', response.statusText);
    }
  } catch (e) {
    console.error('Error adding comment:', e);
  }
};

const toggleFollow = async () => {
    const url = post.value.user.is_following
        ? `/api/users/${post.value.user.id}/unfollow`
        : `/api/users/${post.value.user.id}/follow`;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${auth.token}`,
                'Accept': 'application/json',
            },
        });

        if (response.ok) {
            post.value.user.is_following = !post.value.user.is_following;
        }
    } catch (e) {
        console.error('Error toggling follow:', e);
    }
};

const requestAccess = async () => {
    try {
        const response = await fetch(`/api/posts/${post.value.id}/request-access`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${auth.token}`,
                'Accept': 'application/json',
            },
        });
        if (response.ok) {
            await fetchPost(); // Refresh post data
        }
    } catch (e) {
        console.error('Error requesting access:', e);
    }
};

onMounted(fetchPost);
</script>