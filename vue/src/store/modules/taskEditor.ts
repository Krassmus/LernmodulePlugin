import { Action, Module, Mutation, VuexModule } from 'vuex-module-decorators';
import {
  newTask,
  TaskDefinition,
  taskDefinitionSchema,
} from '@/models/TaskDefinition';
import { isArray, isEqual } from 'lodash';
import { saveTask, SaveTaskResponse } from '@/routes/lernmodule';
import { setAutoFreeze } from 'immer';
import { formatInvalidTaskDefinitionErrorMessage } from '@/functions';
import { InitializeMessage } from '@/models/CoursewareBlockIframeMessages';

// Prevent immer from freezing objects.  This behavior causes trouble when we
// attempt to use v-model with an object produced by immer, because v-model
// works by mutating objects.
setAutoFreeze(false);

type Saved = {
  status: 'saved';
};
type Saving = {
  status: 'saving';
};
type SaveError = {
  status: 'error';
  error: unknown;
};
type SaveStatus = Saved | Saving | SaveError;

interface UndoRedoState {
  taskDefinition: TaskDefinition;
  undoBatch: unknown;
}

type TaskSaveLocation =
  | {
      type: 'cw_blocks';
      id: string; // Corresponds to cw_blocks.id database column
    }
  | {
      type: 'lernmodule_module';
      id: string; // Corresponds to lernmodule_module.module_id database column
    };

function sleep(ms: number) {
  return new Promise((r) => setTimeout(r, ms));
}

@Module({ name: 'taskEditor' })
export class TaskEditorModule extends VuexModule {
  saveStatus: SaveStatus = { status: 'saved' };
  fatalErrors: string[] = [];
  nonFatalErrors: string[] = [];
  serverTaskDefinition: TaskDefinition | [] = [];
  serverModuleName: string = '';
  serverInfotext: string = '';
  moduleName: string = '';
  infotext: string = '';
  taskSaveLocation: TaskSaveLocation | null = null;
  undoRedoStack: UndoRedoState[] = [
    {
      taskDefinition: newTask('FillInTheBlanks'),
      undoBatch: {},
    },
  ];
  undoRedoIndex = 0;

  get taskDefinition(): TaskDefinition {
    return this.undoRedoStack[this.undoRedoIndex].taskDefinition;
  }

  get hasUnsavedChanges(): boolean {
    return (
      !isEqual(this.taskDefinition, this.serverTaskDefinition) ||
      this.serverModuleName !== this.moduleName ||
      this.serverInfotext !== this.infotext
    );
  }

  get canUndo() {
    return this.undoRedoIndex > 0;
  }

  get canRedo() {
    return this.undoRedoIndex < this.undoRedoStack.length - 1;
  }

  @Mutation
  undo() {
    if (this.undoRedoIndex > 0) {
      const currentState = this.undoRedoStack[this.undoRedoIndex];
      this.undoRedoIndex--;

      // Focus the input field whose contents will change as a result of this undo.
      // That's how undo/redo behaves in native apps like Microsoft Word.
      // When you press undo, it scrolls to the point in the document where
      // the undo took place.  This lets you observe the result of your action.
      const inputElements = document.querySelectorAll(
        `[data-undo-focus-id="${currentState.undoBatch}"]`
      );
      if (inputElements.length > 0) {
        const inputEl = inputElements.item(0) as HTMLElement;
        // If the input is inside of a collapsed <fieldset>, then the <fieldset>
        // should be un-collapsed so that the user can see the input field.
        const collapsedParents = [
          ...document.querySelectorAll('fieldset.collapsable.collapsed'),
        ].filter((element) => element.contains(inputEl));
        collapsedParents.forEach((parent) =>
          parent.classList.remove('collapsed')
        );
        inputEl.focus();
      }
    }
  }

  @Mutation
  redo() {
    if (this.undoRedoIndex < this.undoRedoStack.length - 1) {
      this.undoRedoIndex++;
      const nextState = this.undoRedoStack[this.undoRedoIndex];

      // Manage focus -- see comments in undo()
      const inputElements = document.querySelectorAll(
        `[data-undo-focus-id="${nextState.undoBatch}"]`
      );
      if (inputElements.length > 0) {
        const inputEl = inputElements.item(0) as HTMLElement;
        const collapsedParents = [
          ...document.querySelectorAll('fieldset.collapsable.collapsed'),
        ].filter((element) => element.contains(inputEl));
        collapsedParents.forEach((parent) =>
          parent.classList.remove('collapsed')
        );
        inputEl.focus();
      }
    }
  }

  // Initialize the editor store to a state usable for courseware
  @Mutation
  initializeCourseware(payload: {
    initializeMessage: InitializeMessage;
    task_json: TaskDefinition;
  }) {
    console.info('initializeCourseware()', payload);
    this.serverTaskDefinition = payload.task_json;
    this.undoRedoStack = [
      { taskDefinition: this.serverTaskDefinition, undoBatch: {} },
    ];
    this.taskSaveLocation = {
      type: 'cw_blocks',
      id: payload.initializeMessage.block.id,
    };
  }

