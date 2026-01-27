<template>
  <div class="travis-go-post" :data-post-type="post.attributes.post_type">
    <h4 class="travis-go-post-heading">
      [<span class="video-timestamp"
        ><a
          href="#"
          @click.prevent="onClickTimestamp(post.attributes.start_time)"
          >{{ formatVideoTimestamp(post.attributes.start_time, false, ':') }}</a
        >
        <span v-if="post.attributes.end_time">
          —
          <a
            href="#"
            @click.prevent="onClickTimestamp(post.attributes.end_time)"
          >
            {{ formatVideoTimestamp(post.attributes.end_time, false, ':') }}
          </a></span
        ></span
      >
      <span class="post-type">{{ post.attributes.post_type }}</span>
      <span> </span>
      <a href="#" @click.prevent="$emit('clickPostAuthorName', post)"
        >@{{ postAuthorFormattedName }}</a
      >]
      <StudipActionMenu
        :title="$gettext('Aktionen')"
        :items="postActionMenuItems()"
        :collapseAt="true"
        class="travis-go-post-action-menu"
        @deletePost="deletePost(post.attributes.id)"
        @startCommentingPost="startCommentingPost"
      />
    </h4>
    <div class="travis-go-post-contents-container">
      <p class="travis-go-post-contents" v-html="contentsPurified"></p>
      <section
        class="travis-go-comments"
        v-if="isCommenting || comments.length > 0"
      >
        <form class="default" @submit.prevent>
          <fieldset class="collapsable collapsed">
            <legend>
              <template v-if="comments.length > 0">
                {{
                  $gettextInterpolate(
                    $ngettext(
                      '%{ numberOfComments } Kommentar',
                      '%{ numberOfComments } Kommentare',
                      comments.length
                    ),
                    { numberOfComments: comments.length }
                  )
                }}
              </template>
              <template v-else>{{ $gettext('Kommentieren') }}</template>
            </legend>
            <div class="travis-go-comments-list">
              <p
                class="travis-go-comment"
                v-for="comment in comments"
                :key="comment.attributes.id"
              >
                <a
                  class="travis-go-comment-author"
                  href="#"
                  @click.prevent="$emit('clickCommentAuthorName', comment)"
                  >@{{ commentAuthorName(comment) }}</a
                >
                {{ comment.attributes.contents }}
                <StudipActionMenu
                  v-if="commentActionMenuItems(comment).length > 0"
                  :title="$gettext('Aktionen')"
                  :items="commentActionMenuItems(comment)"
                  :collapseAt="true"
                  class="travis-go-comment-action-menu"
                  @deleteComment="deleteComment(comment.attributes.id)"
                  @startEditingComment="startEditingComment(comment)"
                />
              </p>
            </div>
            <!--  Wrapped in a div so the form will be hidden when the enclosing
                  fieldset is collapsed - otherwise the css rule display: flex
                  will override the fieldset's rule '> * { display: none }' -->
            <div>
              <form
                class="comment-editor"
                @submit.prevent="
                  isEditingComment ? submitEditedComment() : submitComment()
                "
              >
                <input
                  type="text"
                  :placeholder="$gettext('Schreibe einen Kommentar...')"
                  v-model="commentEditorInput"
                  ref="commentEditorInputElement"
                />
                <button
                  v-if="isEditingComment"
                  class="button cancel cancel-editing-comment-button"
                  @click="cancelEditingComment"
                >
                  {{ $gettext('Zurücksetzen') }}
                </button>
                <button
                  class="button accept send-comment-button"
                  type="submit"
                  :disabled="commentEditorInput.trim().length === 0"
                >
                  {{
                    isEditingComment
                      ? $gettext('Aktualisieren')
                      : $gettext('Abschicken')
                  }}
                </button>
              </form>
            </div>
          </fieldset>
        </form>
      </section>
    </div>
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
  .travis-go-post-contents-container {
    grid-area: contents;
    form.default {
      fieldset {
        padding: 0;
        margin: 0;
        border: none;
      }
      legend {
        background-color: unset;
        border: none;
        padding-left: 0;
        padding-top: 0;
        padding-bottom: 0;
        margin-bottom: 0.5em;
        margin-left: -5px;
        font-size: unset;
      }
    }
    .travis-go-comments-list {
      border-left: 3px solid #899ab9;
      padding: 0 10px 0 26px;
      margin-top: 0.5em;
      margin-bottom: 10px;

      .travis-go-comment {
        .travis-go-comment-author {
          font-weight: 700;
        }
        /* We use opacity, not visibility or display: none, so the buttons can
           be accessed via tab */
        .travis-go-comment-action-menu {
          opacity: 0;
        }
        &:hover,
        &:focus-within {
          .travis-go-comment-action-menu {
            opacity: 1;
          }
        }
      }
    }
    .comment-editor {
      display: flex;
      gap: 10px;
      button.send-comment-button {
        margin: 0;
        min-width: fit-content;
      }
    }
  }
  .travis-go-post-contents {
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
import {
  computed,
  defineProps,
  defineEmits,
  PropType,
  ref,
  nextTick,
} from 'vue';
import {
  CreateCommentRequest,
  TravisGoComment,
  travisGoCommentSchema,
  TravisGoPost,
  UpdateCommentRequest,
} from '@/models/InteractiveVideoTask';
import { formatVideoTimestamp } from '@/components/interactiveVideo/formatVideoTimestamp';
import { store } from '@/store';
import DOMPurify from 'dompurify';
import { User } from '@/php-integration';
import StudipActionMenu from '@/components/studip/StudipActionMenu.vue';
import { LinkAction } from '@/components/studip/interfaces';
import { $gettext, $ngettext, $gettextInterpolate } from '@/language/gettext';
const props = defineProps({
  post: {
    type: Object as PropType<TravisGoPost>,
    required: true,
  },
  comments: {
    type: Object as PropType<TravisGoComment[]>,
    required: true,
  },
});
const emit = defineEmits({
  clickTimestamp(payload: number) {
    return true;
  },
  deletePost(postId: string) {
    return true;
  },
  deleteComment(id: string) {
    return true;
  },
  clickPostAuthorName(post: TravisGoPost) {
    return true;
  },
  clickCommentAuthorName(comment: TravisGoComment) {
    return true;
  },
});
const contentsPurified = computed(() =>
  DOMPurify.sanitize(props.post?.attributes.contents, {
    USE_PROFILES: { html: true },
  })
);

function userById(id: string): User | undefined {
  return store.getters['users/byId']({ id });
}
const postAuthor = computed<User | undefined>(() =>
  userById(props.post?.attributes.mk_user_id)
);
const postAuthorFormattedName = computed<string | undefined>(
  () => postAuthor.value?.attributes?.['formatted-name']
);

function userUrl(user: User | undefined): string {
  const username = user?.attributes?.['username'];
  if (username) {
    return window.STUDIP.URLHelper.getURL('dispatch.php/profile', { username });
  } else {
    return '';
  }
}

const postAuthorUrl = computed<string>(() => {
  return userUrl(postAuthor.value);
});

function commentAuthorName(comment: TravisGoComment): string {
  return (
    userById(comment.attributes.mk_user_id)?.attributes['formatted-name'] ??
    $gettext('Unbekannt')
  );
}

const commentEditorInput = ref<string>('');
const commentEditorInputElement = ref<HTMLInputElement | undefined>();
const isCommenting = ref(false);
function startCommentingPost() {
  isCommenting.value = true;
  nextTick(() => {
    const inputEl = commentEditorInputElement.value as HTMLElement;
    // If the input is inside of a collapsed <fieldset>, then the <fieldset>
    // should be un-collapsed so that the user can see the input field.
    const collapsedParents = [
      ...document.querySelectorAll('fieldset.collapsable.collapsed'),
    ].filter((element) => element.contains(inputEl));
    collapsedParents.forEach((parent) => parent.classList.remove('collapsed'));
    inputEl.focus();
  });
}
async function submitComment() {
  try {
    await createComment({
      attributes: {
        post_id: props.post.attributes.id,
        contents: commentEditorInput.value,
      },
    });
    commentEditorInput.value = '';
  } catch (error: unknown) {
    window.STUDIP.Report.error(
      $gettext('Dein Kommentar konnte nicht abgeschickt werden.'),
      [JSON.stringify(error, null, 2)]
    );
    console.error(error);
  }
}
async function createComment(post: CreateCommentRequest) {
  return await store.dispatch(
    'lernmodule-plugin/travis-go-comments/create',
    post
  );
}

const editedCommentId = ref<string | undefined>();
const isEditingComment = computed<boolean>(
  () => editedCommentId.value !== undefined
);
function startEditingComment(comment: TravisGoComment) {
  editedCommentId.value = comment.attributes.id;
  commentEditorInput.value = comment.attributes.contents;
}
function cancelEditingComment() {
  editedCommentId.value = undefined;
  commentEditorInput.value = '';
}
async function submitEditedComment() {
  try {
    if (!editedCommentId.value) {
      throw new Error(
        $gettext('Das bearbeitete Comment ID ist undefined oder leer.')
      );
    }
    await updateComment({
      id: editedCommentId.value,
      attributes: {
        contents: commentEditorInput.value,
      },
    });
    editedCommentId.value = undefined;
    commentEditorInput.value = '';
  } catch (error: unknown) {
    window.STUDIP.Report.error(
      $gettext('Dein Kommentar konnte nicht abgeschickt werden.'),
      [JSON.stringify(error, null, 2)]
    );
    console.error(error);
  }
}

async function updateComment(comment: UpdateCommentRequest) {
  return await store.dispatch(
    'lernmodule-plugin/travis-go-comments/update',
    comment
  );
}

function postActionMenuItems(): LinkAction[] {
  const deleteAction = {
    action_id: '1',
    label: $gettext('Löschen'),
    icon: 'trash',
    emit: 'deletePost',
  };
  return [
    ...(props.post.meta.permissions.mayDelete ? [deleteAction] : []),
    {
      action_id: '2',
      label: $gettext('Kommentieren'),
      icon: 'comment',
      emit: 'startCommentingPost',
    },
    {
      action_id: '3',
      type: 'link',
      label: $gettext('Nutzerprofil öffnen'),
      icon: 'share',
      url: postAuthorUrl.value,
    },
  ];
}

function commentActionMenuItems(comment: TravisGoComment): LinkAction[] {
  const deleteAction = {
    action_id: '1',
    label: $gettext('Löschen'),
    icon: 'trash',
    emit: 'deleteComment',
  };
  const startEditingAction = {
    action_id: '2',
    label: $gettext('Bearbeiten'),
    icon: 'edit',
    emit: 'startEditingComment',
  };
  return [
    ...(comment.meta.permissions.mayDelete ? [deleteAction] : []),
    ...(comment.meta.permissions.mayEdit ? [startEditingAction] : []),
    {
      action_id: '3',
      type: 'link',
      label: $gettext('Nutzerprofil öffnen'),
      icon: 'share',
      url: userUrl(userById(comment.attributes.mk_user_id)),
    },
  ];
}
function deletePost(id: string) {
  emit('deletePost', id);
}
function deleteComment(id: string) {
  emit('deleteComment', id);
}

function onClickTimestamp(time: number) {
  emit('clickTimestamp', time);
}
</script>
