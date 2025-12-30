<script setup lang="ts">
import { computed, defineProps, onMounted, PropType, ref } from 'vue';
import {
  CreatePostRequest,
  InteractiveVideoTask,
  TravisGoPostProps,
  TravisGoPostType,
} from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import TravisGoPost from '@/components/interactiveVideo/viewer/TravisGoPost.vue';
import { store } from '@/store';
import ErrorMessage from '@/components/ErrorMessage.vue';

const props = defineProps({
  task: {
    type: Object as PropType<InteractiveVideoTask>,
    required: true,
  },
});

const postsFake = ref<TravisGoPostProps[]>([
  {
    id: 'fakeid',
    type: 'meta',
    authorId: 'anna',
    authorName: 'Anna',
    description: "This is anna's post",
    start: 0,
  },
  {
    id: 'fakeid2',
    type: 'image',
    authorId: 'kevin',
    authorName: 'Kevin',
    description: "This is Kevin's post",
    start: 0,
  },
]);

const loadPostsError = ref<string | undefined>();
function loadPosts() {
  console.log(store);
  const x = store
    .dispatch('lernmodule-plugin/travis-go-posts/loadAll')
    .then((result) => console.log('result of travis-go-posts/loadAll', result))
    .catch((error) => {
      loadPostsError.value = error.toString();
      console.error('error', error);
    });
  console.log(x);
  /* eslint-disable-next-line no-debugger */
  // debugger;
}
const posts = computed(
  () => store.getters['lernmodule-plugin/travis-go-posts']
);
onMounted(() => loadPosts());

const searchInput = ref<string>('');
const postDescriptionInput = ref<string>('');
const postTypeInput = ref<TravisGoPostType>('text');
const createPostError = ref<string | undefined>();
function onClickSearch() {}

async function createPost(post: { attributes: CreatePostRequest }) {
  return store.dispatch('lernmodule-plugin/travis-go-posts/create', post);
}
function onClickPost() {
  console.log('onClickPost');
  if (!props.task) {
    throw new Error('task not provided');
  }
  const res = createPost({
    attributes: {
      description: postDescriptionInput.value,
      post_type: postTypeInput.value,
      start_time: 0, // TODO Implement start/end time inputs.
      video_id: '24', // TODO plumb video id and type into task or editor store or something.
      video_type: 'cw_blocks', // TODO plumb video type (cw_blocks or lernmodule_module)
    },
  })
    .then((result) => console.log('result of create post', result))
    .catch((error) => {
      console.error('error', error);
      createPostError.value = error;
    });
  return res;
}
</script>

<template>
  <div v-if="task.travisGoSettings.enabled" class="travis-go-main">
    <div class="travis-go-left-column">
      <VideoPlayer :task="task" />
      <div class="annotation-controls">
        <button class="button date">{{ $gettext('Start') }}</button>
        <button class="button date">{{ $gettext('End') }}</button>
        <select v-model="postTypeInput">
          <option value="meta">Meta</option>
          <option value="image">Image</option>
          <option value="audio">Audio</option>
          <option value="text">Text</option>
        </select>
      </div>
      <StudipWysiwyg insertHtmlComment v-model="postDescriptionInput" />
      <button @click="onClickPost" class="button">
        {{ $gettext('Kommentar posten') }}
      </button>
      <ErrorMessage
        style="max-height: unset"
        :error="createPostError"
        v-if="createPostError"
      />
      <div class="travis-go-participants-list">
        <a>@Anna</a>
        <a>@Kevin</a>
      </div>
    </div>
    <div class="travis-go-right-column">
      <nav class="search-bar">
        <input type="text" v-model="searchInput" />
        <button class="button h5p-search" @click="onClickSearch" />
      </nav>
      <section class="project-title-and-description">
        <h3>{{ $gettext('Projekttitel') }}</h3>
        <p>
          {{ task.travisGoSettings.projectTitle }}
        </p>
        <h3>{{ $gettext('Projektbeschreibung') }}</h3>
        <p>
          {{ task.travisGoSettings.projectDescription }}
        </p>
      </section>
      <section class="travis-go-posts">
        <pre>{{ { posts: posts, postsFake } }}</pre>
        <TravisGoPost
          v-for="(post, index) in postsFake"
          :key="post.id"
          :class="{
            odd: index % 2 === 0,
          }"
          :post="post"
        />
      </section>
      <ErrorMessage :error="loadPostsError" v-if="loadPostsError" />
    </div>
  </div>
  <VideoPlayer v-else :task="task" />
</template>

<style scoped lang="scss">
.travis-go-main {
  h3 {
    margin-top: 0;
    margin-bottom: 0.45em;
  }
  p {
    margin-bottom: 0.45em;
  }
  display: flex;
  flex-wrap: wrap;

  gap: 10px;
  > .travis-go-left-column {
    flex: 1;
    min-width: calc(min(400px, 100%));
  }
  > .travis-go-right-column {
    flex: 1;
  }
}

.travis-go-posts {
  .travis-go-post.odd {
    background: var(--color--gray-6);
  }
}
.travis-go-participants-list {
  background: var(--color--gray-6);
  display: flex;
  gap: 10px;
  padding: 10px;
  align-items: center;
  a {
    color: black;
  }
}
.search-bar {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 10px;
  width: 100%;
  input[type='text'] {
    flex-grow: 1;
    align-self: stretch;
  }
  button.h5p-search {
    min-width: unset;
    width: 0;
    margin: 0;
    padding: 5px 15px;
  }
}
</style>
