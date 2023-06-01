import { createApp } from 'vue';
import { taskEditorStore, store, coursewareBlockStore } from '@/store';
import { modelUndoable } from '@/directives/vModelUndoable';
import { gettextPlugin } from '@/language/gettext';
import './assets/global.css';
import { isString } from 'lodash';
import { taskDefinitionSchema } from '@/models/TaskDefinition';
import CoursewareBlock from '@/components/CoursewareBlock.vue';
import { z } from 'zod';

// Messages sent by webpack during development.  We can ignore them
const webpackMessageSchema = z.object({
  type: z.union([
    z.literal('webpackProgress'),
    z.literal('webpackOk'),
    z.literal('webpackClose'),
    z.literal('webpackInvalid'),
  ]),
});

// Messages sent by the iFrameSizer library.  We can ignore them
const iFrameSizerMessageSchema = z.string().startsWith('[iFrameSizer]');

// Indicates that the edit UI should be shown or hidden
const showEditChangeMessageSchema = z.object({
  type: z.literal('ShowEditChange'),
  state: z.boolean(),
});

// Contains data which should be used to initialize the store for the Courseware block
const initializeCoursewareBlockMessageSchema = z.object({
  type: z.literal('InitializeCoursewareBlock'),
  block: z.object({
    attributes: z.object({
      payload: z.object({
        initialized: z.boolean(),
        task_json: z.unknown(),
      }),
    }),
  }),
  canEdit: z.boolean(),
  isTeacher: z.boolean(),
});
type InitializeMessage = z.infer<typeof initializeCoursewareBlockMessageSchema>;

// Messages which may be posted to the iframe in which the Vue 3 CoursewareBlock
// component is embedded
const windowMessageSchema = z.union([
  initializeCoursewareBlockMessageSchema,
  showEditChangeMessageSchema,
  webpackMessageSchema,
  iFrameSizerMessageSchema,
]);

// Wait to load until a message is posted to Window.
if (!window.frameElement) {
  throw new Error(
    'This script appears not to be running in an iframe.  It is only meant to ' +
      'be run in an iframe inside of Stud.IP'
  );
}

window.addEventListener('message', (event) => {
  const dataParseResult = windowMessageSchema.safeParse(event.data);
  if (!dataParseResult.success) {
    console.info('Message not recognized: ', event.data, dataParseResult.error);
    return;
  }
  if (isString(dataParseResult.data)) {
    // Message sent by the iFrameSizer library.  We can ignore it
    return;
  }
  switch (dataParseResult.data.type) {
    case 'webpackProgress':
    case 'webpackOk':
    case 'webpackClose':
    case 'webpackInvalid':
      break; // ignore
    case 'InitializeCoursewareBlock':
      console.warn('message posted to Window: ', event, 'data: ', event.data);
      initializeApp(dataParseResult.data);
      break;
    case 'ShowEditChange':
      console.warn('message posted to Window: ', event, 'data: ', event.data);
      coursewareBlockStore.setShowEditorUI(dataParseResult.data.state);
      break;
    default:
  }
});

function initializeApp(typedData: InitializeMessage) {
  const taskParseResult = taskDefinitionSchema.safeParse(
    typedData.block.attributes.payload.task_json
  );
  if (taskParseResult.success) {
    taskEditorStore.initializeCourseware(taskParseResult.data);
  } else {
    console.warn('Could not parse task_json.  Result: ', taskParseResult);
    taskEditorStore.initializeCourseware();
  }
  // TODO probably should render a courseware-specific component as the root component,
  //  because it needs to be able to switch between editor and viewer modes and
  //  display the 'edit' elements in a <legend> the way courseware blocks usually do
  // TODO Also, the 'save' button, which is displayed in the sidebar using Teleport,
  //  is not compatible with Courseware.  It also needs to be refactored/redone
  const app = createApp(CoursewareBlock);
  app.directive('model-undoable', modelUndoable);
  app.use(store);
  app.use(gettextPlugin);
  app.mount('#app');
}
