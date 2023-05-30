import { createStore } from 'vuex';
import { getModule } from 'vuex-module-decorators';
import { TaskEditorModule } from '@/store/modules/taskEditor';
import { CoursewareBlockModule } from '@/store/modules/coursewareBlock';

export const store = createStore({
  modules: {
    taskEditor: TaskEditorModule,
    coursewareBlock: CoursewareBlockModule,
  },
});
export const taskEditorStore = getModule(TaskEditorModule, store);
export const coursewareBlockStore = getModule(CoursewareBlockModule, store);
