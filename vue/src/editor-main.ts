import { createApp } from 'vue';
import LernmoduleEditor from '@/components/LernmoduleEditor.vue';
import { taskEditorStore, store } from '@/store';
import { modelUndoable } from '@/directives/vModelUndoable';

taskEditorStore.initialize();
const app = createApp(LernmoduleEditor);
app.directive('model-undoable', modelUndoable);
app.use(store).mount('#app');
