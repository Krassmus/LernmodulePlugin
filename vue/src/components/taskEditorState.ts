import { taskEditorStore } from '@/store';
import { InjectionKey } from 'vue';

export const taskEditorStateSymbol = Symbol(
  'Task editor injected dependencies'
) as InjectionKey<TaskEditorState>;

export interface TaskEditorState {
  performEdit: typeof taskEditorStore.performEdit;
}
