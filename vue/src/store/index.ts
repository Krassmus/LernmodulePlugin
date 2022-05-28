import { createStore } from 'vuex';
import { getModule } from 'vuex-module-decorators';
import { TaskEditorModule } from '@/store/modules/taskEditor';

export const store = createStore({
  modules: {
    taskEditor: TaskEditorModule,
  },
});
export const taskEditorStore = getModule(TaskEditorModule, store);
