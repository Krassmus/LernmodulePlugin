<template>
  <form
    v-if="selectedInteraction"
    class="default selected-interaction-properties"
  >
    <fieldset ref="timeInputsFieldset">
      <legend>
        {{ printInteractionType(selectedInteraction) }}
        {{ $gettext('bearbeiten') }}
      </legend>
      <label>
        {{ $gettext('Start') }}
        <input
          type="number"
          v-model.number="inputStartTime"
          class="time-input"
          :class="{
            invalid: inputStartTimeErrors.length > 0,
          }"
          :aria-invalid="inputStartTimeErrors.length > 0"
          @blur="onBlurTimeInputs"
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
          <input
            type="number"
            v-model.number="inputEndTime"
            class="time-input"
            :class="{
              invalid: inputEndTimeErrors.length > 0,
            }"
            :aria-invalid="inputEndTimeErrors.length > 0"
            @blur="onBlurTimeInputs"
          />
        </label>
        <p
          class="validation-error"
          v-for="error in inputEndTimeErrors"
          :key="error"
        >
          {{ error }}
        </p>
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
import { defineComponent, inject, PropType } from 'vue';
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

export default defineComponent({
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
      inputEndTime: NaN,
    };
  },
  methods: {
    viewerForTaskType,
    $gettext,
    printInteractionType,
    editorForTaskType,
    onBlurTimeInputs(event: FocusEvent) {
      const isFocusWithinTimeInputs = (
        this.$refs.timeInputsFieldset as Node
      ).contains(event.relatedTarget as Node);
      if (isFocusWithinTimeInputs) {
        // Don't reset the fields' input values if the user is still editing the
        // start/end times
        return;
      }
      this.inputStartTime = this.selectedInteraction.startTime;
      if (this.selectedInteraction.type !== 'pause') {
        this.inputEndTime = this.selectedInteraction.endTime;
      }
      console.log(
        'onblur',
        this.selectedInteraction.startTime,
        this.inputStartTime,
        (this.selectedInteraction as { endTime?: number })?.endTime,
        this.inputEndTime
      );
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
    inputEndTime(value: number) {
      if (this.inputEndTimeErrors.length === 0) {
        if (this.selectedInteraction.type === 'pause') {
          console.error(
            'Unexpected end time input for "pause" interaction. (A "pause" has ' +
              'no end time.) Ignoring.'
          );
          return;
        }
        // eslint-disable-next-line vue/no-mutating-props
        this.selectedInteraction.endTime = value;
        /*
         Scenario: startTime = 0, endTime = 4.
         User begins to edit startTime and sets startTime = 5.
         This is invalid (startTime > endTime) and is not applied.
         Then, user edits endTime and sets endTime = 6.
         Now, both inputs are valid. start < end. So, both should be applied.
        */
        if (this.inputStartTimeErrors.length === 0) {
          // eslint-disable-next-line vue/no-mutating-props
          this.selectedInteraction.startTime = this.inputStartTime;
        }
      }
    },
    'selectedInteraction.endTime': {
      handler: function (value: number) {
        this.inputEndTime = value;
      },
      immediate: true,
    },
  },
  computed: {
    inputStartTimeErrors(): string[] {
      const errors: string[] = [];
      if (!(typeof this.inputStartTime === 'number')) {
        errors.push($gettext('Das eingegebene Wert muss eine Nummer sein.'));
      }
      if (this.inputStartTime < 0) {
        errors.push($gettext('Das eingegebene Wert muss größer als 0 sein.'));
      }
      if (this.selectedInteraction.type !== 'pause') {
        if (this.inputStartTime >= this.selectedInteraction.endTime) {
          errors.push($gettext('Der Startpunkt muss vor dem Endpunkt sein.'));
        }
        // TODO check video length as well.
      }
      return errors;
    },
    inputEndTimeErrors(): string[] {
      const errors: string[] = [];
      if (!(typeof this.inputEndTime === 'number')) {
        errors.push($gettext('Das eingegebene Wert muss eine Nummer sein.'));
      }
      if (this.inputEndTime < 0) {
        errors.push($gettext('Das eingegebene Wert muss größer als 0 sein.'));
      }
      if (this.selectedInteraction.type !== 'pause') {
        if (this.inputEndTime <= this.selectedInteraction.startTime) {
          errors.push($gettext('Der Startpunkt muss vor dem Endpunkt sein.'));
        }
        // TODO check video length as well.
      }
      return errors;
    },
  },
});
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
