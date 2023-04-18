<template>
  <div class="h5pMemoryGame">
    <MemoryCard
      v-for="card in this.task.cards"
      :key="card.uuid"
      :card="card"
    ></MemoryCard>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MemoryTaskDefinition } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import MemoryCard from '@/components/MemoryCard.vue';

export default defineComponent({
  name: 'MemoryViewer',
  components: { MemoryCard },
  emits: ['updateAttempt'],
  props: {
    task: {
      type: Object as PropType<MemoryTaskDefinition>,
      required: true,
    },
  },
  data() {
    return {
      selectedCardIndex: -1,
    };
  },
  methods: { $gettext },
  computed: {},
});
</script>

<style scoped>
.h5pMemoryGame {
  display: grid;
  grid-gap: 1em;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  /*grid-template-rows: repeat(auto-fit, minmax(250px, 1fr));*/
}
</style>
