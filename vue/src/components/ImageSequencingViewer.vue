<template>
  <div class="main-flex">Image Sequencing Viewer</div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ImageSequencingTask } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import { taskEditorStore } from '@/store';

export default defineComponent({
  name: 'ImageSequencingViewer',
  components: {},
  props: {
    task: {
      type: Object as PropType<ImageSequencingTask>,
      required: true,
    },
  },
  data() {
    return {};
  },
  beforeMount(): void {},
  methods: {
    $gettext,
    urlForIcon(iconName: string) {
      return (
        window.STUDIP.ASSETS_URL + 'images/icons/blue/' + iconName + '.svg'
      );
    },
  },
  computed: {
    taskDefinition: () => taskEditorStore.taskDefinition as ImageSequencingTask,
  },
});
</script>

<style scoped>
.main-flex {
  display: flex;
  justify-content: space-between;
  width: 100%;
  gap: 1em;
}
</style>
