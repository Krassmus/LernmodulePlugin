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
  <p v-else>Platzhalter für {{ element.type }}</p>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { fileIdToUrl, PairElement } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'MultimediaElement',
  props: {
    element: {
      type: Object as PropType<PairElement>,
      required: true,
    },
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
  flex-grow: 1;
}
</style>
