<template>
  <div class="travis-go-post">
    <h4>
      [{{ formatVideoTimestamp(post.start) }}
      <span class="post-type">{{ post.type }}</span> @{{ post.authorName }}]
    </h4>
    {{ post.description }}
  </div>
</template>
<style scoped lang="scss">
.travis-go-post {
  h4 {
    margin-top: 5px;
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
