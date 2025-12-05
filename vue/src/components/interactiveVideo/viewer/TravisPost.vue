<template>
  <div class="travis-post" :data-type="post.type">
    <span class="icon" :data-type="post.type" />
    <div class="contents">
      <h4>
        [{{ formatVideoTimestamp(post.start) }}
        <span class="post-type">{{ post.type }}</span> @{{ post.authorName }}]
      </h4>
      {{ post.description }}
    </div>
  </div>
</template>
<style scoped lang="scss"></style>

<script setup lang="ts">
import '@/assets/travis-post.scss';
import { defineProps, PropType } from 'vue';
import { Post } from '@/models/InteractiveVideoTask';
import { formatVideoTimestamp } from '@/components/interactiveVideo/formatVideoTimestamp';
defineProps({
  post: {
    type: Object as PropType<Post>,
    required: true,
  },
});
</script>
