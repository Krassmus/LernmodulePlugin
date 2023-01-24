<template>
  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Text erstellen') }}</legend>
      <div>
        <textarea
          v-model="taskDefinition.template"
          ref="theTextArea"
          class="h5pFillInTheBlanksEditor"
        />
      </div>
    </fieldset>
  </form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MarkTheWordsTaskDefinition } from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'MarkTheWordsEditor',
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as MarkTheWordsTaskDefinition,
  },
});
</script>

<style scoped></style>
