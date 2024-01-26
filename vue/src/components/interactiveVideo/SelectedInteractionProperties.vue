<template>
  <div class="selected-interaction-properties">
    <h3>
      {{ $gettext('Interaktion') }}
      "{{ printInteractionType(selectedInteraction) }}"
    </h3>
    <form v-if="selectedInteraction" class="default">
      <fieldset ref="timeInputsFieldset">
        <legend>
          {{ $gettext('Zeitpunkt') }}
        </legend>
        <label>
          {{ $gettext('Start') }}
          <VideoTimeInput
            v-model="inputStartTime"
            @update:error="(error) => (inputStartTimeFormatError = error)"
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
        <label>
          {{ $gettext('Ende') }}
          <VideoTimeInput
            v-model="inputEndTime"
            @update:error="(error) => (inputEndTimeFormatError = error)"
            class="time-input"
            :class="{
              invalid: inputEndTimeErrors.length > 0,
            }"
            :aria-invalid="inputEndTimeErrors.length > 0"
          />
        </label>
        <p
          class="validation-error"
          v-for="error in inputEndTimeErrors"
          :key="error"
        >
          {{ error }}
        </p>
      </fieldset>
    </form>
    <KeepAlive>
      <component
        v-if="selectedInteraction.type === 'lmbTask'"
        :key="`${selectedInteraction.id}-editor`"
        :is="editorForTaskType(selectedInteraction.taskDefinition.task_type)"
        :taskDefinition="selectedInteraction.taskDefinition"
      />
    </KeepAlive>
    <h3>
      {{ $gettext('Vorschau') }}
    </h3>
    <KeepAlive>
      <component
        v-if="selectedInteraction.type === 'lmbTask'"
        class="lmb-task-preview"
        :key="`${selectedInteraction.id}-viewer`"
        :is="viewerForTaskType(selectedInteraction.taskDefinition.task_type)"
        :task="selectedInteraction.taskDefinition"
      />
    </KeepAlive>
  </div>
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
import VideoTimeInput from '@/components/interactiveVideo/VideoTimeInput.vue';

export default defineComponent({
  name: 'SelectedInteractionProperties',
  components: { VideoTimeInput },
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
      inputStartTime: 0,
      inputStartTimeFormatError: undefined as Error | undefined,
      inputEndTime: 1,
      inputEndTimeFormatError: undefined as Error | undefined,
    };
  },
  methods: {
    viewerForTaskType,
    $gettext,
    printInteractionType,
    editorForTaskType,
  },
  watch: {
    inputStartTime(value: number) {
      if (this.inputStartTimeErrors.length === 0) {
        // eslint-disable-next-line vue/no-mutating-props
        this.selectedInteraction.startTime = value;
      }
      if (this.inputEndTimeErrors.length === 0) {
        // eslint-disable-next-line vue/no-mutating-props
        this.selectedInteraction.endTime = this.inputEndTime;
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
      if (this.inputStartTimeFormatError) {
        errors.push(this.inputStartTimeFormatError.message);
      }
      if (this.inputStartTime < 0) {
        errors.push($gettext('Das eingegebene Wert muss größer als 0 sein.'));
      }
      if (this.inputStartTime >= this.selectedInteraction.endTime) {
        errors.push($gettext('Der Startpunkt muss vor dem Endpunkt sein.'));
      }
      // TODO check video length as well.
      return errors;
    },
    inputEndTimeErrors(): string[] {
      const errors: string[] = [];
      if (this.inputEndTimeFormatError) {
        errors.push(this.inputEndTimeFormatError.message);
      }
      if (this.inputEndTime < 0) {
        errors.push($gettext('Das eingegebene Wert muss größer als 0 sein.'));
      }
      if (this.inputEndTime <= this.selectedInteraction.startTime) {
        errors.push($gettext('Der Startpunkt muss vor dem Endpunkt sein.'));
      }
      // TODO check video length as well.
      return errors;
    },
  },
});
</script>
<style scoped lang="scss">
form.default .time-input {
  max-width: 12em;
}
.lmb-task-preview {
  margin-bottom: 1em;
}
</style>
