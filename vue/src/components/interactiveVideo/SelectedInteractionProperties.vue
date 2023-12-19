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
        <input type="number" class="time-input" v-model="inputStartTime" />
      </label>
      <label v-else>
        {{ $gettext('Start') }}
        <!-- eslint-disable-next-line vue/no-mutating-props-->
        <input type="number" class="time-input" v-model="inputStartTime" />
        {{ $gettext('Ende') }}
        <!-- eslint-disable-next-line vue/no-mutating-props-->
        <input type="number" class="time-input" v-model="inputEndTime" />
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
import { inject, PropType } from 'vue';
import {
  Interaction,
  printInteractionType,
} from '@/models/InteractiveVideoTask';
import { editorForTaskType, viewerForTaskType } from '@/models/TaskDefinition';
import { $gettext } from '../../language/gettext';
import {
  EditorState,
  editorStateSymbol,
} from '@/components/interactiveVideo/editorState';

export default {
  name: 'SelectedInteractionProperties',
  setup() {
    return {
      editor: inject<EditorState>(editorStateSymbol),
    };
  },
  props: {
    selectedInteraction: {
      type: Object as PropType<Interaction>,
      required: true,
    },
  },
  data() {
    return {
      // inputStartTime
    };
  },
  methods: {
    viewerForTaskType,
    $gettext,
    printInteractionType,
    editorForTaskType,
    validateStartTime(value: number): boolean {
      // return value > 0 && value < this.editor!.
      //  TODO check video length as well.
      if (this.selectedInteraction.type === 'pause') {
        return value > 0;
      } else {
        return value > 0 && value < this.selectedInteraction.endTime;
      }
    },
  },
  computed: {
    inputStartTime: {
      get() {
        return this.selectedInteraction.startTime;
      },
      set(value: number) {
        if (this.validateStartTime(value)) {
          // eslint-disable-next-line vue/no-mutating-props
          this.selectedInteraction.startTime = value;
        }
      },
    },
    inputEndTime: {
      get() {
        return this.selectedInteraction.endTime;
      },
      set(value: number) {
        // eslint-disable-next-line vue/no-mutating-props
        this.selectedInteraction.endTime = value;
      },
    },
  },
};
</script>
<style scoped lang="scss">
.selected-interaction-properties {
  margin-top: 2em;
}
</style>
