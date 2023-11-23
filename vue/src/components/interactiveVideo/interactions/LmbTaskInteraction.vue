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
      editor: inject<EditorState>(editorStateSymbol),
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
  <div
    v-if="editor"
    :class="{
      selected: editor?.selectedInteractionId.value === interaction.id,
    }"
  >
    {{ interaction.taskDefinition.task_type }}
  </div>
  <button
    v-else
    type="button"
    @click="$emit('activateInteraction', interaction)"
  >
    {{ interaction.taskDefinition.task_type }}
  </button>
</template>

<style scoped>
.selected {
  border: 4px solid blue;
}
</style>
