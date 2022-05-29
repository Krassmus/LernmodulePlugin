<template>
  <FillInTheBlanksSubtask
    v-for="(template, i) in this.task.templates"
    :key="i"
    :template="template"
  />
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { FillInTheBlanksDefinition } from '@/models/TaskDefinition';
import FillInTheBlanksSubtask from '@/components/FillInTheBlanksSubtask.vue';
export default defineComponent({
  components: { FillInTheBlanksSubtask },
  props: {
    task: {
      type: Object as PropType<FillInTheBlanksDefinition>,
      required: true,
    },
  },
  data() {
    return {};
  },
  computed: {},
});
</script>

<style scoped></style>
