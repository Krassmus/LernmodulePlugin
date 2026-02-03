<script setup lang="ts">
import {
  computed,
  defineProps,
  nextTick,
  onMounted,
  PropType,
  ref,
  watch,
} from 'vue';
import {
  CreatePostRequest,
  InteractiveVideoTask,
  TravisGoComment,
  travisGoCommentSchema,
  TravisGoPost,
  travisGoPostSchema,
  TravisGoPostType,
  UpdatePostRequest,
} from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import TravisGoPostComponent from '@/components/interactiveVideo/viewer/TravisGoPost.vue';
import { store, taskEditorStore } from '@/store';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { SafeParseError } from 'zod';
import strings from '@/components/interactiveVideo/strings';
import { User } from '@/php-integration';
import { formatVideoTimestamp } from '@/components/interactiveVideo/formatVideoTimestamp';
import { $gettext } from '@/language/gettext';

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
  seekVideo(time);
}

function searchUserComments(userId: string) {
  const user = getUserById(userId);
  if (!user) {
    throw new Error('User not found by id ' + userId);
  }
  searchInput.value = '@' + user.attributes.username;
}
function searchPostAuthor(post: TravisGoPost) {
  searchUserComments(post.attributes.mk_user_id);
}
function searchPostType(post: TravisGoPost) {
  searchInput.value = post.attributes.post_type;
}
function searchCommentAuthor(comment: TravisGoComment) {
  searchUserComments(comment.attributes.mk_user_id);
}
async function deleteComment(id: string) {
  const prompt = $gettext('Kommentar löschen');
  const confirmed = window.confirm(prompt);
  if (!confirmed) {
    console.info('Delete prompt canceled by user');
    return;
  }
  try {
    await store.dispatch('lernmodule-plugin/travis-go-comments/delete', {
      id,
    });
    window.STUDIP.Report.success($gettext('Der Kommentar wurde gelöscht.'));
  } catch (error: unknown) {
    window.STUDIP.Report.error(
      $gettext('Der Kommentar konnte nicht gelöscht werden.'),
      [JSON.stringify(error, null, 2)]
    );
    console.error(error);
  }
}

const editedPostId = ref<string | undefined>();
function startEditingPost(post: TravisGoPost): void {
  if (
    postWysiwygInput.value !== '' ||
    startTimeInput.value !== undefined ||
    endTimeInput.value !== undefined
  ) {
    const confirmed = confirm(
      $gettext('Die Inhalte des Post-Editors werden gelöscht.')
    );
    if (!confirmed) {
      return;
    }
  }
  editedPostId.value = post.attributes.id;
  postWysiwygInput.value = post.attributes.contents;
  postTypeInput.value = post.attributes.post_type;
  startTimeInput.value = post.attributes.start_time;
  endTimeInput.value = post.attributes.end_time ?? undefined;
  // Focus the wysiwyg text area.
  nextTick(() => {
    // Use nextTick to allow for the editor contents to be updated.
    const wysiwygEl = postWysiwygInputElement.value!.$el;
    const editor = window.STUDIP.wysiwyg.getEditor(wysiwygEl);
    editor!.editing!.view.focus();
    // Put the cursor at the end.
    editor!.model.change((writer) => {
      writer.setSelection(
        writer.createPositionAt(editor!.model.document.getRoot()!, 'end')
      );
    });
  });
}

function resetPostEditor() {
  if (
    postWysiwygInput.value !== '' ||
    startTimeInput.value !== undefined ||
    endTimeInput.value !== undefined
  ) {
    const confirmed = confirm(
      $gettext('Die Inhalte des Post-Editors werden gelöscht.')
    );
    if (!confirmed) {
      return;
    }
  }
  editedPostId.value = undefined;
  postWysiwygInput.value = '';
  postTypeInput.value = 'text';
  startTimeInput.value = undefined;
  endTimeInput.value = undefined;
}

async function deletePost(id: string) {
  const prompt = $gettext('Post löschen');
  const confirmed = window.confirm(prompt);
  if (!confirmed) {
    console.info('Delete prompt canceled by user');
    return;
  }
  try {
    await store.dispatch('lernmodule-plugin/travis-go-posts/delete', {
      id,
    });
    window.STUDIP.Report.success($gettext('Der Post wurde gelöscht.'));
  } catch (error: unknown) {
    window.STUDIP.Report.error(
      $gettext('Der Post konnte nicht gelöscht werden.'),
      [JSON.stringify(error, null, 2)]
    );
    console.error(error);
  }
}

