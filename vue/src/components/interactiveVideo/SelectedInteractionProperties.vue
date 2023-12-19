<template>
  <form
    v-if="selectedInteraction"
    class="default selected-interaction-properties"
  >
    <fieldset>
      <legend>
        {{ printInteractionType(selectedInteraction) }}
        {{ $gettext('bearbeiten') }}
      </legend>
      <label v-if="selectedInteraction.type === 'pause'">
        {{ $gettext('Zeitpunkt') }}

        <!-- eslint-disable-next-line vue/no-mutating-props-->
        <input type="number" v-model="selectedInteraction.startTime" />
      </label>
      <label v-else>
        {{ $gettext('Start') }}
        <!-- eslint-disable-next-line vue/no-mutating-props-->
        <input type="number" v-model="selectedInteraction.startTime" />
        {{ $gettext('Ende') }}
        <!-- eslint-disable-next-line vue/no-mutating-props-->
        <input type="number" v-model="selectedInteraction.endTime" />
      </label>
    </fieldset>
    <KeepAlive>
      <component
        v-if="selectedInteraction.type === 'lmbTask'"
        :key="`${selectedInteraction.id}-viewer`"
        :is="viewerForTaskType(selectedInteraction.taskDefinition.task_type)"
        :task="selectedInteraction.taskDefinition"
      />
    </KeepAlive>
    <KeepAlive>
      <component
        v-if="selectedInteraction.type === 'lmbTask'"
        :key="`${selectedInteraction.id}-editor`"
        :is="editorForTaskType(selectedInteraction.taskDefinition.task_type)"
        :taskDefinition="selectedInteraction.taskDefinition"
      />
    </KeepAlive>
  </form>
</template>
<script lang="ts">
import { PropType } from 'vue';
import {
  Interaction,
  printInteractionType,
} from '@/models/InteractiveVideoTask';
import { editorForTaskType, viewerForTaskType } from '@/models/TaskDefinition';
import { $gettext } from '../../language/gettext';

export default {
  name: 'SelectedInteractionProperties',
  methods: {
    viewerForTaskType,
    $gettext,
    printInteractionType,
    editorForTaskType,
  },
  props: {
    selectedInteraction: {
      type: Object as PropType<Interaction>,
      required: true,
    },
  },
};
</script>
<style scoped lang="scss">
.selected-interaction-properties {
  margin-top: 2em;
}
</style>
