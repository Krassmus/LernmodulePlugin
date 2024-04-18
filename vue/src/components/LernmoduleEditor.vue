<template>
  <!--  <h1>Variables passed from server:</h1>-->
  <!--  <pre>{{ LernmoduleVueJS }}</pre>-->
  <teleport to="#sidebar-actions .sidebar-widget-content .widget-list">
    <li
      class="save-row"
      @click="saveTask"
      :class="saveStatus.status !== 'saving' ? 'action' : 'action disabled'"
    >
      <span>{{ $gettext('Speichern') }}</span>
      <span
        :class="
          saveStatus.status === 'saved' && this.hasUnsavedChanges
            ? 'save-status-modified'
            : ''
        "
        >{{ saveStatusText }}</span
      >
    </li>
    <template v-if="LernmoduleVueJS.LERNMODULE_DEBUG">
      <!-- As the undo/redo functionality is still not complete, these buttons
           are to be hidden except when in development mode. -->
      <li @click="undo" :class="canUndo ? 'action' : 'action disabled'">
        {{ $pgettext('Im Sinne von undo/redo', 'Rückgängig') }}
      </li>
      <li @click="redo" :class="canRedo ? 'action' : 'action disabled'">
        {{ $pgettext('Im Sinne von undo/redo', 'Wiederherstellen') }}
      </li>
    </template>
  </teleport>

  <form class="default">
    <fieldset>
      <legend>{{ $gettext('Grunddaten') }}</legend>
      <label>
        {{ $gettext('Titel') }}
        <input type="text" :value="moduleName" @input="onInputModuleName" />
      </label>
      <label>
        {{ $gettext('Aufgabenbeschreibung') }}
        <studip-wysiwyg
          :model-value="infotext"
          id="ckeditorElement"
          @update:modelValue="onInputInfotext"
          insert-html-comment
          remove-wrapping-p-tag
        />
      </label>
    </fieldset>
  </form>
  <div style="margin-bottom: 1em; margin-top: 1em">
    <label>{{ $gettext('Aufgabentyp auswählen:') }}</label>
    <select
      :value="taskDefinition.task_type"
      @input="onSelectTaskType"
      style="margin-left: 0.5em"
    >
      <option value="FillInTheBlanks">
        {{ $gettext('Fill In The Blanks') }}
      </option>
      <option value="FindTheHotspot">{{ $gettext('Find The Hotspot') }}</option>
      <option value="FindTheWords">{{ $gettext('Find The Words') }}</option>
      <option value="Question">{{ $gettext('Question') }}</option>
      <option value="DragTheWords">{{ $gettext('Drag The Words') }}</option>
      <option value="MarkTheWords">{{ $gettext('Mark The Words') }}</option>
      <option value="ImagePairing">{{ $gettext('Image Pairing') }}</option>
      <option value="ImageSequencing">
        {{ $gettext('Image Sequencing') }}
      </option>
    </select>
  </div>

  <div>
    <component
      :is="editorForTaskType(taskDefinition.task_type)"
      :taskDefinition="taskDefinition"
    />

    <div class="save-undo-redo">
      <button
        class="button"
        @click="saveTask"
        :disabled="saveStatus.status === 'saving'"
      >
        {{ $gettext('Speichern') }}
      </button>
    </div>

    <h1 style="margin-top: 1em">{{ $gettext('Vorschau') }}</h1>
    <component
      :is="viewerForTaskType(taskDefinition.task_type)"
      :task="taskDefinition"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  editorForTaskType,
  newTask,
  TaskDefinition,
  viewerForTaskType,
} from '@/models/TaskDefinition';
import { taskEditorStore } from '@/store';
import { $gettext, $pgettext } from '@/language/gettext';
import StudipWysiwyg from '@/components/StudipWysiwyg.vue';

export default defineComponent({
  name: 'LernmoduleEditor',
  components: { StudipWysiwyg },
  mounted() {
    // Prompt about unsaved changes when leaving the page
    window.addEventListener('beforeunload', this.onBeforeUnload, {
      capture: true,
    });
  },
  unmounted() {
    window.removeEventListener('beforeunload', this.onBeforeUnload, {
      capture: true,
    });
  },
  computed: {
    LernmoduleVueJS: () => window.STUDIP.LernmoduleVueJS,
    taskDefinition: () => taskEditorStore.taskDefinition,
    moduleName: () => taskEditorStore.moduleName,
    infotext: () => taskEditorStore.infotext,
    saveStatus: () => taskEditorStore.saveStatus,
    hasUnsavedChanges: () => taskEditorStore.hasUnsavedChanges,
    canUndo: () => taskEditorStore.canUndo,
    canRedo: () => taskEditorStore.canRedo,
    saveStatusText(): string {
      switch (this.saveStatus.status) {
        case 'saved':
          if (this.hasUnsavedChanges) {
            return $gettext('Modifiziert');
          } else {
            return $gettext('Gespeichert');
          }
        case 'saving':
          return $gettext('Wird gespeichert...');
        case 'error':
          return $gettext('Beim Speichern ist ein Fehler aufgetreten.');
      }
      return '';
    },
  },
  methods: {
    $pgettext,
    $gettext,
    editorForTaskType,
    viewerForTaskType,
    saveTask: taskEditorStore.saveTask,
    onBeforeUnload(event: BeforeUnloadEvent) {
      if (this.hasUnsavedChanges) {
        event.preventDefault();
        return (event.returnValue = true);
      }
    },
    undo: taskEditorStore.undo,
    redo: taskEditorStore.redo,
    onInputModuleName(event: InputEvent) {
      const name = (event.target as HTMLInputElement).value;
      taskEditorStore.setModuleName(name);
    },
    onInputInfotext(modelValue: string) {
      taskEditorStore.setInfoText(modelValue);
    },
    onSelectTaskType(event: InputEvent): void {
      const taskType = (event.target as HTMLInputElement).value;
      taskEditorStore.performEdit({
        newTaskDefinition: newTask(taskType as TaskDefinition['task_type']),
        undoBatch: {},
      });
    },
  },
});
</script>

<style scoped>
.action {
  cursor: pointer;
  color: #28497c;
}

.action.disabled {
  color: #aaa;
  cursor: not-allowed;
}

.save-row {
  display: flex;
  justify-content: space-between;
  padding-right: 0.5em;
}

.save-status-modified {
  font-weight: bold;
}

.save-status-modified::after {
  content: '*';
  color: red;
}

.task-name-input {
  flex-grow: 1;
  border: none;
}

.task-name-input:focus {
  outline: none;
}
</style>
