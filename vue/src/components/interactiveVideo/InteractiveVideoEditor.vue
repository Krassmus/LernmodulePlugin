<script lang="ts">
import { defineComponent } from 'vue';
import { taskEditorStore } from '@/store';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import { $gettext } from '@/language/gettext';
import TabsComponent from '@/components/interactiveVideo/TabsComponent.vue';
import TabComponent from '@/components/interactiveVideo/TabComponent.vue';

export default defineComponent({
  name: 'InteractiveVideoEditor',
  methods: { $gettext },
  computed: {
    taskDefinition: () =>
      taskEditorStore.taskDefinition as InteractiveVideoTask,
  },
  components: {
    TabComponent,
    TabsComponent,
  },
});
</script>

<template>
  <div>Editor for interactive video</div>
  <TabsComponent>
    <TabComponent :title="$gettext('1. Video auswählen')" icon="video2">
      First tab -- here you add the video
      <pre>{{ taskDefinition.video }}</pre>
    </TabComponent>
    <TabComponent
      :title="$gettext('2. Interaktionen hinzufügen')"
      icon="content"
    >
      Second tab -- Here you add the interactions
      <pre>{{ taskDefinition.interactions }}</pre>
    </TabComponent>
  </TabsComponent>
  <div>
    Task definition:
    <pre>{{ taskDefinition }}</pre>
  </div>
</template>

<style scoped></style>
