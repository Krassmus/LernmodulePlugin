<template>
  <span>Find The Words - Viewer</span><br />
  <div class="h5pWords">
    <span v-for="word in this.task.words" :key="word" v-text="word" />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FindTheWordsTask } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'FindTheWordsViewer',
  props: {
    task: { type: Object as PropType<FindTheWordsTask>, required: true },
  },
  emits: ['updateAttempt'],
  data() {
    return {};
  },
  methods: {},
  computed: {},
});
</script>

<style scoped>
.h5pWords {
  display: flex;
  align-items: center;
  gap: 0.5em;
}
</style>
