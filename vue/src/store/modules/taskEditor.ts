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

function sleep(ms: number) {
  return new Promise((r) => setTimeout(r, ms));
}

@Module({ name: 'taskEditor' })
export class TaskEditorModule extends VuexModule {
  saveStatus: SaveStatus = { status: 'saved' };
  fatalErrors: string[] = [];
  nonFatalErrors: string[] = [];
  taskDefinition: TaskDefinition = newTask('FillInTheBlanks');
  serverTaskDefinition: TaskDefinition | [] = [];
  moduleName: string = '';

  get hasUnsavedChanges(): boolean {
    return !isEqual(this.taskDefinition, this.serverTaskDefinition);
  }

  @Mutation
  initialize() {
    if (!isArray(window.STUDIP.LernmoduleVueJS.module.customdata)) {
      // TODO: Warning!! Bad!! You should parse the contents, do not just type-cast!!
      this.serverTaskDefinition = window.STUDIP.LernmoduleVueJS.module
        .customdata as TaskDefinition;
      this.taskDefinition = this.serverTaskDefinition;
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

  @Mutation
  setTaskDefinition(taskDefinition: TaskDefinition) {
    this.taskDefinition = taskDefinition;
  }

  @Mutation
  setModuleName(name: string) {
    this.moduleName = name;
  }
}
