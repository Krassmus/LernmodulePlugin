<template>
  <div class="h5pElementPair">
    <div class="h5pElement">
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
    </div>
    <div class="h5pElement">
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
    </div>
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
    pairIndex: {
      type: Number as PropType<number>,
      required: true,
    },
  },
  data() {
    return {};
  },
  methods: {
    $gettext,
  },
  computed: {},
});
</script>

<style scoped>
.h5pElementPair {
  display: flex;
  flex: 0 0 auto;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  border: rgba(0, 0, 0, 0) 2px solid;
  border-radius: 0.25em;
  gap: 0.25em;
  padding: 0.25em;
}

.h5pElement {
  border: #888888 1px solid;
  border-radius: 0.25em;
  padding: 0.25em;
  width: 8em;
  height: 8em;
}

.selected {
  border: #0a78d1 2px solid;
}
</style>
