<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import {
  EditorState,
  editorStateSymbol,
} from '@/components/interactiveVideo/editorState';
import { OverlayInteraction } from '@/models/InteractiveVideoTask';

export default defineComponent({
  name: 'OverlayInteraction',
  setup() {
    return {
      editor: inject<EditorState>(editorStateSymbol),
    };
  },
  props: {
    interaction: {
      type: Object as PropType<OverlayInteraction>,
      required: true,
    },
  },
});
</script>

<template>
  <div
    class="overlay"
    :class="{
      selected: editor?.selectedInteractionId.value === interaction.id,
      editor: !!editor,
    }"
  >
    {{ editor ? 'Edited overlay' : 'Viewed overlay' }}
  </div>
</template>

<style scoped>
.overlay {
  background: var(--dark-gray-color-15);
  border-radius: 10px;
  padding: 0.5em;

  &.selected {
    border: 0.2em solid black;
  }
}
</style>
