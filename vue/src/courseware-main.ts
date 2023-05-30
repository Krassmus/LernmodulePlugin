import { createApp } from 'vue';
import LernmoduleEditor from '@/components/LernmoduleEditor.vue';
import { taskEditorStore, store } from '@/store';
import { modelUndoable } from '@/directives/vModelUndoable';
import { gettextPlugin } from '@/language/gettext';
import './assets/global.css';
import { isObject } from 'lodash';
import { TaskDefinition } from '@/models/TaskDefinition';

// Messages which the mindmap editor will respond to if they are posted to
// the iframe which it is embedded in.
type WindowMessage = InitializeMessage | WebpackMessage;
interface WebpackMessage {
  type: 'webpackProgress' | 'webpackOk' | 'webpackClose' | 'webpackInvalid';
}
interface InitializeMessage {
  type: 'InitializeCoursewareBlock';
  block: {
    attributes: {
      payload: {
        initialized: boolean;
        task_json: object;
      };
    };
  };
  canEdit: boolean;
  isTeacher: boolean;
  username: string;
}

// Wait to load until a message is posted to Window.
if (!window.frameElement) {
  throw new Error(
    'This script appears not to be running in an iframe.  It is only meant to ' +
      'be run in an iframe inside of Stud.IP'
  );
}

window.addEventListener('message', (event) => {
  console.warn('message posted to Window: ', event, 'data: ', event.data);
  const typedData = event.data as WindowMessage;
  switch (typedData.type) {
    case 'webpackProgress':
    case 'webpackOk':
    case 'webpackClose':
    case 'webpackInvalid':
      break; // ignore
    case 'InitializeCoursewareBlock':
      // TODO parse the event data according to a schema instead of using these
      //  ad-hoc checks
      if (!event.data.block.attributes.payload.task_json) {
        throw new Error('payload.mindmap_id is undefined');
      }
      if (!isObject(event.data.block.attributes.payload.task_json)) {
        throw new Error('payload.mindmap_id is not a string');
      }
      initializeApp(typedData);
      break;
    default:
      console.error('Message not recognized: ', event.data);
      return;
  }
});

function initializeApp(typedData: InitializeMessage) {
  taskEditorStore.initializeCourseware(
    // TODO: Warning!! Bad!! You should parse the contents, do not just type-cast!!
    typedData.block.attributes.payload.task_json as TaskDefinition
  );
  // TODO probably should render a courseware-specific component as the root component,
  //  because it needs to be able to switch between editor and viewer modes and
  //  display the 'edit' elements in a <legend> the way courseware blocks usually do
  // TODO Also, the 'save' button, which is displayed in the sidebar using Teleport,
  //  is not compatible with Courseware.  It also needs to be refactored/redone
  const app = createApp(LernmoduleEditor);
  app.directive('model-undoable', modelUndoable);
  app.use(store);
  app.use(gettextPlugin);
  app.mount('#app');
}
