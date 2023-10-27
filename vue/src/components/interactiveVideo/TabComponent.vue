<!-- Adapted from https://zerotomastery.io/blog/tab-component-design-with-vue/ -->
<template>
  <div
    role="tabpanel"
    class="cw-tab cw-tabs-content"
    :class="{ 'cw-tab-active': isActive }"
    v-show="isActive"
    :aria-labelledby="buttonSelector"
  >
    <slot />
  </div>
</template>

<script>
import { defineComponent } from 'vue';
export default defineComponent({
  inject: ['activeTabHash', 'addTab'],
  props: {
    title: {
      type: String,
      required: true,
    },
    icon: {
      type: String,
      required: false,
      default: '',
    },
  },
  data() {
    return { hash: '', isActive: false };
  },
  created() {
    this.hash = '#' + this.title.toLowerCase().replace(/ /g, '-');

    this.addTab({
      title: this.title,
      hash: this.hash,
      icon: this.icon,
    });
  },
  watch: {
    activeTabHash() {
      this.isActive = this.activeTabHash === this.hash;
    },
  },
  computed: {
    buttonSelector() {
      return '#' + this.hash;
    },
  },
});
</script>
