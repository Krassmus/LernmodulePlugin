<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import { $gettext } from '@/language/gettext';
import TabsComponent from '@/components/courseware-components-ported-to-vue3/TabsComponent.vue';
import TabComponent from '@/components/courseware-components-ported-to-vue3/TabComponent.vue';
import AddInteractions from '@/components/interactiveVideo/AddInteractions.vue';
import SelectVideo from '@/components/interactiveVideo/SelectVideo.vue';
import ConfigureTravisGo from '@/components/interactiveVideo/ConfigureTravisGo.vue';

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
    ConfigureTravisGo,
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
    <TabComponent
      :title="$gettext('3. Travis Go konfigurieren')"
      icon="visibility-visible"
    >
      <ConfigureTravisGo :settings="taskDefinition.travisGoSettings" />
    </TabComponent>
  </TabsComponent>
  <div v-if="false">
    Task definition:
    <pre>{{ taskDefinition }}</pre>
  </div>
</template>

<style scoped></style>
