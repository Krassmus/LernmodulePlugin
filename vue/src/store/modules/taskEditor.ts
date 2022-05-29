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
  editType: object;
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
      editType: {},
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
    if (!isArray(window.STUDIP.LernmoduleVueJS.module.customdata)) {
      // TODO: Warning!! Bad!! You should parse the contents, do not just type-cast!!
      this.serverTaskDefinition = window.STUDIP.LernmoduleVueJS.module
        .customdata as TaskDefinition;
      this.undoRedoStack = [
        { taskDefinition: this.serverTaskDefinition, editType: {} },
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
    saveTask(
      this.taskDefinition,
      this.moduleName,
      window.STUDIP.LernmoduleVueJS.module.module_id,
      window.STUDIP.LernmoduleVueJS.block_id
    )
      .then(async (result) => {
        await sleep(500);
        this.context.commit('saveSuccess', result.taskDefinition);
      })
      .catch((error) => {
        this.context.commit('saveFailure', { error });
      });
  }

  /**
   * Modify the currently edited task definition and possibly create a new
   * undo/redo state.  Changes are batched by 'editType'.
   * If editType === {}, a new undo/redo state will always be created.
   * Otherwise, a new undo/redo state will only be created
   * if editType !== lastEditType.
   * @param taskDefinition The new task definition
   * @param editType
   */
  @Mutation
  setTaskDefinition(taskDefinition: TaskDefinition, editType: object) {
    const currentUndoRedoState = this.undoRedoStack[this.undoRedoIndex];
    if (isEqual(currentUndoRedoState.taskDefinition, taskDefinition)) {
      return; // Do not pollute the undo-redo history with no-op changes
    }
    const shouldMergeEdits =
      !isEqual(editType, {}) &&
      isEqual(editType, currentUndoRedoState.editType);
    if (shouldMergeEdits) {
      // Amend the last undo state instead of creating a new one.
      currentUndoRedoState.taskDefinition = taskDefinition;
      this.undoRedoStack = this.undoRedoStack.slice(0, this.undoRedoIndex + 1);
    } else {
      // Create a new undo state and append it to the stack.
      this.undoRedoStack = this.undoRedoStack
        .slice(0, this.undoRedoIndex + 1)
        .concat([
          {
            taskDefinition,
            editType: editType,
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
