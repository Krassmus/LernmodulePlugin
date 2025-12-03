<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import {
  InteractiveVideoTask,
  TravisGoSettings,
} from '@/models/InteractiveVideoTask';
import { $gettext } from '@/language/gettext';
import TabsComponent from '@/components/courseware-components-ported-to-vue3/TabsComponent.vue';
import TabComponent from '@/components/courseware-components-ported-to-vue3/TabComponent.vue';
import AddInteractions from '@/components/interactiveVideo/AddInteractions.vue';
import SelectVideo from '@/components/interactiveVideo/SelectVideo.vue';
import ConfigureTravisGo from '@/components/interactiveVideo/ConfigureTravisGo.vue';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import produce from 'immer';

export default defineComponent({
  name: 'InteractiveVideoEditor',
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  methods: {
    $gettext,
    updateTravisGoSettings(payload: {
      settings: TravisGoSettings;
      undoBatch?: unknown;
    }) {
      this.taskEditor!.performEdit({
        newTaskDefinition: produce(this.taskDefinition, (draft) => {
          draft.travisGoSettings = payload.settings;
        }),
        undoBatch: payload.undoBatch,
      });
    },
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
      <ConfigureTravisGo
        :settings="taskDefinition.travisGoSettings"
        @update="updateTravisGoSettings"
      />
    </TabComponent>
  </TabsComponent>
  <div v-if="false">
    Task definition:
    <pre>{{ taskDefinition }}</pre>
  </div>
</template>

<style scoped></style>
