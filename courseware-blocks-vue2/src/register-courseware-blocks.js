import LernmoduleCoursewareBlockBaseNonSFC from '@/components/LernmoduleCoursewareBlockBaseNonSFC';

// When adding a Courseware block for a new task type, you must add its name
// to this array so that a corresponding vue 2 component will be registered in
// the Courseware.
const taskTypes = [
  'DragTheWords',
  'FillInTheBlanks',
  'FindTheHotspots',
  'FindTheWords',
  'LmbInteractiveVideo',
  'MarkTheWords',
  'Memory',
  'Pairing',
  'Question',
  'Sequencing',
];

const debug = window.STUDIP.LernmoduleCoursewareBlocksPlugin.debug;
if (debug) {
  console.log(
    'Hello :) Adding event handler to add Lernmodule blocks in Courseware...'
  );
}
window.STUDIP.eventBus.on(
  'courseware:init-plugin-manager',
  /**
   * The development of the lernmodule courseware blocks began on Stud.IP 5.x,
   * which used Vue 2. As the Vue.js Lernmodule were developed using Vue 3,
   * they were embedded over iframes into Courseware. This design is maintained
   * to this day (2025.11.10).
   * All of the Lernmodule Courseware Block Vue components inherit from the same
   * base component, which merely opens an iframe in which the actual component
   * corresponding to the task type is displayed.
   */
  async (pluginManager) => {
    const isVue3 = await checkIsVue3();
    for (const taskType of taskTypes) {
      const name = componentNameForTaskType(taskType);
      const componentOptions = {
        name,
        extends: LernmoduleCoursewareBlockBaseNonSFC,
      };
      // In Vue 3, components registered over plugins at runtime must be defined
      // using 'defineAsyncComponent', whereas in Vue 2, you may simply provide
      // the bare 'options' object.
      const blockComponent = isVue3
        ? window.Vue.defineAsyncComponent(async () => componentOptions)
        : componentOptions;

      pluginManager.addBlock(name, blockComponent);
      if (debug) {
        console.info('Registered CW block component: ', name);
      }
    }
  }
);

function componentNameForTaskType(taskType) {
  return `Courseware${taskType}Block`;
}

async function checkIsVue3() {
  // Hacky method to figure out the version of Vue in Stud.IP.
  const isVue3 = await window.STUDIP.Vue.load().then(
    (v) => v.createApp().version
  );
  if (debug) {
    console.log(isVue3 ? 'Detected vue 3' : 'Detected vue 2');
  }
  return isVue3;
}
