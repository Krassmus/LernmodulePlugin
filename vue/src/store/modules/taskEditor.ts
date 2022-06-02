import { Action, Module, Mutation, VuexModule } from 'vuex-module-decorators';
import { newTask, TaskDefinition } from '@/models/TaskDefinition';
import { isArray, isEqual } from 'lodash';
import { saveTask } from '@/routes';

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
  undoBatch: object;
}

function sleep(ms: number) {
  return new Promise((r) => setTimeout(r, ms));
}

@Module({ name: 'taskEditor' })
export class TaskEditorModule extends VuexModule {
  saveStatus: SaveStatus = { status: 'saved' };
  fatalErrors: string[] = [];
  nonFatalErrors: string[] = [];
  serverTaskDefinition: TaskDefinition | [] = [];
  moduleName: string = '';
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
    return !isEqual(this.taskDefinition, this.serverTaskDefinition);
  }

  get canUndo() {
    return this.undoRedoIndex > 0;
  }

  get canRedo() {
    return this.undoRedoIndex < this.undoRedoStack.length - 1;
  }

  @Mutation
  undo() {
    this.undoRedoIndex = Math.max(0, this.undoRedoIndex - 1);
  }

  @Mutation
  redo() {
    this.undoRedoIndex = Math.min(
      this.undoRedoStack.length - 1,
      this.undoRedoIndex + 1
    );
  }

  @Mutation
  initialize() {
    // In Linux on Anns PC this is an empty array but on Windows on Thomas PC it's just NULL.
    // We don't know why this is, but for now, we will just accept it and handle both cases.
    if (
      window.STUDIP.LernmoduleVueJS.module.customdata &&
      !isArray(window.STUDIP.LernmoduleVueJS.module.customdata)
    ) {
      // TODO: Warning!! Bad!! You should parse the contents, do not just type-cast!!
      this.serverTaskDefinition = window.STUDIP.LernmoduleVueJS.module
        .customdata as TaskDefinition;
      this.undoRedoStack = [
        { taskDefinition: this.serverTaskDefinition, undoBatch: {} },
      ];
    }
    this.moduleName = window.STUDIP.LernmoduleVueJS.module.name;
  }

  @Mutation
  startedSaving() {
    this.saveStatus = { status: 'saving' };
  }

  @Mutation
  saveSuccess(taskDefinition: TaskDefinition) {
    this.saveStatus = { status: 'saved' };
    this.serverTaskDefinition = taskDefinition;
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
        window.STUDIP.LernmoduleVueJS.module.module_id,
        window.STUDIP.LernmoduleVueJS.block_id
      );
      await sleep(500);
      this.context.commit('saveSuccess', saveResult.taskDefinition);
    } catch (error) {
      this.context.commit('saveFailure', { error });
    }
  }

  /**
   * Modify the currently edited task definition, creating a new undo/redo state
   * if appropriate.  Edits are batched together based on the parameter 'undoBatch'.
   * @param payload.taskDefinition The new state of the task being edited
   * @param payload.undoBatch An object.
   * If undoBatch is an empty object, a new undo/redo state will always be created.
   * Otherwise, a new undo/redo state will be created if undoBatch is different
   * from the undoBatch of the previously performed action.
   */
  @Mutation
  performEdit(payload: { taskDefinition: TaskDefinition; undoBatch: object }) {
    const currentUndoRedoState = this.undoRedoStack[this.undoRedoIndex];
    if (isEqual(currentUndoRedoState.taskDefinition, payload.taskDefinition)) {
      return; // Do not pollute the undo-redo history with no-op changes
    }
    const shouldMergeEdits =
      !isEqual(payload.undoBatch, {}) &&
      isEqual(payload.undoBatch, currentUndoRedoState.undoBatch);
    if (shouldMergeEdits) {
      // Amend the last undo state instead of creating a new one.
      currentUndoRedoState.taskDefinition = payload.taskDefinition;
      this.undoRedoStack = this.undoRedoStack.slice(0, this.undoRedoIndex + 1);
    } else {
      // Create a new undo state and append it to the stack.
      this.undoRedoStack = this.undoRedoStack
        .slice(0, this.undoRedoIndex + 1)
        .concat([
          {
            taskDefinition: payload.taskDefinition,
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
}
