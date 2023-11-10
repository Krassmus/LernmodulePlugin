<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import {
  EditorState,
  editorStateSymbol,
} from '@/components/interactiveVideo/editorState';
import { Interaction, LmbTaskInteraction } from '@/models/InteractiveVideoTask';

export default defineComponent({
  name: 'LmbTaskInteraction',
  setup() {
    return {
      editorState: inject<EditorState>(editorStateSymbol),
    };
  },
  props: {
    interaction: {
      type: Object as PropType<LmbTaskInteraction>,
      required: true,
    },
  },
});
</script>

<template>
  <button
    type="button"
    :class="{
      selected: editorState?.selectedInteractionId.value === interaction.id,
    }"
  >
    {{ interaction.taskDefinition.task_type }}
  </button>
</template>

<style scoped>
.selected {
  border: 4px solid black;
}
</style>
