import CoursewareFillInTheBlanksBlock from '@/components/CoursewareFillInTheBlanksBlock.vue';

console.info('hello :)');
window.STUDIP.eventBus.on('courseware:init-plugin-manager', (pluginManager) => {
  pluginManager.addBlock(
    'courseware-fill-in-the-blanks-block',
    CoursewareFillInTheBlanksBlock
  );
  console.info('Registered FITB block');
});
