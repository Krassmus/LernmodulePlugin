<template>
  <button type="button" class="h5pElementPair">
    <MultimediaElement
      v-if="pair.draggableElement"
      :element="pair.draggableElement"
      class="h5pMultimediaElement"
    />
    <MultimediaElement
      v-if="pair.targetElement"
      :element="pair.targetElement"
      class="h5pMultimediaElement"
    />
  </button>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { $gettext } from '@/language/gettext';
import { Pair } from '@/models/TaskDefinition';
import MultimediaElement from '@/components/MultimediaElement.vue';

export default defineComponent({
  name: 'ElementPair',
  components: { MultimediaElement },
  props: {
    pair: {
      type: Object as PropType<Pair>,
      required: true,
    },
  },
  methods: {
    $gettext,
  },
});
</script>

<style scoped>
.h5pElementPair {
  display: flex;
  flex-direction: column;
  border: rgba(0, 0, 0, 0) 2px solid;
  border-radius: 0.5em;
  gap: 0.25em;
  padding: 0;
  background: unset;
}

.h5pElementPair.selected {
  border: #0a78d1 2px solid;
  box-shadow: 0 0 8px #0a78d1;
}

.h5pElementPair:not(.disabled):not(.selected):hover {
  cursor: grab;
  border: 2px solid #0a78d1;
  box-shadow: 0 0 4px #0a78d1;
}

.h5pMultimediaElement {
  width: 8em;
  height: 8em;
}
</style>
