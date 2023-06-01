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
  methods: {
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
    storeBlock() {
      // Courseware is written such that, until a block is saved for the first
      // time by an editor, its payload will not be initialized in the DB.
      // Until the payload has been stored in the DB, a new payload will be
      // generated using MindmapBlock::initialPayload() upon each page load.
      // This means that if e.g. a timestamp or a random ID is generated
      // in initialPayload(), each user who views the block will see a different
      // timestamp and a different random ID, as long as the block is in a
      // created-but-never-saved state.
      // The workaround here is to explicitly handle the created-but-not-saved state
      // using the flag "initialized", which is only set upon saving the payload
      // by hand.  Only after "initialized: true" is set will the mindmap editor
      // be activated.

      // const attributes = {
      //   ...this.block.attributes,
      //   payload: {
      //     ...this.block.attributes.payload,
      //     initialized: true,
      //     task_json: this.block.attributes.payload.initialized ? this.block.attributes.payload.task_json : {}
      //   }
      // };
      const attributes = {
        ...this.block.attributes,
        /*
         TODO This payload should not be hard-coded. It should instead be taken from
            a message posted to our window from the Vue 3 code in our iframe.
        */
        payload: {
          initialized: false,
          task_type: 'FillInTheBlanks',
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
