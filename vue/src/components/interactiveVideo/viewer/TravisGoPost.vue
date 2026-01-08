<template>
  <div class="travis-go-post" :data-post-type="post.post_type">
    <h4 class="travis-go-post-heading">
      [<span class="video-timestamp"
        ><a
          href="javascript:undefined"
          @click="onClickTimestamp(post.start_time)"
          >{{ formatVideoTimestamp(post.start_time, false, ':') }}</a
        >
        <span v-if="post.end_time">
          —
          <a
            href="javascript:undefined"
            @click="onClickTimestamp(post.end_time)"
          >
            {{ formatVideoTimestamp(post.end_time, false, ':') }}
          </a></span
        ></span
      >
      <span class="post-type">{{ post.post_type }}</span>
      <span> </span>
      <a :href="userUrl">@{{ userFormattedName }}</a
      >]
    </h4>
    <p class="travis-go-post-contents" v-html="contentsPurified"></p>
  </div>
</template>
<style scoped lang="scss">
.travis-go-post {
  display: grid;
  grid-template-columns: min-content 1fr;
  grid-template-rows: min-content 1fr;
  grid-template-areas:
    'icon heading'
    '.    contents';
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
  .travis-go-post-contents {
    grid-area: contents;
    margin-bottom: 0;
  }
  .video-timestamp,
  .post-type {
    margin-right: 0.25em;
  }
  margin: 0;
  padding: 5px;
}
</style>
<style lang="scss">
/* Prevent large images from messing up the page layout. */
.travis-go-post-contents {
  overflow: hidden;
  img {
    object-fit: contain;
    width: auto;
    max-height: 20vh;
  }
}
</style>

<script setup lang="ts">
import { computed, defineProps, defineEmits, PropType } from 'vue';
import { TravisGoPostProps } from '@/models/InteractiveVideoTask';
import { formatVideoTimestamp } from '@/components/interactiveVideo/formatVideoTimestamp';
import { store } from '@/store';
import DOMPurify from 'dompurify';
import { User } from '@/php-integration';
const props = defineProps({
  post: {
    type: Object as PropType<TravisGoPostProps>,
    required: true,
  },
});
const contentsPurified = computed(() =>
  DOMPurify.sanitize(props.post?.contents, {
    USE_PROFILES: { html: true },
  })
);
const user = computed<User | undefined>(() =>
  store.getters['users/byId']({ id: props.post?.mk_user_id })
);
const userFormattedName = computed<string | undefined>(
  () => user.value?.attributes?.['formatted-name']
);

const userUrl = computed<string>(() => {
  const username = user.value?.attributes?.['username'];
  if (username) {
    return window.STUDIP.URLHelper.getURL('dispatch.php/profile', { username });
  } else {
    return '';
  }
});

const emit = defineEmits({
  clickTimestamp(payload: number) {
    return true;
  },
});

function onClickTimestamp(time: number) {
  if (!props.post) {
    throw new Error('Prop "post" is missing');
  }
  emit('clickTimestamp', time);
}
</script>
