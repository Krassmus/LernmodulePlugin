<!-- Adapted by Ann Yanich from the Vue 2 component 'CoursewareTab' from Stud.IP 5.4 -->
<template>
  <div
    role="tabpanel"
    class="cw-tab"
    :id="id"
    :class="{ 'cw-tab-active': isActive }"
    :aria-labelledby="selectorId"
  >
    <slot></slot>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
export default defineComponent({
  name: 'courseware-tab',
  props: {
    name: { type: String, required: true },
    alias: { type: String, default: '' },
    selected: { type: Boolean, default: false },
    index: { type: Number, required: true },
    icon: { type: String, default: '' },
  },
  data() {
    return {
      isActive: false,
    };
  },
  computed: {
    selectorId() {
      return '#' + this._uid + '-' + this.name.toLowerCase().replace(/ /g, '-');
    },
    id() {
      return (
        this._uid +
        '-' +
        this.name.toLowerCase().replace(/ /g, '-') +
        '-tabpanel'
      );
    },
  },
  mounted() {
    this.isActive = this.selected;
  },
  updated() {
    if (this.isActive) {
      window.STUDIP.eventBus.emit('courseware:update-tab', { uid: this._uid });
    }
  },
  watch: {
    selected(newValue) {
      this.isActive = newValue;
    },
  },
});
</script>
