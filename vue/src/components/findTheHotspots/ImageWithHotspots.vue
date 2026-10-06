<template>
  <div
    class="image-and-hotspots-container-wrapper"
    :class="{
      debug: debug,
    }"
    ref="wrapper"
  >
    <div class="image-and-hotspots-container" ref="root">
      <!--  In the editor, hotspots are visible. In the viewer, they should be
      invisible. We can tell whether we are in the editor or the viewer based
      on whether the value of the injected field 'editor' is defined. -->
      <button
        v-for="hotspot in hotspots"
        :key="hotspot.uuid"
        type="button"
        class="hotspot"
        :class="{
          'invisible-hotspot': !editor,
          selected: editor?.selectedHotspotId.value === hotspot.uuid,
        }"
        :id="`hotspot-${uid}-${hotspot.uuid}`"
        :style="getHotspotStyle(hotspot)"
        @click="onClickHotspot($event, hotspot)"
        @pointerdown="onPointerdownHotspot($event, hotspot)"
        @pointermove="onPointermoveHotspot($event, hotspot)"
        @pointerup="onPointerupHotspot($event, hotspot)"
      >
        <template v-if="editor">
          <div
            v-for="handle in resizeHandles"
            :key="handle"
            class="resize-handle"
            :class="handle"
            @pointerdown.stop="
              onPointerdownResizeHandle($event, handle, hotspot)
            "
            @pointermove.stop="
              onPointermoveResizeHandle($event, handle, hotspot)
            "
            @pointerup.stop="onPointerupResizeHandle($event, handle, hotspot)"
          />
        </template>
      </button>
      <LazyImage
        :src="fileIdToUrl(image.file_id)"
        :alt="image.altText"
        @click="onClickBackground"
        class="hotspots-image"
      />
    </div>
    <pre
      v-if="debug && editor"
      :style="{ flexBasis: '50%', flexGrow: 0, flexShrink: 0 }"
      >{{ { selectedHotspot, dragState } }}</pre
    >
    <pre
      v-if="debug && viewer"
      :style="{ flexBasis: '50%', flexGrow: 0, flexShrink: 0 }"
      >{{ { selectedHotspot, dragState } }}</pre
    >
    <div
      ref="selectedHotspotTooltip"
      v-if="editor"
      class="selected-hotspot-tooltip"
      :class="{
        hidden: !selectedHotspot,
      }"
    >
      <div style="display: flex; margin-bottom: 0; align-items: flex-start">
        <label style="display: flex; margin-bottom: 0; align-items: center">
          <input
            :checked="selectedHotspot?.correct"
            type="checkbox"
            @change="editor!.changeHotspotCorrectness($event.target.checked)"
          />
          {{ $gettext('Korrekt') }}
        </label>
        <button
          type="button"
          class="small-button trash"
          style="margin-left: auto"
          @click="editor!.deleteSelectedHotspot()"
          :title="$gettext('Diesen Hotspot löschen')"
        />
      </div>
      <label>
        {{ $gettext('Feedback:') }}
        <input
          :value="(selectedHotspot as Hotspot)?.feedback"
          @input="editor!.setHotspotFeedback($event.target.value)"
          type="text"
          :title="$gettext('Feedback bei Klick auf diesen Hotspot')"
        />
      </label>
      <div class="arrow" data-popper-arrow></div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import LazyImage from '@/components/LazyImage.vue';
import { Hotspot } from '@/models/FindTheHotspotsTask';
import { fileIdToUrl } from '@/models/TaskDefinition';
import { ImageFileElement } from '@/models/common';
import {
  FindTheHotspotsEditorState,
  findTheHotspotsEditorStateSymbol,
} from '@/components/findTheHotspots/findTheHotspotsEditorState';
import {
  FindTheHotspotsViewerState,
  findTheHotspotsViewerStateSymbol,
} from '@/components/findTheHotspots/findTheHotspotsViewerState';
import { createPopper, Instance } from '@popperjs/core';
import { v4 } from 'uuid';
import { ResizeHandle } from '@/models/InteractiveVideoTask';
import { $gettext } from '@/language/gettext';

