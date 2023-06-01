import { Action, Module, Mutation, VuexModule } from 'vuex-module-decorators';
import { TaskDefinition } from '@/models/TaskDefinition';

@Module({ name: 'coursewareBlock' })
export class CoursewareBlockModule extends VuexModule {
  showEditorUI: boolean = false;

  // Toggle whether to show the editing UI in the courseware block.
  // This should be triggered via messages posted to the iframe.
  // See courseware-main.ts and CoursewareFillInTheBlanksBlock.vue.
  @Mutation
  setShowEditorUI(state: boolean) {
    this.showEditorUI = state;
  }

  @Action
  saveBlock(taskDefinition: TaskDefinition) {
    console.log('coursewareBlockStore: saveBlock() action called');
    // Tell the Vue 2 component we are wrapped in to save the block.
    // See the method 'onWindowMessage' of CoursewareFillInTheBlanksBlock.vue
    window.parent.postMessage({
      type: 'SaveCoursewareBlock',
      taskDefinition,
    });
  }
}
