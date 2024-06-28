<template>
  <img
    v-if="element.type === 'image'"
    :src="fileIdToUrl(element.file_id)"
    :alt="element.altText"
    class="image-element"
    draggable="false"
  />
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
  max-width: 8em;
  max-height: 8em;
  padding: 0.5em;
  object-fit: contain;
  box-shadow: inset 0 0 72px #cbd5de; /* x-offset, y-offset, blur-radius */
  border: solid 1px #cbd5de;
  border-radius: 0.25em;
}
</style>
