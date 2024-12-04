<template>
  <button type="button" class="pairing-editor-pair">
    <span class="pair-element">
      <MultimediaElement
        v-if="pair.draggableElement"
        :element="pair.draggableElement"
      />
    </span>
    <span class="pair-element">
      <MultimediaElement
        v-if="pair.targetElement"
        :element="pair.targetElement"
      />
    </span>
  </button>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { $gettext } from '@/language/gettext';
import { Pair } from '@/models/TaskDefinition';
import MultimediaElement from '@/components/MultimediaElement.vue';

export default defineComponent({
  name: 'PairingEditorPair',
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
.pairing-editor-pair {
  display: flex;
  flex-direction: column;

  border: 2px solid #dbe2e8;
  border-radius: 0.5em;
  background-color: #e7ebf1;

  gap: 0.25em;
  padding: 0.25em;
  margin: 0;

  overflow: hidden;
}

.pairing-editor-pair.selected {
  border: 2px solid #0a78d1;
  box-shadow: 0 0 4px #0a78d1;
}

.pairing-editor-pair:not(.disabled):not(.selected):hover {
  cursor: grab;
  border: 2px solid #0a78d1;
  box-shadow: 0 0 8px #0a78d1 inset;
}

.pair-element {
  width: 8em;
  height: 8em;

  display: flex;
  align-items: center;
  justify-content: center;

  overflow: hidden;
}
</style>
