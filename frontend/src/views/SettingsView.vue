<template>
  <div class="page">
    <div class="card">

      <h2 class="title">Add Yandex Organization</h2>

      <form @submit.prevent="submitUrl" class="form">
        <input
          v-model="url"
          type="text"
          placeholder="https://yandex.com/maps/-/CPtpU-KP"
        />

        <button :disabled="loading">
          {{ loading ? 'Saving...' : 'Save' }}
        </button>
      </form>

      <div v-if="loading" class="loading">Loading...</div>

      <div v-if="error" class="error">
        {{ error }}
      </div>

      <h3 class="subtitle">Saved Organizations</h3>

      <ul class="list">
        <li v-for="org in organizations" :key="org.id" class="list-item">
          <router-link :to="`/org/${org.id}`" class="link">
            {{ org.name }}
          </router-link>

          <span class="meta">
            (last synced:
            {{ new Date(org.last_synced_at).toLocaleString() }})
          </span>
        </li>
      </ul>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '../axios'
import { useRouter } from 'vue-router'

const url = ref('')
const loading = ref(false)
const error = ref('')
const organizations = ref([])
const router = useRouter()

const submitUrl = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await axios.post('/api/organizations', {
      url: url.value
    })

    router.push(`/org/${response.data.organization.id}`)
  } catch (err) {
    error.value =
      err?.response?.data?.message || 'Something went wrong'
  } finally {
    loading.value = false
  }
}

const fetchOrganizations = async () => {
  const res = await axios.get('/api/organizations')
  organizations.value = res.data
}

onMounted(fetchOrganizations)
</script>