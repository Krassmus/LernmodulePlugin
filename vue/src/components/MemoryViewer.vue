<template>
  <div v-for="card in this.task.cards" :key="card.uuid">
    {{ card.question }}
  </div>
  <pre>{{ this.task }}</pre>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MemoryTaskDefinition } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'MemoryViewer',
  components: {},
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

<style scoped></style>
