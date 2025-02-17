<template>
  <div class="image-and-hotspots-container-wrapper" :class="{ debug: !!debug }">
    <div class="image-and-hotspots-container">
      <!--  In the editor, hotspots are visible. In the viewer, they should be
      invisible. We can tell whether we are in the editor or the viewer based
      on whether the value of the injected field 'editor' is defined. -->
      <div
        v-for="hotspot in hotspots"
        :key="hotspot.uuid"
        class="hotspot"
        :class="{
          'invisible-hotspot': !editor,
          selected: editor?.selectedHotspotId.value === hotspot.uuid,
        }"
        :style="getHotspotStyle(hotspot)"
        @click="onClickHotspot(hotspot)"
        @pointerdown="onPointerdownHotspot(hotspot)"
      />
      <LazyImage
        :src="fileIdToUrl(image.file_id)"
        :alt="image.altText"
        @click="onClickBackground"
        class="image hotspots-image"
      />
      <pre
        v-if="debug"
        :style="{ position: 'absolute', top: 0, left: '100%' }"
        >{{ { selectedHotspot } }}</pre
      >
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import LazyImage from '@/components/LazyImage.vue';
import { Hotspot } from '@/models/FindTheHotspotsTask';
import { fileIdToUrl } from '@/models/TaskDefinition';
import { ImageElement } from '@/models/common';
import {
  FindTheHotspotsEditorState,
  findTheHotspotsEditorStateSymbol,
} from '@/components/findTheHotspots/findTheHotspotsEditorState';

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
    };
  },
  props: {
    hotspots: {
      type: Object as PropType<Hotspot[]>,
      required: true,
    },
    image: {
      type: Object as PropType<ImageElement>,
      required: true,
    },
  },
  computed: {
    debug(): boolean {
      return window.STUDIP.LernmoduleVueJS.LERNMODULE_DEBUG;
    },
    selectedHotspot(): Hotspot | undefined {
      return this.hotspots.find(
        (h) => h.uuid === this.editor?.selectedHotspotId.value
      );
    },
  },
  methods: {
    fileIdToUrl,
    onClickBackground() {
      this.editor?.selectHotspot(undefined);
    },
    onClickHotspot(hotspot: Hotspot) {
      console.log('onClickHotspot', hotspot);
      this.editor?.selectHotspot(hotspot.uuid);
    },
    onPointerdownHotspot(hotspot: Hotspot) {
      this.editor?.selectHotspot(hotspot.uuid);
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
    justify-content: flex-start;
  }
}

.image-and-hotspots-container {
  position: relative;
  height: max-content;
}

.hotspots-image {
  user-select: none;
  max-height: 400px;
  width: 100%;
}

.hotspot {
  position: absolute;
  border: 2px dashed rgba(0, 0, 0, 0.7);
  background-color: rgba(255, 255, 255, 0.5);

  // This class isn't called 'invisible' or 'hidden', because there are Stud.IP
  // CSS classes with those names that set display: none;
  &.invisible-hotspot {
    opacity: 0;
  }
}

.hotspot.selected {
  border: 2px dashed #0099ff;
}
</style>
