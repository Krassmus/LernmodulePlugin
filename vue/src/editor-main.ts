import { createApp } from 'vue';
import LernmoduleEditor from '@/components/LernmoduleEditor.vue';
import { taskEditorStore, store } from '@/store';
import { modelUndoable } from '@/directives/vModelUndoable';
import { gettextPlugin } from '@/language/gettext';
import './assets/global.css';
import ErrorMessage from '@/components/ErrorMessage.vue';
import { disableDrag } from '@/directives/vDisableDrag';

init();

function init() {
  try {
    taskEditorStore.initializeNonCourseware();
  } catch (e: unknown) {
    const errorMessage = (e as Error).message;
    const app = createApp(ErrorMessage, {
      error: errorMessage,
    });
    app.mount('#stud5p-app');
    return;
  }

  const app = createApp(LernmoduleEditor);
  app.directive('model-undoable', modelUndoable);
  app.directive('disable-drag', disableDrag);
  app.use(store);
  app.use(gettextPlugin);

  // This config is needed until Vue 3.3 in order to allow us to pass around
  // reactive refs using provide/inject.  In particular, this functionality
  // is used in the TabsComponent/TabComponent.vue used in the Interactive Video
  // task editor.
  // https://vuejs.org/guide/components/provide-inject.html#working-with-reactivity
  app.config.unwrapInjectedRef = true;
  app.mount('#stud5p-app');
}
