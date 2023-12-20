<template>
  <form
    v-if="selectedInteraction"
    class="default show_validation_hints selected-interaction-properties"
  >
    <fieldset>
      <legend>
        {{ printInteractionType(selectedInteraction) }}
        {{ $gettext('bearbeiten') }}
      </legend>
      <label>
        {{ $gettext('Start') }}
        <input
          type="number"
          v-model="inputStartTime"
          class="time-input"
          :class="{
            invalid: inputStartTimeErrors.length > 0,
          }"
          :aria-invalid="inputStartTimeErrors.length > 0"
        />
      </label>
      <p
        class="validation-error"
        v-for="error in inputStartTimeErrors"
        :key="error"
      >
        {{ error }}
      </p>
      <template v-if="selectedInteraction.type !== 'pause'">
        <label>
          {{ $gettext('Ende') }}
          <!-- eslint-disable-next-line vue/no-mutating-props-->
          <input type="number" class="time-input" v-model="inputEndTime" />
        </label>
      </template>
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
      inputStartTime: NaN,
    };
  },
  methods: {
    viewerForTaskType,
    $gettext,
    printInteractionType,
    editorForTaskType,
    validateEndTime(value: number): boolean {
      //  TODO check video length as well.
      return value >= 0 && value > this.selectedInteraction.startTime;
    },
  },
  watch: {
    inputStartTime(value: number) {
      if (this.inputStartTimeErrors.length === 0) {
        // eslint-disable-next-line vue/no-mutating-props
        this.selectedInteraction.startTime = value;
      }
    },
    'selectedInteraction.startTime': {
      handler: function (value: number) {
        this.inputStartTime = value;
      },
      immediate: true,
    },
  },
  computed: {
    inputStartTimeErrors(): string[] {
      const errors: string[] = [];
      if (this.inputStartTime < 0) {
        errors.push($gettext('Das eingegebene Wert muss größer als 0 sein.'));
      }
      if (this.selectedInteraction.type !== 'pause') {
        if (this.inputStartTime >= this.selectedInteraction.endTime) {
          errors.push($gettext('Der Startpunkt muss vor dem Endpunkt kommen.'));
        }
        // TODO check video length as well.
      }
      return errors;
    },
    inputEndTime: {
      get() {
        return this.selectedInteraction.endTime;
      },
      set(value: number) {
        if (this.validateEndTime(value)) {
          // eslint-disable-next-line vue/no-mutating-props
          this.selectedInteraction.endTime = value;
        }
      },
    },
  },
};
</script>
<style scoped lang="scss">
form.default .time-input {
  max-width: 12em;
}
.selected-interaction-properties {
  fieldset {
    width: 100%;
  }
}
</style>
