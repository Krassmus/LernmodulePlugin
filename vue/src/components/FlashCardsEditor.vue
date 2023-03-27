<template>
  <EditedFlashCard
    v-for="card in taskDefinition.cards"
    :key="card.uuid"
    :card="card"
  />

  <label>
    {{ this.debug }}
  </label>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { FlashCardTaskDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import { uploadImage } from '@/routes';
import EditedFlashCard from '@/components/EditedFlashCard.vue';

export default defineComponent({
  name: 'FlashCardsEditor',
  components: { EditedFlashCard },
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as FlashCardTaskDefinition,
  },
  methods: {
    async onClickUpload() {
      try {
        const res = await this.uploadImage();
        console.log('image upload result: ', res);
      } catch (error) {
        console.error('image upload failed. ', error);
      }
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
