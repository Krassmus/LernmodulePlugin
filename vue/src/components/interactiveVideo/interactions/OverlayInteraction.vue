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
  <div v-if="editor">Edited overlay</div>
  <div v-else>Viewer overlay</div>
</template>

<style scoped></style>
