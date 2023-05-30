import { Module, Mutation, VuexModule } from 'vuex-module-decorators';

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
}
