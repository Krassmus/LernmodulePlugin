import CoursewareFillInTheBlanksBlock from '@/components/CoursewareFillInTheBlanksBlock.vue';

console.info('hello :)');
if (!window.STUDIP) {
  console.warn(
    'window.STUDIP is undefined. ' +
      'The courseware block will not be registered'
  );
} else {
  window.STUDIP.eventBus.on(
    'courseware:init-plugin-manager',
    (pluginManager) => {
      pluginManager.addBlock(
        'courseware-fill-in-the-blanks-block',
        CoursewareFillInTheBlanksBlock
      );
      console.info('Registered FITB block');
    }
  );
}
