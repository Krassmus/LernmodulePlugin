<template>
  <div class="selected-interaction-properties">
    <h3>
      {{ $gettext('Interaktion') }}
      "{{ printInteractionType(selectedInteraction) }}"
    </h3>
    <form v-if="selectedInteraction" class="default">
      <fieldset class="collapsable">
        <legend>
          {{ $gettext('Einstellungen') }}
        </legend>
        <div class="start-and-end-time-inputs">
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
        </div>
        <p
          class="validation-error"
          v-for="error in inputEndTimeErrors"
          :key="error"
        >
          {{ error }}
        </p>
        <label>
          <!-- TODO don't mutate props -- this should be undoable -->
          <!-- eslint-disable vue/no-mutating-props -->
          <input
            type="checkbox"
            v-model="selectedInteraction.pauseWhenVisible"
          />
          {{ $gettext('Video pausieren') }}
        </label>
      </fieldset>
    </form>
    <template v-if="selectedInteraction.type === 'overlay'">
      <!-- Oddly, if this StudipWysiwyg is not wrapped in a div, it seems not to
      work correctly. It keeps firing its mounted() function when hidden/shown,
      (e.g. when you alternate between selecting an overlay and selecting another
      Interaction), and it leads to many instances of the WYSIWYG editor
      appearing next to each other.
      I don't entirely understand it, but this is my workaround for the issue. -Ann -->
      <KeepAlive>
        <div>
          <StudipWysiwyg
            :key="`${selectedInteraction.id}-overlay-wysiwyg`"
            v-model="selectedInteraction.content_wysiwyg"
          />
        </div>
      </KeepAlive>
    </template>
    <template v-else-if="selectedInteraction.type === 'lmbTask'">
      <KeepAlive>
        <component
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
          class="lmb-task-preview"
          :key="`${selectedInteraction.id}-viewer`"
          :is="viewerForTaskType(selectedInteraction.taskDefinition.task_type)"
          :task="selectedInteraction.taskDefinition"
        />
      </KeepAlive>
    </template>
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
  InteractiveVideoEditorState,
  interactiveVideoEditorStateSymbol,
} from '@/components/interactiveVideo/interactiveVideoEditorState';
import VideoTimeInput from '@/components/interactiveVideo/VideoTimeInput.vue';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';

export default defineComponent({
  name: 'SelectedInteractionProperties',
  components: { VideoTimeInput, StudipWysiwyg },
  setup() {
    return {
      editor: inject<InteractiveVideoEditorState>(
        interactiveVideoEditorStateSymbol
      ),
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
.start-and-end-time-inputs {
  display: flex;
  gap: 1em;
}
.lmb-task-preview {
  margin-bottom: 1em;
}
</style>
