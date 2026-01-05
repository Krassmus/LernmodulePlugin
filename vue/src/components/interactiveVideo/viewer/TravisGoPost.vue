<template>
  <div class="travis-go-post" :data-post-type="post.post_type">
    <h4 class="travis-go-post-heading">
      [{{ formatVideoTimestamp(post.start_time) }}
      <span class="post-type">{{ post.post_type }}</span>
      <span> </span>
      <a :href="userUrl">@{{ userFormattedName }}</a
      >]
    </h4>
    <p class="travis-go-post-description" v-html="contentsPurified"></p>
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
  grid-column-gap: 5px;
  grid-row-gap: 0.45em;
  align-items: start;
  &::before {
    grid-area: icon;
  }
  .travis-go-post-heading {
    grid-area: heading;
    margin-top: 0;
    margin-bottom: 0;
  }
  .travis-go-post-description {
    grid-area: description;
    margin-bottom: 0;
  }
  .post-type {
    margin-right: 0.25em;
  }
  margin: 0;
  padding: 5px;
}
</style>

<script setup lang="ts">
import { computed, defineProps, PropType } from 'vue';
import { TravisGoPostProps } from '@/models/InteractiveVideoTask';
import { formatVideoTimestamp } from '@/components/interactiveVideo/formatVideoTimestamp';
import { store } from '@/store';
import DOMPurify from 'dompurify';
const props = defineProps({
  post: {
    type: Object as PropType<TravisGoPostProps>,
    required: true,
  },
});
const contentsPurified = computed(() =>
  DOMPurify.sanitize(props.post?.description, {
    USE_PROFILES: { html: true },
  })
);
const user = computed(() =>
  store.getters['users/byId']({ id: props.post?.mk_user_id })
);
const userFormattedName = computed(
  () => user.value?.attributes?.['formatted-name']
);

const userUrl = computed(() => {
  const username = user.value?.attributes?.['username'];
  return window.STUDIP.URLHelper.getURL('dispatch.php/profile', { username });
});
</script>
