<template>
  <div class="travis-go-post" :data-post-type="post.type">
    <h4 class="travis-go-post-heading">
      [{{ formatVideoTimestamp(post.start) }}
      <span class="post-type">{{ post.type }}</span> @{{ post.authorName }}]
    </h4>
    <p class="travis-go-post-description">
      {{ post.description }}
    </p>
  </div>
</template>
<style scoped lang="scss">
.travis-go-post {
  display: grid;
  grid-template-columns: min-content 1fr;
  grid-template-rows: min-content 1fr;
  grid-template-areas:
    'icon heading'
    '.    description';
  grid-gap: 5px;
  align-items: start;
  &::before {
    grid-area: icon;
  }
  .travis-go-post-heading {
    grid-area: heading;
    margin-top: 0;
  }
  .travis-go-post-description {
    grid-area: description;
  }
  margin: 0;
  padding: 5px;
}
</style>

<script setup lang="ts">
import { defineProps, PropType } from 'vue';
import { TravisGoPostProps } from '@/models/InteractiveVideoTask';
import { formatVideoTimestamp } from '@/components/interactiveVideo/formatVideoTimestamp';
defineProps({
  post: {
    type: Object as PropType<TravisGoPostProps>,
    required: true,
  },
});
</script>
