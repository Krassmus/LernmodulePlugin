<template>
  <span>{{ task.template }}</span>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MarkTheWordsTaskDefinition } from '@/models/TaskDefinition';

export default defineComponent({
  name: 'MarkTheWordsViewer',
  props: {
    task: {
      type: Object as PropType<MarkTheWordsTaskDefinition>,
      required: true,
    },
  },
  data() {
    return {};
  },
  methods: {},
  computed: {},
});
</script>

<style scoped></style>
