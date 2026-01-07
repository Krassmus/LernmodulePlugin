<script setup lang="ts">
import { computed, defineProps, onMounted, PropType, ref } from 'vue';
import {
  CreatePostRequest,
  InteractiveVideoTask,
  TravisGoPostProps,
  travisGoPostSchema,
  TravisGoPostType,
} from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import TravisGoPost from '@/components/interactiveVideo/viewer/TravisGoPost.vue';
import { store } from '@/store';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { SafeParseReturnType } from 'zod';
import { v4 } from 'uuid';
import strings from '@/components/interactiveVideo/strings';
import { User } from '@/php-integration';
import { formatVideoTimestamp } from '@/components/interactiveVideo/formatVideoTimestamp';

const props = defineProps({
  task: {
    type: Object as PropType<InteractiveVideoTask>,
    required: true,
  },
});

const debug = window.STUDIP.LernmoduleVueJS.LERNMODULE_DEBUG;

const videoPlayer = ref<InstanceType<typeof VideoPlayer> | undefined>(
  undefined
);

function onClickPostTimestamp(time: number) {
  videoPlayer.value!.player!.currentTime(time);
}

const currentTime = ref(0);
function onTimeUpdate(time: number) {
  currentTime.value = time;
}
const startTimeInput = ref<number | undefined>();
function onClickStart() {
  startTimeInput.value = currentTime.value;
}
const endTimeInput = ref<number | undefined>();
function onClickEnd() {
  endTimeInput.value = currentTime.value;
}

function loadCurrentUser() {
  store.dispatch('users/loadById', { id: window.STUDIP.USER_ID });
}

const loadPostsError = ref<string | undefined>();
function loadPosts() {
  console.log(store);
  const x = store
    .dispatch('lernmodule-plugin/travis-go-posts/loadWhere', {
      filter: {
        // TODO Plumb video_id and video_type
        video_id: '24',
        video_type: 'cw_blocks',
      },
      options: {
        include: 'user',
        'fields[users]': 'formatted-name,username',
      },
    })
    .then((result) => console.log('result of travis-go-posts/loadAll', result))
    .catch((error) => {
      loadPostsError.value = error;
      console.error('error', error);
    });
  console.log(x);
  /* eslint-disable-next-line no-debugger */
  // debugger;
}
const rawPosts = computed<unknown>(
  () => store.getters['lernmodule-plugin/travis-go-posts/all']
);
type ParsedPost = SafeParseReturnType<TravisGoPostProps, TravisGoPostProps>;
const parsedPosts = computed<ParsedPost[]>(() => {
  const raw = rawPosts.value as {
    attributes: unknown[];
  }[];
  return raw
    .map((rawVal) => travisGoPostSchema.safeParse(rawVal.attributes))
    .toSorted((a, b) => {
      if (a.success && !b.success) {
        return -1;
      } else if (!a.success && b.success) {
        return 1;
      } else if (a.success && b.success) {
        return a.data.start_time - b.data.start_time;
      } else {
        return 0;
      }
    });
});
const participantsIds = computed<string[]>(() => {
  const ids = parsedPosts.value.flatMap((post) => {
    if (post.success) {
      return [post.data.mk_user_id];
    } else {
      return [];
    }
  });
  return [...new Set(ids)];
});
function urlForUserId(id: string): string {
  const user = getUserById(id);
  if (user) {
    return window.STUDIP.URLHelper.getURL('dispatch.php/profile', {
      username: user.attributes.username,
    });
  } else {
    return '';
  }
}
function getUserById(id: string): User | undefined {
  return store.getters['users/byId']({ id });
}

onMounted(() => {
  loadPosts();
  loadCurrentUser();
});

const searchInput = ref<string>('');
const postWysiwygInput = ref<string>('');
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
      contents: postWysiwygInput.value,
      post_type: postTypeInput.value,
      start_time: startTimeInput.value ?? 0, // TODO Implement start/end time inputs.
      end_time: endTimeInput.value ?? null, // TODO Implement start/end time inputs.
      video_id: '24', // TODO plumb video id and type into task or editor store or something.
      video_type: 'cw_blocks', // TODO plumb video type (cw_blocks or lernmodule_module)
    },
  })
    .then((result) => {
      console.log('result of create post', result);
      postWysiwygInput.value = '';
      createPostError.value = undefined;
    })
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
      <VideoPlayer @timeupdate="onTimeUpdate" :task="task" ref="videoPlayer" />
      <div class="annotation-controls">
        <button class="button date" @click="onClickStart">
          <template v-if="startTimeInput">
            {{ formatVideoTimestamp(startTimeInput) }}
          </template>
          <template v-else>
            {{ $gettext('Start') }}
          </template>
        </button>
        <button class="button date" @click="onClickEnd">
          <template v-if="endTimeInput">
            {{ formatVideoTimestamp(endTimeInput) }}
          </template>
          <template v-else>
            {{ $gettext('End') }}
          </template>
        </button>
        <select v-model="postTypeInput">
          <option value="meta">Meta</option>
          <option value="image">Image</option>
          <option value="audio">Audio</option>
          <option value="text">Text</option>
        </select>
      </div>
      <StudipWysiwyg insertHtmlComment v-model="postWysiwygInput" />
      <button @click="onClickPost" class="button">
        {{ $gettext('Kommentar posten') }}
      </button>
      <ErrorMessage
        style="max-height: unset"
        :error="
          debug
            ? `${strings.couldNotSendPostError} Error: ${JSON.stringify(
                createPostError,
                null,
                2
              )}`
            : strings.couldNotSendPostError
        "
        v-if="createPostError"
      />
      <div class="travis-go-participants-list">
        <a v-for="id in participantsIds" :key="id" :href="urlForUserId(id)">
          {{ getUserById(id)?.attributes['formatted-name'] }}
        </a>
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
        <template
          v-for="(post, index) in parsedPosts"
          :key="post?.data?.id ?? v4()"
        >
          <TravisGoPost
            v-if="post.success"
            :post="post.data"
            @clickTimestamp="onClickPostTimestamp"
            :class="{
              odd: index % 2 === 0,
            }"
          />
          <ErrorMessage
            v-else
            class="travis-go-post"
            :class="{
              odd: index % 2 === 0,
            }"
            :error="
              debug
                ? `${strings.postCouldNotBeParsedError} Error: ${post.error}`
                : strings.postCouldNotBeParsedError
            "
          />
        </template>
        <pre
          style="white-space: pre-wrap"
          v-if="debug && parsedPosts.some((post) => post.error)"
        >
          {{ { rawPosts } }}
        </pre>
      </section>
      <ErrorMessage
        v-if="loadPostsError"
        :error="
          debug
            ? `${strings.couldNotLoadPostsError} Error: ${JSON.stringify(
                loadPostsError,
                null,
                2
              )}`
            : strings.couldNotLoadPostsError
        "
      />
    </div>
  </div>
  <VideoPlayer
    v-else
    @timeupdate="onTimeUpdate"
    :task="task"
    ref="videoPlayer"
  />
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
