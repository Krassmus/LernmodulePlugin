<template>
  <div class="cw-mindmap-block">
    <component
      :is="coursewarePluginComponents.CoursewareDefaultBlock"
      ref="defaultBlock"
      :block="block"
      :canEdit="canEdit"
      :isTeacher="isTeacher"
      :preview="true"
      :defaultGrade="false"
      @storeEdit="storeBlock"
    >
      <template #content>
        <p>Fill In The Blanks block content. Payload:</p>
        <pre>{{ block.attributes.payload }}</pre>
      </template>
      <template v-if="canEdit" #edit>
        Fill In The Blanks editor content
      </template>
    </component>
  </div>
</template>

<style scoped></style>

<script lang="js">
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
  async mounted() {
    if (!this.block.attributes.payload.initialized) {
      this.storeBlock();
    }
  },
  computed: {
    editorUrl() {
      return window.STUDIP.LernmoduleCoursewareBlocksPlugin.editorUrl;
    },
    isBlockInitialized() {
      return this.block.attributes.payload.initialized;
    },
  },
  methods: {
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
        payload: {
          initialized: true,
          task_json: { a: 1 }
        }
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
