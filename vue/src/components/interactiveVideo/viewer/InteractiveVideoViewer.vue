<script setup lang="ts">
import '@/assets/interactive-video-viewer.scss';
import { defineProps, PropType, ref } from 'vue';
import { InteractiveVideoTask, Post } from '@/models/InteractiveVideoTask';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';
import TravisPost from '@/components/interactiveVideo/viewer/TravisPost.vue';

defineProps({
  task: {
    type: Object as PropType<InteractiveVideoTask>,
    required: true,
  },
});

const posts = ref<Post[]>([
  {
    id: 'fakeid',
    type: 'meta',
    authorId: 'anna',
    authorName: 'Anna',
    description: "This is anna's post",
    start: 0,
  },
  {
    id: 'fakeid',
    type: 'image',
    authorId: 'kevin',
    authorName: 'Kevin',
    description: "This is Kevin's post",
    start: 0,
  },
]);

const searchInput = ref<string>('');
function onClickSearch() {}
function maskForIcon(icon: string, color: string = 'black') {
  const url = `${window.STUDIP.ASSETS_URL}images/icons/${color}/${icon}.svg`;
  return `--mask-value: url("${url}") no-repeat center / contain;`;
}
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
      <div class="participants-list" :style="maskForIcon('own-license')">
        <a>@Anna</a>
        <a>@Kevin</a>
      </div>
    </div>
    <div class="travis-go-right-column">
      <nav class="search-bar">
        <input type="text" v-model="searchInput" />
        <button class="button search" @click="onClickSearch" />
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
      <section class="travis-posts">
        <TravisPost
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