type DragState =
  | {
      type: 'dragHotspot';
      dragId: string;
      hotspotId: string;
      pointerStartPos: [number, number]; // clientX, clientY
      hotspotStartPos: [number, number]; // fraction x, fraction y
    }
  | {
      type: 'resizeHotspot';
      dragId: string;
      hotspotId: string;
      pointerStartPos: [number, number]; // clientX, clientY
      hotspotStartPos: [number, number]; // fraction x, fraction y
      hotspotStartSize: [number, number]; // fraction x, fraction y
      handle: string;
    }
  | undefined;

export default defineComponent({
  name: 'ImageWithHotspots',
  components: { LazyImage },
  setup() {
    return {
      editor: inject<FindTheHotspotsEditorState | undefined>(
        findTheHotspotsEditorStateSymbol,
        // Supply a default value to suppress the vue warning about missing
        // injection in the viewer:
        // [Vue warn]: injection "Symbol(Find The Hotspots Editor state)" not found.
        undefined
      ),
      viewer: inject<FindTheHotspotsViewerState | undefined>(
        findTheHotspotsViewerStateSymbol,
        // Supply a default value to suppress the vue warning about missing
        // injection in the viewer:
        // [Vue warn]: injection "Symbol(Find The Hotspots Viewer state)" not found.
        undefined
      ),
    };
  },
  props: {
    hotspots: {
      type: Object as PropType<Hotspot[]>,
      required: true,
    },
    image: {
      type: Object as PropType<ImageFileElement>,
      required: true,
    },
  },
  data() {
    return {
      popperInstance: undefined as Instance | undefined,
      dragState: undefined as DragState,
    };
  },
  computed: {
    debug(): boolean {
      return window.STUDIP.LernmoduleVueJS.LERNMODULE_DEBUG && false;
    },
    // A unique ID for this instance of ImageWithHotspots, so that we can refer
    // to elements inside of it by ID when there are multiple instances
    // (e.g. viewer and editor) on the same page.
    uid(): string {
      return v4();
    },
    selectedHotspot(): Hotspot | undefined {
      return this.hotspots.find(
        (h) => h.uuid === this.editor?.selectedHotspotId.value
      );
    },
    resizeHandles() {
      return [
        'left',
        'top-left',
        'top',
        'top-right',
        'right',
        'bottom-right',
        'bottom',
        'bottom-left',
      ] as const;
    },
  },
  watch: {
    selectedHotspot: {
      immediate: true,
      handler: function (hotspot: Hotspot | undefined) {
        this.popperInstance?.destroy();
        if (hotspot) {
          // Run on next tick, because we need to wait for the hotspotElement
          // to be rendered by Vue, in case it isn't yet visible at the moment this
          // watcher is triggered.
          // For example, if the user deletes the currently selected hotspot
          // and then clicks undo, the hotspotElement will not be visible until
          // the next reactivity "tick" has elapsed.
          this.$nextTick(() => {
            const hotspotElement = document.getElementById(
              `hotspot-${this.uid}-${hotspot.uuid}`
            ) as HTMLElement;
            const tooltipElement = this.$refs
              .selectedHotspotTooltip as HTMLElement;
            this.popperInstance = createPopper(hotspotElement, tooltipElement, {
              placement: 'top',
              modifiers: [
                {
                  name: 'offset',
                  options: {
                    offset: [0, 14],
                  },
                },
              ],
            });
          });
        }
      },
    },
  },
  methods: {
    $gettext,
    fileIdToUrl,
    onClickBackground(event: PointerEvent) {
      if (this.editor) {
        this.editor?.selectHotspot(undefined);
      } else if (this.viewer) {
        // Get the click coordinates relative to the wrapper element
        const wrapperRect = (
          this.$refs.wrapper as HTMLElement
        ).getBoundingClientRect();

        let clickX = event.clientX - wrapperRect.left;
        let clickY = event.clientY - wrapperRect.top;

        this.viewer.clickBackground(clickX, clickY);
      }
    },
    onClickHotspot(event: PointerEvent, hotspot: Hotspot) {
      console.log('onClickHotspot', hotspot);

      if (this.editor) {
        this.editor?.selectHotspot(hotspot.uuid);
      } else if (this.viewer) {
        // Get the click coordinates relative to the wrapper element
        const wrapperRect = (
          this.$refs.wrapper as HTMLElement
        ).getBoundingClientRect();

        let clickX = event.clientX - wrapperRect.left;
        let clickY = event.clientY - wrapperRect.top;

        this.viewer.clickHotspot(hotspot.uuid, clickX, clickY);
      }
    },
    onPointerdownHotspot(event: PointerEvent, hotspot: Hotspot) {
      console.log('onPointerDownHotspot');
      if (!this.editor) {
        return;
      }
      this.editor.selectHotspot(hotspot.uuid);
      this.dragState = {
        type: 'dragHotspot',
        hotspotId: hotspot.uuid,
        dragId: v4(),
        pointerStartPos: [event.clientX, event.clientY],
        hotspotStartPos: [hotspot.x, hotspot.y],
      };
      (event.target as HTMLElement).setPointerCapture(event.pointerId);
    },
    onPointerupHotspot(event: PointerEvent, hotspot: Hotspot) {
      console.log('onPointerUpHotspot');
      this.dragState = undefined;
      (event.target as HTMLElement).releasePointerCapture(event.pointerId);
    },
    onPointermoveHotspot(event: PointerEvent, hotspot: Hotspot) {
      if (
        this.dragState?.type === 'dragHotspot' &&
        this.dragState.hotspotId === hotspot.uuid
      ) {
        const rootRect = (
          this.$refs.root as HTMLElement
        ).getBoundingClientRect();
        const hotspotEl = event.currentTarget as HTMLElement;

        const clientDx = event.clientX - this.dragState.pointerStartPos[0];
        const dxFraction = clientDx / rootRect.width;
        const xFraction = this.dragState.hotspotStartPos[0] + dxFraction;
        const hotspotWidth = hotspotEl.clientWidth / rootRect.width;
        const minX = 0.05 - hotspotWidth;
        const maxX = 0.95;
        const clampedXFraction = Math.min(maxX, Math.max(minX, xFraction));

        const clientDy = event.clientY - this.dragState.pointerStartPos[1];
        const dyFraction = clientDy / rootRect.height;
        const yFraction = this.dragState.hotspotStartPos[1] + dyFraction;
        const hotspotHeight = hotspotEl.clientHeight / rootRect.height;
        const minY = 0.05 - hotspotHeight;
        const maxY = 0.95;
        const clampedYFraction = Math.min(maxY, Math.max(minY, yFraction));

        const id = this.dragState.hotspotId;
        this.editor?.dragHotspot(
          this.dragState.dragId,
          id,
          clampedXFraction,
          clampedYFraction
        );
        this.popperInstance?.update();
      }
    },
    onPointerdownResizeHandle(
      event: PointerEvent,
      handle: ResizeHandle,
      hotspot: Hotspot
    ) {
      console.log('onPointerDownResizeHandle');
      if (!this.editor) {
        return;
      }
      this.editor.selectHotspot(hotspot.uuid);
      this.dragState = {
        type: 'resizeHotspot',
        dragId: v4(),
        hotspotId: hotspot.uuid,
        pointerStartPos: [event.clientX, event.clientY],
        hotspotStartPos: [hotspot.x, hotspot.y],
        hotspotStartSize: [hotspot.width, hotspot.height],
        handle,
      };
      (event.target as HTMLElement).setPointerCapture(event.pointerId);
    },
    onPointerupResizeHandle(
      event: PointerEvent,
      handle: ResizeHandle,
      hotspot: Hotspot
    ) {
      console.log('onPointerUpResizeHandle');
      this.dragState = undefined;
      (event.target as HTMLElement).releasePointerCapture(event.pointerId);
    },
    onPointermoveResizeHandle(
      event: PointerEvent,
      handle: ResizeHandle,
      hotspot: Hotspot
    ) {
      if (
        this.dragState?.type === 'resizeHotspot' &&
        this.dragState.hotspotId === hotspot.uuid
      ) {
        const rootRect = (
          this.$refs.root as HTMLElement
        ).getBoundingClientRect();
        const {
          hotspotStartPos: [xInitial, yInitial],
          hotspotStartSize: [widthInitial, heightInitial],
        } = this.dragState;
        let newX = xInitial;
        let newWidth = widthInitial;
        let newY = yInitial;
        let newHeight = heightInitial;

        // Convert pointer movement in pixels to fractions of root rect width/height
        const dxPointerPixels =
          event.clientX - this.dragState.pointerStartPos[0];
        const dxPointer = dxPointerPixels / rootRect.width;

        const dyPointerPixels =
          event.clientY - this.dragState.pointerStartPos[1];
        const dyPointer = dyPointerPixels / rootRect.height;

        // Horizontal resizing
        if (
          handle === 'left' ||
          handle === 'top-left' ||
          handle === 'bottom-left'
        ) {
          if (dxPointer > widthInitial) {
            newX = xInitial + widthInitial;
            newWidth = dxPointer - widthInitial;
          } else {
            newX = xInitial + dxPointer;
            newWidth = widthInitial - dxPointer;
          }
        } else if (
          handle === 'right' ||
          handle === 'top-right' ||
          handle === 'bottom-right'
        ) {
          if (-dxPointer > widthInitial) {
            newX = xInitial + widthInitial + dxPointer;
            newWidth = -dxPointer - widthInitial;
          } else {
            newX = xInitial;
            newWidth = widthInitial + dxPointer;
          }
        }

        // Vertical resizing
        if (
          handle === 'top-left' ||
          handle === 'top' ||
          handle === 'top-right'
        ) {
          if (dyPointer > heightInitial) {
            newY = yInitial + heightInitial;
            newHeight = dyPointer - heightInitial;
          } else {
            newY = yInitial + dyPointer;
            newHeight = heightInitial - dyPointer;
          }
        } else if (
          handle === 'bottom-left' ||
          handle === 'bottom' ||
          handle === 'bottom-right'
        ) {
          if (-dyPointer > heightInitial) {
            newY = yInitial + heightInitial + dyPointer;
            newHeight = -dyPointer - heightInitial;
          } else {
            newY = yInitial;
            newHeight = heightInitial + dyPointer;
          }
        }

        // Prevent shrinking the hotspot below a minimum size
        let filteredHeight = hotspot.height,
          filteredWidth = hotspot.width,
          filteredX = hotspot.x,
          filteredY = hotspot.y;
        const newHeightPixels = newHeight * rootRect.height;
        const newWidthPixels = newWidth * rootRect.width;
        const minPixelDimension = 20;
        if (newHeightPixels > minPixelDimension) {
          filteredHeight = newHeight;
          filteredY = newY;
        }
        if (newWidthPixels > minPixelDimension) {
          filteredWidth = newWidth;
          filteredX = newX;
        }

        // Prevent moving the hotspot completely off screen
        const isOffscreenX =
          filteredX > 0.95 || filteredX + filteredWidth < 0.05;
        const isOffscreenY =
          filteredY > 0.95 || filteredY + filteredHeight < 0.05;
        if (isOffscreenX) {
          filteredX = hotspot.x;
          filteredWidth = hotspot.width;
        }
        if (isOffscreenY) {
          filteredY = hotspot.y;
          filteredHeight = hotspot.height;
        }

        this.editor!.resizeHotspot(
          this.dragState.dragId,
          hotspot.uuid,
          filteredX,
          filteredY,
          filteredWidth,
          filteredHeight
        );
        this.popperInstance?.update();
      }
    },
    getHotspotStyle(hotspot: Hotspot): Partial<CSSStyleDeclaration> {
      if (hotspot.type === 'rectangle') {
        return {
          left: `${hotspot.x * 100}%`,
          top: `${hotspot.y * 100}%`,
          width: `${hotspot.width * 100}%`,
          height: `${hotspot.height * 100}%`,
        };
      } else {
        return {
          left: `${hotspot.x * 100}%`,
          top: `${hotspot.y * 100}%`,
          width: `${hotspot.width * 100}%`,
          height: `${hotspot.height * 100}%`,
          borderRadius: '50%',
        };
      }
    },
  },
});
</script>

