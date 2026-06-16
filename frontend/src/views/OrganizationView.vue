<template>
  <div class="page">
    <div v-if="organization" class="card">

      <h2 class="title">{{ organization.name }}</h2>

      <p class="subtitle">
        Average rating:
        <strong>{{ organization.average_rating || 'N/A' }}</strong>
        ({{ organization.total_ratings }} ratings,
        {{ organization.total_reviews }} reviews)
      </p>

      <div v-if="reviews.data.length">

        <div
          v-for="review in reviews.data"
          :key="review.id"
          class="review-card"
        >
          <div class="review-header">
            <strong>{{ review.author }}</strong>
            <span class="rating">{{ review.rating }}★</span>
          </div>

          <div class="date">
            {{ new Date(review.date).toLocaleDateString() }}
          </div>

          <p class="text">{{ review.text }}</p>
        </div>

        <div class="pagination">
          <button
            @click="prevPage"
            :disabled="reviews.prev_page_url === null"
          >
            Previous
          </button>

          <span>
            Page {{ reviews.current_page }} of {{ reviews.last_page }}
          </span>

          <button
            @click="nextPage"
            :disabled="reviews.next_page_url === null"
          >
            Next
          </button>
        </div>

      </div>

      <div v-else class="empty">
        No reviews available.
      </div>

    </div>

    <div v-else class="loading">
      Loading...
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from '../axios'

const route = useRoute()
const organization = ref(null)
const reviews = ref({ data: [] })

const fetchData = async (page = 1) => {
  const res = await axios.get(`/api/organizations/${route.params.id}?page=${page}`)
  organization.value = res.data.organization
  reviews.value = res.data.reviews
}

const nextPage = () => {
  if (reviews.value.next_page_url) {
    fetchData(reviews.value.current_page + 1)
  }
}

const prevPage = () => {
  if (reviews.value.prev_page_url) {
    fetchData(reviews.value.current_page - 1)
  }
}

onMounted(() => fetchData())
</script>

