<template>
  <!--  TODO #28 use button instead of div -->
  <div class="h5pElementPair">
    <template v-if="pair.draggableElement">
      <EditedImagePairImage
        v-if="pair.draggableElement.type === 'image'"
        :image="pair.draggableElement"
      />
      <p v-else-if="pair.draggableElement.type === 'text'">
        Platzhalter für Text Element
      </p>
      <p v-else-if="pair.draggableElement.type === 'audio'">
        Platzhalter für Audio Element
      </p>
    </template>
    <template v-if="pair.targetElement">
      <EditedImagePairImage
        v-if="pair.targetElement.type === 'image'"
        :image="pair.targetElement"
      />
      <p v-else-if="pair.targetElement.type === 'text'">
        Platzhalter für Text Element
      </p>
      <p v-else-if="pair.targetElement.type === 'audio'">
        Platzhalter für Audio Element
      </p>
    </template>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { $gettext } from '@/language/gettext';
import { Pair } from '@/models/TaskDefinition';
import EditedImagePairImage from '@/components/EditedImagePairImage.vue';

export default defineComponent({
  name: 'ElementPair',
  components: { EditedImagePairImage },
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
</style>
