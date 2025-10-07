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

  return {
    posts,
    loading,
    error,
    fetchPosts,
  };
}