<style scoped lang="scss">
.image-and-hotspots-container-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;

  &.debug {
    align-items: flex-start;
    justify-content: flex-start;
  }
}

.image-and-hotspots-container {
  display: flex;
  position: relative;
  overflow: hidden;
  max-height: 400px;
}

.hotspots-image {
  user-select: none;
  max-height: 400px;
  width: 100%;
}

$hotspotBorderWidth: 2px;

button.hotspot {
  /* CSS Reset for button styles */
  padding: 0;
  //border: none;
  font: inherit;
  color: inherit;
  //background-color: transparent;
  cursor: pointer;
  box-sizing: border-box;

  position: absolute;
  border: $hotspotBorderWidth dashed rgba(0, 0, 0, 0.7);
  background-color: rgba(255, 255, 255, 0.5);

  // This class isn't called 'invisible' or 'hidden', because there are Stud.IP
  // CSS classes with those names that set display: none;
  &.invisible-hotspot {
    opacity: 0;
    cursor: unset;
    border: unset;
    background: unset;
    // Ensure that keyboard-only users are able to see the hotspot, if any,
    // that they have focused with the keyboard.  (Accessibility standards)
    &:focus {
      opacity: unset;
    }

    &.correct {
      color: #255c41;
      background-image: radial-gradient(
        circle at center,
        rgba(157, 216, 187, 0.3) 0,
        transparent 80%
      );
      background-size: 100% 100%;
      background-position: center;
    }

    &.incorrect {
      color: #b71c1c;
      background-image: radial-gradient(
        circle at center,
        rgba(247, 208, 208, 0.3) 0,
        transparent 80%
      );
      background-size: 100% 100%;
      background-position: center;
    }
  }

  &.selected {
    border-color: #0a78d1;
  }

  &.selected .resize-handle {
    $size: 12px;
    position: absolute;
    background: white;
    border: 1px solid #0a78d1;
    width: $size;
    height: $size;
    box-sizing: border-box;

    &.top-left {
      cursor: nwse-resize;
      top: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
      left: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
    }

    &.top {
      cursor: ns-resize;
      top: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
      left: calc(50% - $size / 2);
    }

    &.top-right {
      cursor: nesw-resize;
      top: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
      right: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
    }

    &.right {
      cursor: ew-resize;
      top: calc(50% - $size / 2);
      right: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
    }

    &.bottom-right {
      cursor: nwse-resize;
      bottom: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
      right: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
    }

    &.bottom {
      cursor: ns-resize;
      bottom: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
      left: calc(50% - $size / 2);
    }

    &.bottom-left {
      cursor: nesw-resize;
      bottom: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
      left: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
    }

    &.left {
      cursor: ew-resize;
      top: calc(50% - $size / 2);
      left: calc(0% - $size / 2 - $hotspotBorderWidth / 2);
    }
  }
}

