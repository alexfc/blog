import { ref } from 'vue';

export function usePosts() {
  const posts = ref(null);
  const loading = ref(false);
  const error = ref(null);

  const fetchPosts = async (url) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await fetch(url);
      posts.value = await response.json();
    } catch (e) {
      error.value = 'Error fetching posts.';
      console.error('There was an error fetching the posts:', e);
    } finally {
      loading.value = false;
    }
  };

  const createPost = async (post, tags, token) => {
    const response = await fetch('/api/posts', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        ...post,
        tags: tags.split(',').map(tag => tag.trim()),
      }),
    });

    if (response.ok) {
      return await response.json();
    } else {
      const data = await response.json();
      throw new Error(data.message || 'Create failed.');
    }
  };

  return {
    posts,
    loading,
    error,
    fetchPosts,
    createPost,
  };
}
