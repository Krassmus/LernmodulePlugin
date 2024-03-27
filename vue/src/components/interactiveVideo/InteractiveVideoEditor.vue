<!-- Allow us to mutate the prop 'taskDefinition' as much as we want-->
<!-- eslint-disable vue/no-mutating-props -->
<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import { $gettext } from '@/language/gettext';
import TabsComponent from '@/components/courseware-components-ported-to-vue3/TabsComponent.vue';
import TabComponent from '@/components/courseware-components-ported-to-vue3/TabComponent.vue';
import AddInteractions from '@/components/interactiveVideo/AddInteractions.vue';
import SelectVideo from '@/components/interactiveVideo/SelectVideo.vue';

export default defineComponent({
  name: 'InteractiveVideoEditor',
  data() {
    return {};
  },
  methods: {
    $gettext,
  },
  props: {
    taskDefinition: {
      type: Object as PropType<InteractiveVideoTask>,
      required: true,
    },
  },
  computed: {},
  components: {
    SelectVideo,
    AddInteractions,
    TabComponent,
    TabsComponent,
  },
});
</script>

<template>
  <TabsComponent>
    <TabComponent :title="$gettext('1. Video auswählen')" icon="video2">
      <SelectVideo :task-definition="taskDefinition" />
    </TabComponent>
    <TabComponent
      :title="$gettext('2. Interaktionen hinzufügen')"
      icon="content"
    >
      <AddInteractions :task-definition="taskDefinition" />
    </TabComponent>
  </TabsComponent>
  <div style="display: none">
    Task definition:
    <pre>{{ taskDefinition }}</pre>
  </div>
</template>

<style scoped></style>
