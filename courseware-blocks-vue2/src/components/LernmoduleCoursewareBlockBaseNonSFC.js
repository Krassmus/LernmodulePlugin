import iframeResize from 'iframe-resizer/js/iframeResizer';

export default {
  name: 'LernmoduleCoursewareBlockBase',
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
      if (message.data && Object.hasOwn(message.data, 'type')) {
        switch (message.data.type) {
          case 'SaveCoursewareBlock':
            console.log(
              'got message posted to window: ',
              message,
              'saving courseware block. taskDefinition: ',
              message.data.taskDefinition
            );
            this.storeBlock(message.data.taskDefinition);
            break;
          case 'CancelEditingCoursewareBlock':
            console.log(
              'got message posted to window: ',
              message,
              'canceling editing.'
            );
            // close the edit menu
            this.$refs.defaultBlock.closeEdit();
            // Reload the iframe (this causes the state of the block to reset to how
            // it is on the server, discarding user's unsaved changes).
            this.$refs.lernmoduleIframe.contentWindow.location.reload();
            break;
        }
      }
    },
    // Pass the child CoursewareDefaultBlock's editing state on to our iFrame.
    // This lets our Vue 3 component know whether to hide/show its editing UI
    onShowEditChange(state) {
      this.$refs.lernmoduleIframe.contentWindow.postMessage({
        type: 'ShowEditChange',
        state,
      });
    },
    onIframeLoad() {
      console.log('on iframe load');
      // Configure iFrameResize to resize the iframe to the height of the
      // #app element, which is marked with data-iframe-height, inside the iframe
      iframeResize(
        { heightCalculationMethod: 'taggedElement' },
        this.$refs.lernmoduleIframe
      );

      // Send message to initialize the Vue 3 courseware block's store
      debugger;
      const message = {
        type: 'InitializeCoursewareBlock',
        ...window.STUDIP.CoursewareLernmoduleBlocksPlugin,
        canEdit: this.canEdit,
        isTeacher: this.isTeacher,
        block: JSON.parse(JSON.stringify(this.block)),
        context: JSON.parse(JSON.stringify(this.$store.getters.context)),
      };
      this.$refs.lernmoduleIframe.contentWindow.postMessage(message);

      // Call onShowEditChange one time after load to initialize the 'editing'
      // state in our Vue 3 component
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
  template: `
    <component
      class="cw-lernmodule-block cw-block"
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
        <template v-if="false">
          <p>Lernmodule block content. Block:</p>
          <pre style="font-size: 8px">{{ block }}</pre>
        </template>
        <iframe
          ref="lernmoduleIframe"
          class="lernmodule-iframe"
          :src="iframeUrl"
          @load="onIframeLoad"
        />
      </template>
    </component>
  `,
};
