<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import { $gettext } from '@/language/gettext';
import TabsComponent from '@/components/interactiveVideo/TabsComponent.vue';
import TabComponent from '@/components/interactiveVideo/TabComponent.vue';

export default defineComponent({
  name: 'InteractiveVideoEditor',
  methods: { $gettext },
  props: {
    taskDefinition: {
      type: Object as PropType<InteractiveVideoTask>,
      required: true,
    },
  },
  computed: {},
  components: {
    TabComponent,
    TabsComponent,
  },
});
</script>

<template>
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
