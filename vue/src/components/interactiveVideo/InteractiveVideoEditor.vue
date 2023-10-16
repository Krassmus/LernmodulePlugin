<script lang="ts">
import { defineComponent } from 'vue';
import { taskEditorStore } from '@/store';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import CoursewareTabs from '@/components/interactiveVideo/CoursewareTabs.vue';
import CoursewareTab from '@/components/interactiveVideo/CoursewareTab.vue';

export default defineComponent({
  name: 'InteractiveVideoEditor',
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as InteractiveVideoTask,
  },
  components: {
    CoursewareTab,
    CoursewareTabs,
  },
});
</script>

<template>
  <div>Editor for interactive video</div>
  <CoursewareTabs>
    <CoursewareTab :index="0" :name="'Video hinzufügen'" :selected="true">
      First tab -- here you add the video
      <pre>{{ taskDefinition.video }}</pre>
    </CoursewareTab>
  </CoursewareTabs>
  <div>
    Task definition:
    <pre>{{ taskDefinition }}</pre>
  </div>
</template>

<style scoped></style>
