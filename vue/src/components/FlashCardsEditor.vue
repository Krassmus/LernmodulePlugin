<template>
  <button type="button" class="button" @click="onClickUpload">
    {{ $gettext('Bild hochladen') }}
  </button>

  <label>
    {{ this.debug }}
  </label>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { FlashCardTaskDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'FlashCardsEditor',
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as FlashCardTaskDefinition,
  },
  methods: {
    onClickUpload(): void {
      this.debug = window.STUDIP.ASSETS_URL;
    },
  },
  data() {
    return {
      debug: '' as string,
    };
  },
});
</script>

<style scoped></style>