.selected-hotspot-tooltip {
  &.hidden {
    display: none;
  }
  position: absolute;
  display: flex;
  flex-direction: column;
  gap: 0.5em;
  background: white;
  color: black;
  border-radius: 12px;
  border: #0a78d1 2px solid;
  padding: 8px;
  > .arrow,
  > .arrow::before {
    position: absolute;
    width: 8px;
    height: 8px;
    background: inherit;
  }

  > .arrow {
    visibility: hidden;
  }

  > .arrow::before {
    visibility: visible;
    content: '';
    transform: translateX(-$hotspotBorderWidth) rotate(45deg);
  }

  &[data-popper-placement^='top'] > .arrow {
    bottom: -5px;
  }

  &[data-popper-placement^='bottom'] > .arrow {
    top: -7px;
  }

  &[data-popper-placement^='left'] > .arrow {
    right: -4px;
  }

  &[data-popper-placement^='right'] > .arrow {
    left: -4px;
  }

  &[data-popper-placement^='top'] > .arrow:before {
    border-bottom: #0a78d1 2px solid;
    border-right: #0a78d1 2px solid;
  }

  &[data-popper-placement^='bottom'] > .arrow:before {
    border-top: #0a78d1 2px solid;
    border-left: #0a78d1 2px solid;
  }
}
</style>
