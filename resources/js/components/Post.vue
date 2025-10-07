<template>
  <div>
    <div v-if="loading">Loading...</div>
    <div v-if="error">{{ error }}</div>
    <div v-if="post">
      <router-link v-if="auth.user && auth.user.id === post.user_id" :to="{ name: 'edit-post', params: { slug: post.slug } }">Edit Post</router-link>
      <h1>{{ post.title }}</h1>
      <p>By {{ post.user.name }}</p>
      <button v-if="auth.user && auth.user.id !== post.user_id" @click="toggleFollow">
        {{ post.user.is_following ? 'Unfollow' : 'Follow' }}
      </button>
      <p>{{ post.content }}</p>

      <div v-if="post.tags && post.tags.length > 0">
        <strong>Tags:</strong>
        <ul>
          <li v-for="tag in post.tags" :key="tag.id">{{ tag.name }}</li>
        </ul>
      </div>

      <hr>

      <h3>Comments</h3>
      <div v-if="auth.user">
        <textarea v-model="newComment" placeholder="Add a comment"></textarea>
        <button @click="addComment">Add Comment</button>
      </div>
      <div v-else>
        <p>You must be logged in to comment. <router-link to="/login">Log in</router-link></p>
      </div>

      <ul>
        <li v-for="comment in comments" :key="comment.id">
          <strong>{{ comment.user.name }}</strong>: {{ comment.content }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
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
      await fetchComments();
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

onMounted(fetchPost);
</script>