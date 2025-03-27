import LernmoduleCoursewareBlockBase from '@/components/LernmoduleCoursewareBlockBase.vue';

// When adding a Courseware block for a new task type, you must add its name
// to this array so that a corresponding vue 2 component will be registered in
// the Courseware.
const taskTypes = [
  'FillInTheBlanks',
  'Question',
  'DragTheWords',
  'MarkTheWords',
  'Memory',
  'Pairing',
  'Sequencing',
  'LmbInteractiveVideo',
  'FindTheHotspots',
];

const debug = window.STUDIP.LernmoduleCoursewareBlocksPlugin.debug;
if (debug) {
  console.log('Hello :) Registering Lernmodule Courseware blocks...');
}
window.STUDIP.eventBus.on('courseware:init-plugin-manager', (pluginManager) => {
  for (const taskType of taskTypes) {
    const blockComponent = coursewareBlockComponentForTaskType(taskType);
    pluginManager.addBlock(blockComponent.name, blockComponent);
    if (debug) {
      console.info('Registered CW block component: ', blockComponent.name);
    }
  }
});

/**
 * All of the Vue 2 Lernmodule Courseware block components inherit from the same
 * base component, which is merely a proxy to our Vue 3 code.
 */
function coursewareBlockComponentForTaskType(taskType) {
  return {
    name: `Courseware${taskType}Block`,
    extends: LernmoduleCoursewareBlockBase,
  };
}