function seekVideo(time: number) {
  videoPlayer.value!.player!.currentTime(time);
}

const currentTime = ref(0);
function onTimeUpdate(time: number) {
  currentTime.value = time;
}

const startTimeInput = ref<number | undefined>();
function onClickStartTime() {
  if (startTimeInput.value !== undefined) {
    seekVideo(startTimeInput.value);
  } else {
    startTimeInput.value = currentTime.value;
    if (
      endTimeInput.value !== undefined &&
      endTimeInput.value < startTimeInput.value
    ) {
      endTimeInput.value = undefined;
    }
  }
}
function clearStartTime() {
  startTimeInput.value = undefined;
}

const endTimeInput = ref<number | undefined>();
function onClickEndTime() {
  if (endTimeInput.value) {
    seekVideo(endTimeInput.value);
  } else {
    endTimeInput.value = currentTime.value;
    if (
      startTimeInput.value !== undefined &&
      endTimeInput.value < startTimeInput.value
    ) {
      startTimeInput.value = undefined;
    }
  }
}
function clearEndTime() {
  endTimeInput.value = undefined;
}

function loadCurrentUser() {
  store.dispatch('users/loadById', { id: window.STUDIP.USER_ID });
}

const loadPostsError = ref<string | undefined>();
function loadPosts() {
  if (!taskEditorStore.taskSaveLocation) {
    throw new Error('taskEditorStore.taskSaveLocation is missing.');
  }
  const x = store
    .dispatch('lernmodule-plugin/travis-go-posts/loadWhere', {
      filter: {
        video_id: taskEditorStore.taskSaveLocation.id,
        video_type: taskEditorStore.taskSaveLocation.type,
      },
      options: {
        include: 'user,comments,comments.user',
        'fields[users]': 'formatted-name,username',
      },
    })
    .then((result) => console.log('result of travis-go-posts/loadAll', result))
    .catch((error) => {
      loadPostsError.value = error;
      console.error('error', error);
    });
  /* eslint-disable-next-line no-debugger */
  // debugger;
}
const rawPosts = computed<unknown[]>(
  () => store.getters['lernmodule-plugin/travis-go-posts/all']
);
type ParsedPosts = [TravisGoPost[], SafeParseError<TravisGoPost>[]];
const postParseResults = computed<ParsedPosts>(() => {
  const errors: SafeParseError<TravisGoPost>[] = [];
  const successes: TravisGoPost[] = [];
  for (let rawVal of rawPosts.value) {
    const parsed = travisGoPostSchema.safeParse(rawVal);
    if (parsed.success) {
      successes.push(parsed.data);
    } else {
      errors.push(parsed);
    }
  }
  return [successes, errors];
});
const parsedPosts = computed<TravisGoPost[]>(() => postParseResults.value[0]);
const erroredPosts = computed<SafeParseError<TravisGoPost>[]>(
  () => postParseResults.value[1]
);

// Posts that match the search entered by the user
const filteredAndSortedPosts = computed<TravisGoPost[]>(() => {
  return parsedPosts.value
    .filter((post) => postMatchesSearch(post, searchInput.value))
    .toSorted((a, b) => a.attributes.start_time - b.attributes.start_time);
});

// True iff post matches the given search query string
function postMatchesSearch(post: TravisGoPost, query: string): boolean {
  if (!query) {
    return true;
  } else {
    const author = getUserById(post.attributes.mk_user_id);
    const texts = [
      post.attributes.contents,
      post.attributes.post_type,
      author?.attributes.username ?? '',
      author?.attributes['formatted-name'] ?? '',
    ];
    const canonicalQuery = canonicalize(query);
    const comments = commentsForPost(post);
    return (
      texts.some((text) => canonicalize(text).includes(canonicalQuery)) ||
      comments.some((comment) => commentMatchesSearch(comment, canonicalQuery))
    );
  }
}
function commentMatchesSearch(
  comment: TravisGoComment,
  canonicalQuery: string
): boolean {
  const author = getUserById(comment.attributes.mk_user_id);
  const texts = [
    comment.attributes.contents,
    author?.attributes.username ?? '',
    author?.attributes['formatted-name'] ?? '',
  ];
  return texts.some((text) => canonicalize(text).includes(canonicalQuery));
}

