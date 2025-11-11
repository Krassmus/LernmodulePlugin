import { Action, Module, Mutation, VuexModule } from 'vuex-module-decorators';
import { taskEditorStore } from '@/store';
import { Context } from '@/models/CoursewareBlockIframeMessages';

@Module({ name: 'coursewareBlock' })
export class CoursewareBlockModule extends VuexModule {
  showEditorUI: boolean = false;
  studipContext: Context = {
    id: '',
    type: '',
  };

  // Toggle whether to show the editing UI in the courseware block.
  // This should be triggered via messages posted to the iframe.
  // See courseware-main.ts and LernmoduleCoursewareBlockBase.js.
  @Mutation
  setShowEditorUI(state: boolean) {
    this.showEditorUI = state;
  }

  @Mutation
  saveBlock() {
    console.log('coursewareBlockStore: saveBlock() mutation called');
    // Tell the Vue 2 component we are wrapped in to save the block.
    // See the method 'onWindowMessage' of LernmoduleCoursewareBlockBase.js
    // The taskDefinition must be serialized in order for it to be passed
    // between windows.
    const taskDefinition = JSON.parse(
      JSON.stringify(taskEditorStore.taskDefinition)
    );
    window.parent.postMessage({
      type: 'SaveCoursewareBlock',
      taskDefinition,
    });
  }

  @Mutation
  cancelEditing() {
    console.log('coursewareBlockStore: cancelEditing() mutation called');
    // Tell the Vue 2 component we are wrapped in to stop editing the block
    // without saving the user's changes.
    // See the method 'onWindowMessage' of LernmoduleCoursewareBlockBase.js
    window.parent.postMessage({
      type: 'CancelEditingCoursewareBlock',
    });
  }

  @Mutation
  setContext(context: Context) {
    this.studipContext = context;
  }
}
