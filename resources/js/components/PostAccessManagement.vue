<template>
  <div class="mt-8">
    <h3 class="text-2xl font-bold text-white mb-4">Access Requests</h3>
    <div v-if="loading" class="text-center text-gray-500">Loading requests...</div>
    <div v-if="error" class="text-center text-red-500">{{ error }}</div>
    <div v-if="requests.length > 0">
      <ul class="space-y-4">
        <li v-for="request in requests" :key="request.id" class="bg-gray-50 p-4 rounded-lg shadow flex justify-between items-center">
          <div>
            <p class="font-bold text-gray-800">{{ request.user.name }}</p>
            <p class="text-sm text-gray-600">Status: <span :class="statusClass(request.status)">{{ request.status }}</span></p>
          </div>
          <div v-if="request.status === 'pending'" class="flex space-x-2">
            <button @click="approveRequest(request.id)" class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded">Approve</button>
            <button @click="denyRequest(request.id)" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded">Deny</button>
          </div>
        </li>
      </ul>
    </div>
    <div v-else>
      <p class="text-center text-gray-600">No access requests for this post.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps } from 'vue';
import { useAuthStore } from '../stores/auth';

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
});

const requests = ref([]);
const loading = ref(false);
const error = ref(null);
const auth = useAuthStore();

const fetchRequests = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await fetch(`/api/posts/${props.post.id}/access-requests`, {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Accept': 'application/json',
      },
    });
    if (response.ok) {
      requests.value = await response.json();
    } else {
      error.value = 'Could not load access requests.';
    }
  } catch (e) {
    error.value = 'Error fetching access requests.';
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const approveRequest = async (requestId) => {
  await updateRequestStatus(requestId, 'approve');
};

const denyRequest = async (requestId) => {
  await updateRequestStatus(requestId, 'deny');
};

const updateRequestStatus = async (requestId, action) => {
  try {
    const response = await fetch(`/api/post-access-requests/${requestId}/${action}` , {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Accept': 'application/json',
      },
    });
    if (response.ok) {
      await fetchRequests(); // Refresh the list
    }
  } catch (e) {
    console.error(`Error ${action}ing request:`, e);
  }
};

const statusClass = (status) => {
  switch (status) {
    case 'pending':
      return 'text-yellow-600';
    case 'approved':
      return 'text-green-600';
    case 'denied':
      return 'text-red-600';
    default:
      return 'text-gray-600';
  }
};

onMounted(fetchRequests);
</script>