function canonicalize(str: string): string {
  return str
    .toLowerCase()
    .replaceAll(/[.,/#!$%^&@*;:{}=\-_`~()]/g, '')
    .trim();
}

const parsedComments = computed<TravisGoComment[]>(() => {
  return store.getters['lernmodule-plugin/travis-go-comments/all'].flatMap(
    (record: unknown) => {
      const parsedComment = travisGoCommentSchema.safeParse(record);
      if (parsedComment.success) {
        return parsedComment.data;
      } else {
        return [];
      }
    }
  );
});
const erroredComments = computed<unknown[]>(() => {
  return store.getters['lernmodule-plugin/travis-go-comments/all'].flatMap(
    (record: unknown) => {
      const parsedComment = travisGoCommentSchema.safeParse(record);
      if (!parsedComment.success) {
        return { record, error: parsedComment.error };
      } else {
        return [];
      }
    }
  );
});
watch(
  () => erroredComments.value,
  () => {
    if (erroredComments.value.length === 0) {
      return;
    }
    console.error(`${erroredComments.value} Comment(s) could not be loaded`);
    console.error(erroredComments.value);
  }
);

function commentsForPost(post: TravisGoPost): TravisGoComment[] {
  return parsedComments.value.filter(
    (comment) => comment.attributes.post_id === post.attributes.id
  );
}

const participantsIds = computed<string[]>(() => {
  const postUserIds = parsedPosts.value.map((post) => {
    return post.attributes.mk_user_id;
  });
  const commentUserIds = parsedComments.value
    .filter((comment) =>
      parsedPosts.value.some(
        (post) => post.attributes.id === comment.attributes.post_id
      )
    )
    .map((comment) => comment.attributes.mk_user_id);
  // Use Set to remove duplicates
  return [...new Set(postUserIds.concat(commentUserIds))];
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
const postWysiwygInputElement = ref<
  InstanceType<typeof StudipWysiwyg> | undefined
>();
const postTypeInput = ref<TravisGoPostType>('image');
const createPostError = ref<string | undefined>();
interface WysiwygFocusEvent {
  event: unknown;
  name: unknown;
  value: boolean;
}
function onFocusPostWysiwyg(evt: WysiwygFocusEvent) {
  if (!evt.value) {
    return;
  }
  if (startTimeInput.value === undefined) {
    startTimeInput.value = currentTime.value;
  }
}

function cancelSearch() {
  searchInput.value = '';
}

async function createPost(post: { attributes: CreatePostRequest }) {
  return await store.dispatch('lernmodule-plugin/travis-go-posts/create', post);
}
async function updatePost(post: UpdatePostRequest) {
  return await store.dispatch('lernmodule-plugin/travis-go-posts/update', post);
}
function submitNewPost() {
  console.log('submitNewPost');
  if (!props.task) {
    throw new Error('task not provided');
  }
  if (!taskEditorStore.taskSaveLocation) {
    throw new Error('taskEditorStore.taskSaveLocation is missing.');
  }
  const res = createPost({
    attributes: {
      contents: postWysiwygInput.value,
      post_type: postTypeInput.value,
      start_time: startTimeInput.value ?? 0,
      end_time: endTimeInput.value ?? null,
      video_id: taskEditorStore.taskSaveLocation.id,
      video_type: taskEditorStore.taskSaveLocation.type,
    },
  })
    .then((result) => {
      console.log('result of create post', result);
      postWysiwygInput.value = '';
      startTimeInput.value = undefined;
      endTimeInput.value = undefined;
      createPostError.value = undefined;
    })
    .catch((error) => {
      console.error('error', error);
      createPostError.value = error;
      window.STUDIP.Report.error(
        $gettext('Der Post konnte nicht gepostet werden.'),
        [JSON.stringify(error, null, 2)]
      );
    });
  return res;
}

function submitEditedPost() {
  console.log('submitNewPost');
  if (!props.task) {
    throw new Error('task not provided');
  }
  if (!taskEditorStore.taskSaveLocation) {
    throw new Error('taskEditorStore.taskSaveLocation is missing.');
  }
  if (editedPostId.value === undefined) {
    throw new Error('editedPostId is undefined');
  }
  const res = updatePost({
    id: editedPostId.value,
    attributes: {
      contents: postWysiwygInput.value,
      post_type: postTypeInput.value,
      start_time: startTimeInput.value ?? 0,
      end_time: endTimeInput.value ?? null,
    },
  })
    .then((result) => {
      console.log('result of update post', result);
      editedPostId.value = undefined;
      postWysiwygInput.value = '';
      startTimeInput.value = undefined;
      endTimeInput.value = undefined;
      createPostError.value = undefined;
    })
    .catch((error) => {
      console.error('error', error);
      createPostError.value = error;
      window.STUDIP.Report.error(
        $gettext('Der Post konnte nicht geupdated werden.'),
        [JSON.stringify(error, null, 2)]
      );
    });
  return res;
}
</script>

<template>
  <div v-if="task.travisGoSettings.enabled" class="travis-go-main">
    <div class="travis-go-left-column">
      <VideoPlayer @timeupdate="onTimeUpdate" :task="task" ref="videoPlayer" />
      <div class="annotation-controls">
        <button class="button date time-input" @click="onClickStartTime">
          <template v-if="startTimeInput !== undefined">
            {{ formatVideoTimestamp(startTimeInput, false, ':') }}
            <button class="small-button cancel" @click.stop="clearStartTime" />
          </template>
          <template v-else>
            {{ $gettext('Start') }}
          </template>
        </button>
        <button class="button date time-input" @click="onClickEndTime">
          <template v-if="endTimeInput !== undefined">
            {{ formatVideoTimestamp(endTimeInput, false, ':') }}
            <button class="small-button cancel" @click.stop="clearEndTime" />
          </template>
          <template v-else>
            {{ $gettext('End') }}
          </template>
        </button>
        <select class="post-type-input" v-model="postTypeInput">
          <option value="image">{{ $gettext('Bild') }}</option>
          <option value="audio">Audio</option>
          <option value="text">Text</option>
          <option value="meta">Meta</option>
        </select>
        <div
          class="tooltip tooltip-icon"
          :data-tooltip="strings.postTypeTooltip"
        />
      </div>
      <StudipWysiwyg
        insertHtmlComment
        v-model="postWysiwygInput"
        ref="postWysiwygInputElement"
        @focus="onFocusPostWysiwyg"
      />
      <button
        v-if="editedPostId !== undefined"
        @click="resetPostEditor"
        type="button"
        class="button"
      >
        {{ $gettext('Zurücksetzen') }}
      </button>
      <button
        @click="
          editedPostId !== undefined ? submitEditedPost() : submitNewPost()
        "
        class="button"
        :disabled="postWysiwygInput.trim().length === 0"
      >
        {{
          editedPostId !== undefined
            ? $gettext('Post updaten')
            : $gettext('Post abschicken')
        }}
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
        <a
          v-for="id in participantsIds"
          :key="id"
          href="#"
          @click.prevent="searchUserComments(id)"
        >
          {{ getUserById(id)?.attributes['formatted-name'] }}
        </a>
      </div>
    </div>
    <div class="travis-go-right-column">
      <nav class="search-bar">
        <input
          type="text"
          role="searchbox"
          :placeholder="$gettext('Suche')"
          v-model="searchInput"
        />
        <button
          v-if="searchInput !== ''"
          class="button h5p-search cancel"
          :title="$gettext('Suche zurücksetzen')"
          @click="cancelSearch"
        ></button>
        <button class="button h5p-search" />
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
          v-for="(post, index) in filteredAndSortedPosts"
          :key="post.attributes.id"
        >
          <TravisGoPostComponent
            :post="post"
            :comments="commentsForPost(post)"
            @clickTimestamp="onClickPostTimestamp"
            @startEditingPost="startEditingPost"
            @deletePost="deletePost"
            @deleteComment="deleteComment"
            @clickPostAuthorName="searchPostAuthor"
            @clickPostType="searchPostType"
            @clickCommentAuthorName="searchCommentAuthor"
            :class="{
              odd: index % 2 === 0,
            }"
          />
        </template>
        <pre style="white-space: pre-wrap" v-if="false && debug">
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
      <ErrorMessage
        v-if="erroredPosts.length > 0"
        :error="`${erroredPosts.length} post(s) could not be loaded`"
      ></ErrorMessage>
      <ErrorMessage
        v-if="erroredComments.length > 0"
        :error="`${erroredComments.length} comment(s) could not be loaded`"
      ></ErrorMessage>
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
    min-width: calc(min(300px, 100%));
  }

  .annotation-controls {
    display: flex;
    align-items: center;
    margin-top: 0.5em;
    margin-bottom: 0.5em;
    gap: 0.5em;
    > * {
      margin: 0;
    }
    button.time-input {
      display: flex;
      align-items: center;
      gap: 0.5em;
      /* Fixed height it doesn't change height when the 'X' button is visible */
      height: 2.5em;
    }
    .post-type-input {
      align-self: stretch;
      height: auto;
    }
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
