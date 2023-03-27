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
import { uploadImage } from '@/routes';

export default defineComponent({
  name: 'FlashCardsEditor',
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as FlashCardTaskDefinition,
  },
  methods: {
    async onClickUpload() {
      const res = await this.uploadImage();
      console.log('image upload result: ', res);
    },
    async uploadImage() {
      const blob = new Blob();
      return uploadImage(blob);
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
