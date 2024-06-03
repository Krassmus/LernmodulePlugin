import { createApp } from 'vue';
import LernmoduleEditor from '@/components/LernmoduleEditor.vue';
import { taskEditorStore, store } from '@/store';
import { modelUndoable } from '@/directives/vModelUndoable';
import { gettextPlugin } from '@/language/gettext';
import './assets/global.css';

taskEditorStore.initializeNonCourseware();
const app = createApp(LernmoduleEditor);
app.directive('model-undoable', modelUndoable);
app.use(store);
app.use(gettextPlugin);

// This config is needed until Vue 3.3 in order to allow us to pass around
// reactive refs using provide/inject.  In particular, this functionality
// is used in the TabsComponent/TabComponent.vue used in the Interactive Video
// task editor.
// https://vuejs.org/guide/components/provide-inject.html#working-with-reactivity
app.config.unwrapInjectedRef = true;
app.mount('#app');
