import { createApp } from 'vue';
import LernmoduleEditor from '@/components/LernmoduleEditor.vue';
import { taskEditorStore, store } from '@/store';
import { modelUndoable } from '@/directives/vModelUndoable';
import { gettextPlugin } from '@/language/gettext';
import './assets/global.css';

taskEditorStore.initializeCourseware();
const app = createApp(LernmoduleEditor);
app.directive('model-undoable', modelUndoable);
app.use(store);
app.use(gettextPlugin);
app.mount('#app');
