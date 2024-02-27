<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import {
  EditorState,
  editorStateSymbol,
} from '@/components/interactiveVideo/editorState';
import { OverlayInteraction } from '@/models/InteractiveVideoTask';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'OverlayInteraction',
  methods: { $gettext },
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
    :data-hover-tooltip="$gettext('Overlay')"
    v-html="interaction.text"
  ></div>
</template>

<style scoped lang="scss">
.overlay {
  background: var(--dark-gray-color-15);
  border-radius: 10px;
  padding: 0.5em;
  overflow: hidden;

  &.selected {
    z-index: 1;
    border: 0.2em solid black;
  }

  &.editor {
    // Prevent annoying text selection when dragging/dropping the overlay
    user-select: none;
  }

  // Display a tooltip when hovered or focused.
  &.editor:not(.selected) {
    &:hover::before,
    &:focus::before {
      content: attr(data-hover-tooltip);
      display: flex;
      align-items: center;
      white-space: nowrap;
      position: absolute;
      top: -2em;
      left: 50%;
      transform: translateX(-50%);
      height: 1em;
      padding: 0.35em;
      border-radius: 12px;
      background: white;
      color: black;
      opacity: 0.9;
      z-index: 2;
    }
  }
}
</style>