  // Initialize the editor store to a state usable for the non-courseware version
  // of Lernmodule (AKA the 'Lernmodule' tab in a course).
  @Mutation
  initializeNonCourseware() {
    // In Linux on Anns PC this is an empty array but on Windows on Thomas PC it's just NULL.
    // We don't know why this is, but for now, we will just accept it and handle both cases.
    if (
      window.STUDIP.LernmoduleVueJS.module.customdata &&
      !isArray(window.STUDIP.LernmoduleVueJS.module.customdata)
    ) {
      try {
        this.serverTaskDefinition = taskDefinitionSchema.parse(
          window.STUDIP.LernmoduleVueJS.module.customdata
        );
      } catch (e: unknown) {
        const errorMessage = formatInvalidTaskDefinitionErrorMessage(
          e,
          window.STUDIP.LernmoduleVueJS.module.customdata
        );
        console.error(errorMessage, e);
        console.error(
          'task_json: ',
          window.STUDIP.LernmoduleVueJS.module.customdata
        );
        throw new Error(errorMessage, { cause: e });
      }
      this.undoRedoStack = [
        { taskDefinition: this.serverTaskDefinition, undoBatch: {} },
      ];
    }
    this.serverModuleName = window.STUDIP.LernmoduleVueJS.module.name;
    this.serverInfotext = window.STUDIP.LernmoduleVueJS.infotext;
    this.moduleName = window.STUDIP.LernmoduleVueJS.module.name;
    this.infotext = window.STUDIP.LernmoduleVueJS.infotext;
    this.taskSaveLocation = {
      type: 'lernmodule_module',
      id: window.STUDIP.LernmoduleVueJS.module.module_id,
    };
  }

  @Mutation
  startedSaving() {
    this.saveStatus = { status: 'saving' };
  }

  @Mutation
  saveSuccess(payload: SaveTaskResponse) {
    this.saveStatus = { status: 'saved' };
    this.serverTaskDefinition = payload.taskDefinition;
    this.serverModuleName = payload.moduleName;
    this.serverInfotext = payload.infotext;
  }

  @Mutation
  saveFailure(error: unknown) {
    this.saveStatus = { status: 'error', error };
    this.nonFatalErrors.push(
      'The task could not be saved.  Check the console for more details. '
    );
    console.log(error);
  }

  @Action
  async saveTask() {
    if (this.saveStatus.status === 'saving') {
      return;
    }
    this.context.commit('startedSaving');
    try {
      const saveResult = await saveTask(
        this.taskDefinition,
        this.moduleName,
        this.infotext,
        window.STUDIP.LernmoduleVueJS.module.module_id,
        window.STUDIP.LernmoduleVueJS.block_id
      );
      await sleep(500);
      this.context.commit('saveSuccess', saveResult);
    } catch (error) {
      this.context.commit('saveFailure', { error });
    }
  }

  /**
   * Modify the currently edited task definition, creating a new undo/redo state
   * if appropriate.  Edits are batched together based on the parameter 'undoBatch'.
   * @param payload.taskDefinition The new state of the task being edited
   * @param payload.undoBatch (Optional) Any value.
   * If undoBatch is not provided or an empty object is provided, a new
   * undo/redo state will always be created.
   * Otherwise, a new undo/redo state will be created iff undoBatch is different
   * from the undoBatch of the previously performed action.
   */
  @Mutation
  performEdit(payload: {
    newTaskDefinition: TaskDefinition;
    undoBatch?: unknown;
  }) {
    if (!payload.undoBatch) {
      payload.undoBatch = {};
    }
    const currentUndoRedoState = this.undoRedoStack[this.undoRedoIndex];
    if (
      isEqual(currentUndoRedoState.taskDefinition, payload.newTaskDefinition)
    ) {
      return; // Do not pollute the undo-redo history with no-op changes
    }
    const shouldMergeEdits =
      !isEqual(payload.undoBatch, {}) &&
      isEqual(payload.undoBatch, currentUndoRedoState.undoBatch);
    if (shouldMergeEdits) {
      // Amend the last undo state instead of creating a new one.
      currentUndoRedoState.taskDefinition = payload.newTaskDefinition;
      this.undoRedoStack = this.undoRedoStack.slice(0, this.undoRedoIndex + 1);
    } else {
      // Create a new undo state and append it to the stack.
      this.undoRedoStack = this.undoRedoStack
        .slice(0, this.undoRedoIndex + 1)
        .concat([
          {
            taskDefinition: payload.newTaskDefinition,
            undoBatch: payload.undoBatch,
          },
        ]);
      this.undoRedoIndex += 1;
    }
  }

  @Mutation
  setModuleName(name: string) {
    this.moduleName = name;
  }

  @Mutation
  setInfoText(value: string) {
    this.infotext = value;
  }
}
