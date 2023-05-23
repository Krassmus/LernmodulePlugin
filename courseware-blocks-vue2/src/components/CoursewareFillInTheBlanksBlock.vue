<script lang="ts">
import type { Component, PropType } from 'vue';
import Vue from 'vue';
import { mapGetters } from 'vuex';

export default Vue.extend({
  name: 'CoursewareFillInTheBlanksBlock',
  inject: ['coursewarePluginComponents'],
  props: {
    block: {
      type: Object as PropType<LernmoduleBlock>,
      required: true,
    },
    canEdit: {
      type: Object as PropType<Boolean>,
      required: true,
    },
    isTeacher: {
      type: Object as PropType<Boolean>,
      required: true,
    },
  },
  async mounted() {
    if (!this.block.attributes.payload.initialized) {
      this.storeBlock();
    }
  },
  computed: {
    // Typed alias for this.$store
    store() {
      return (this as any).$store as CoursewareStore;
    },
    editorUrl() {
      return window.STUDIP.LernmoduleCoursewareBlocksPlugin.editorUrl;
    },
    isBlockInitialized() {
      return this.block.attributes.payload.initialized;
    },
  },
  methods: {
    storeBlock(): void {
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

      // vite-plugin-vue2 weirdness: 'this' is not correctly typed in methods
      const block = (this as any).block as LernmoduleBlock;
      const attributes = {
        ...block.attributes,
        payload: {
          ...block.attributes.payload,
          initialized: true,
        },
      };

      this.store
        .dispatch('updateBlockInContainer', {
          attributes,
          blockId: block.id,
          containerId: block.relationships.container.data.id,
        })
        .then(() => {
          // close the edit menu
          (this as any).$refs.defaultBlock.displayFeature(false);
        });
    },
  },
});
</script>

<template>
  <div v-if="!coursewarePluginComponents">
    The courseware block cannot be rendered, because the injected
    coursewarePluginComponents are not present. This is to be expected if the
    component is being rendered outside of Stud.IP, e.g. when you are testing it
    under localhost:5173 using 'npm run dev'.
  </div>
  <div v-else class="cw-mindmap-block">
    <!-- In PHPStorm, this line is highlighted as a type error. It appears to be
    an IDE bug.  See https://youtrack.jetbrains.com/issue/WEB-60534 -->
    <component
      :is="coursewarePluginComponents.CoursewareDefaultBlock"
      ref="defaultBlock"
      :block="block"
      :canEdit="canEdit"
      :isTeacher="isTeacher"
      :preview="true"
      :defaultGrade="false"
      @storeEdit="storeBlock"
    />
  </div>
</template>

<style scoped></style>
