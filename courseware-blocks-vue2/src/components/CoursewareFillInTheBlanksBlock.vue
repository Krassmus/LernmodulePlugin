<template>
  <div class="cw-fill-in-the-blanks-block">
    <component
      :is="coursewarePluginComponents.CoursewareDefaultBlock"
      ref="defaultBlock"
      :block="block"
      :canEdit="canEdit"
      :isTeacher="isTeacher"
      :preview="true"
      :defaultGrade="false"
      @storeEdit="storeBlock"
      @showEdit="onShowEditChange"
    >
      <template #content>
        <p>Fill In The Blanks block content. Payload:</p>
        <pre>{{ block.attributes.payload }}</pre>
        <iframe
          ref="lernmoduleIframe"
          class="lernmodule-iframe"
          :src="iframeUrl"
          @load="onIframeLoad"
        />
      </template>
      <template v-if="canEdit" #edit>
        <!--        Fill In The Blanks editor content-->
      </template>
    </component>
  </div>
</template>

<style scoped>
.lernmodule-iframe {
  width: 1px;
  min-width: 100%;
  border: none;
}
</style>

<script>
import iframeResize from 'iframe-resizer/js/iframeResizer';

export default {
  name: 'CoursewareFillInTheBlanksBlock',
  inject: ['coursewarePluginComponents'],
  props: {
    block: {
      type: Object,
      required: true,
    },
    canEdit: {
      type: Boolean,
      required: true,
    },
    isTeacher: {
      type: Boolean,
      required: true,
    },
  },
  computed: {
    iframeUrl() {
      return window.STUDIP.LernmoduleCoursewareBlocksPlugin.editorUrl;
    },
    isBlockInitialized() {
      return this.block.attributes.payload.initialized;
    },
  },
  created() {
    window.addEventListener('message', this.onWindowMessage);
  },
  beforeDestroy() {
    window.removeEventListener('message', this.onWindowMessage);
  },
  methods: {
    // Handle messages posted to our window from our iframe
    onWindowMessage(message) {
      if (message.source !== this.$refs.lernmoduleIframe.contentWindow) {
        return; // Ignore the message -- it's not from our iframe
      }
      if (message.data && message.data.hasOwnProperty('type')) {
        switch (message.data.type) {
          case 'SaveCoursewareBlock':
            console.log(
              'got message posted to window: ',
              message,
              'saving courseware block. taskDefinition: ',
              message.data.taskDefinition
            );
            this.storeBlock(message.data.taskDefinition);
        }
      }
    },
    // Mirror the child CoursewareDefaultBlock's state. We need this in order
    // to tell our iframe whether to hide/show the editing UI
    onShowEditChange(state) {
      this.$refs.lernmoduleIframe.contentWindow.postMessage({
        type: 'ShowEditChange',
        state,
      });
    },
    onIframeLoad(event) {
      console.log('on iframe load');
      // Configure iFrameResize to resize the iframe to the height of the
      // #app element, which is marked with data-iframe-height, inside the iframe
      iframeResize(
        { heightCalculationMethod: 'taggedElement' },
        this.$refs.lernmoduleIframe
      );

      // Send message to initialize the Vue 3 courseware block's store
      this.$refs.lernmoduleIframe.contentWindow.postMessage({
        type: 'InitializeCoursewareBlock',
        ...window.STUDIP.CoursewareLernmoduleBlocksPlugin,
        canEdit: this.canEdit,
        isTeacher: this.isTeacher,
        block: JSON.parse(JSON.stringify(this.block)),
      });

      // Tell the Vue 3 code to show the editor, if we are in editing mode.
      console.log('iframe loaded, calling onShowEditChange...');
      this.onShowEditChange(this.$refs.defaultBlock.showEdit);
    },
    storeBlock(taskDefinition) {
      const attributes = {
        ...this.block.attributes,
        payload: {
          initialized: true,
          task_json: taskDefinition,
        },
      };

      this.$store
        .dispatch('updateBlockInContainer', {
          attributes,
          blockId: this.block.id,
          containerId: this.block.relationships.container.data.id,
        })
        .then(() => {
          // close the edit menu
          this.$refs.defaultBlock.displayFeature(false);
        });
    },
  },
};
</script>
