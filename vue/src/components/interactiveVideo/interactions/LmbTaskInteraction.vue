<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import {
  EditorState,
  editorStateSymbol,
} from '@/components/interactiveVideo/editorState';
import { Interaction, LmbTaskInteraction } from '@/models/InteractiveVideoTask';
import { printTaskType } from '../../../models/TaskDefinition';

export default defineComponent({
  name: 'LmbTaskInteraction',
  methods: { printTaskType },
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
    {{ printTaskType(interaction.taskDefinition.task_type) }}
  </div>
  <button
    v-else
    type="button"
    @click="$emit('activateInteraction', interaction)"
  >
    {{ printTaskType(interaction.taskDefinition.task_type) }}
  </button>
</template>

<style scoped>
.selected {
  border: 4px solid blue;
}
</style>
