<template>
  <template v-if="element.type === 'image'">
    <img
      v-if="element.file_id"
      :src="fileIdToUrl(element.file_id)"
      :alt="element.altText"
      class="image-element"
      draggable="false"
    />
    <div v-else class="image-element" />
  </template>
  <template v-else-if="element.type === 'text'">
    <div class="text-element" draggable="false" v-text="element.content" />
  </template>
  <template v-else-if="element.type === 'audio'">
    <div class="audio-element" draggable="false">
      <CoursewareAudioBlock />
    </div>
  </template>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  fileIdToUrl,
  LernmoduleMultimediaElement,
} from '@/models/TaskDefinition';
const CoursewareAudioBlock = (
  (await import(
    '../../../../../../../resources/vue/components/courseware/CoursewareAudioBlock.vue'
  )) as any
).default;
console.log('cw audio block:', CoursewareAudioBlock);

export default defineComponent({
  name: 'MultimediaElement',
  props: {
    element: {
      type: Object as PropType<LernmoduleMultimediaElement>,
      required: true,
    },
  },
  components: {
    CoursewareAudioBlock,
  },
  methods: { fileIdToUrl },
});
</script>

<style scoped>
.image-element {
  box-sizing: border-box;
  max-width: 100%;
  max-height: 100%;
  padding: 4px;
  object-fit: contain;
  box-shadow: inset 0 0 72px #cbd5de; /* x-offset, y-offset, blur-radius */
  border: solid 1px #cbd5de;
  border-radius: 0.25em;
  background-color: white;
}

.text-element {
  box-sizing: border-box;
  max-width: 100%;
  max-height: 100%;
  box-shadow: inset 0 0 72px #cbd5de; /* x-offset, y-offset, blur-radius */
  border: solid 1px #cbd5de;
  border-radius: 0.25em;
  background-color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: auto;
}

.audio-element {
  box-sizing: border-box;
  max-width: 100%;
  max-height: 100%;
  box-shadow: inset 0 0 72px #cbd5de; /* x-offset, y-offset, blur-radius */
  border: solid 1px #cbd5de;
  border-radius: 0.25em;
  background-color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: auto;
}
</style>
