<!-- Adapted from https://zerotomastery.io/blog/tab-component-design-with-vue/ -->
<template>
  <div class="p-8" v-show="isActive">
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
  },
  data() {
    return { hash: '', isActive: false };
  },
  created() {
    this.hash = '#' + this.title.toLowerCase().replace(/ /g, '-');

    this.addTab({
      title: this.title,
      hash: this.hash,
    });
  },
  watch: {
    activeTabHash() {
      console.log('test');
      this.isActive = this.activeTabHash === this.hash;
    },
  },
});
</script>
