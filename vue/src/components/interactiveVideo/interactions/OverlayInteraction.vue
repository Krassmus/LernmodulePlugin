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
  methods: {
    $gettext,
    onPointerDownResizeHandle(event: PointerEvent, handle: string) {
      this.$emit('pointerDownResizeHandle', {
        event,
        handle,
        interaction: this.interaction,
      });
    },
    onPointerMoveResizeHandle(event: PointerEvent, handle: string) {
      this.$emit('pointerMoveResizeHandle', {
        event,
        handle,
        interaction: this.interaction,
      });
    },
    onPointerUpResizeHandle(event: PointerEvent, handle: string) {
      this.$emit('pointerUpResizeHandle', {
        event,
        handle,
        interaction: this.interaction,
      });
    },
  },
  setup() {
    return {
      editor: inject<EditorState>(editorStateSymbol),
    };
  },
  data() {
    return {
      resizeHandles: [
        'left',
        'top-left',
        'top',
        'top-right',
        'right',
        'bottom-right',
        'bottom',
        'bottom-left',
      ],
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
  >
    <template v-if="editor">
      <div
        v-for="handle in resizeHandles"
        :key="handle"
        class="resize-handle"
        :class="handle"
        @pointerdown.stop="onPointerDownResizeHandle($event, handle)"
        @pointermove.stop="onPointerMoveResizeHandle($event, handle)"
        @pointerup.stop="onPointerUpResizeHandle($event, handle)"
      />
    </template>
    <div class="overlay-content" v-html="interaction.text" />
  </div>
</template>

<style scoped lang="scss">
.overlay {
  background: var(--dark-gray-color-15);
  box-sizing: border-box;
  border-radius: 10px;
  padding: 0.5em;
  position: relative;

  .overlay-content {
    height: 100%;
    width: 100%;
    overflow: hidden;
  }

  .resize-handle {
    $size: 8px;
    position: absolute;
    //background: cornflowerblue;
    &.top {
      cursor: ns-resize;
      top: 0;
      left: $size;
      right: $size;
      height: $size;
    }
    &.top-right {
      cursor: nesw-resize;
      top: 0;
      right: 0;
      height: $size;
      width: $size;
    }
    &.right {
      cursor: ew-resize;
      top: $size;
      bottom: $size;
      right: 0;
      width: $size;
    }
    &.bottom-right {
      cursor: nwse-resize;
      bottom: 0;
      right: 0;
      width: $size;
      height: $size;
    }
    &.bottom {
      cursor: ns-resize;
      bottom: 0;
      left: $size;
      right: $size;
      height: $size;
    }
    &.bottom-left {
      cursor: nesw-resize;
      bottom: 0;
      left: 0;
      width: $size;
      height: $size;
    }
    &.left {
      cursor: ew-resize;
      top: $size;
      bottom: $size;
      left: 0;
      width: $size;
    }
    &.top-left {
      cursor: nwse-resize;
      top: 0;
      left: 0;
      height: $size;
      width: $size;
    }
  }

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
