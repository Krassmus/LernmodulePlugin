<script setup lang="ts">
import { defineProps, PropType, ref } from 'vue';
import {
  InteractiveVideoTask,
  TravisGoPostProps,
} from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import TravisGoPost from '@/components/interactiveVideo/viewer/TravisGoPost.vue';

defineProps({
  task: {
    type: Object as PropType<InteractiveVideoTask>,
    required: true,
  },
});

const posts = ref<TravisGoPostProps[]>([
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

const searchInput = ref<string>('');
function onClickSearch() {}
</script>

<template>
  <div v-if="task.travisGoSettings.enabled" class="travis-go-main">
    <div class="travis-go-left-column">
      <VideoPlayer :task="task" />
      <div class="annotation-controls">
        <button class="button date">{{ $gettext('Start') }}</button>
        <button class="button date">{{ $gettext('End') }}</button>
        <select>
          <option>Meta</option>
          <option>Image</option>
          <option>Sound</option>
          <option>Text</option>
        </select>
      </div>
      <StudipWysiwyg />
      <button class="button">{{ $gettext('Kommentar posten') }}</button>
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
        <TravisGoPost
          v-for="(post, index) in posts"
          :key="post.id"
          :class="{
            odd: index % 2 === 0,
          }"
          :post="post"
        />
      </section>
    </div>
  </div>
  <VideoPlayer v-else :task="task" />
</template>

<style scoped lang="scss">
.travis-go-main {
  h3 {
    margin-top: 10px;
  }
  display: flex;
  flex-wrap: wrap;

  gap: 10px;
  > .travis-go-left-column {
    flex: 1;
    min-width: 400px;
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
