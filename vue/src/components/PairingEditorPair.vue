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
  align-items: center;

  border: 2px solid #dbe2e8;
  border-radius: 0.5em;
  background: white;

  gap: 0.25em;
  padding: 0.25em;
  margin: 0;

  overflow: hidden;

  cursor: pointer;
}

.pairing-editor-pair.selected {
  border: 2px solid #7ba4d3;
  box-shadow: 0 0 10px 0 #406ef3;
}

.pairing-editor-pair:not(.disabled):not(.selected):hover {
  border: 2px solid #7ba4d3;
  box-shadow: 0 0 10px 0 #406ef3;
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
