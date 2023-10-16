<!-- Adapted from https://zerotomastery.io/blog/tab-component-design-with-vue/ -->
<template>
  <div class="cw-tabs">
    <div role="tablist" class="cw-tabs-nav">
      <button
        v-for="tab in tabs"
        :key="tab.title"
        @click="activeTabHash = tab.hash"
        :class="{
          'is-active': tab.hash === activeTabHash,
          [`cw-tabs-nav-icon-text-${tab.icon}`]:
            tab.icon !== '' && tab.name !== '',
          [`cw-tabs-nav-icon-solo-${tab.icon}`]:
            tab.icon !== '' && tab.name === '',
        }"
      >
        {{ tab.title }}
      </button>
    </div>
    <slot />
  </div>
</template>

<script>
import { computed, defineComponent } from 'vue';

export default defineComponent({
  data() {
    return {
      activeTabHash: '',
      tabs: [],
    };
  },
  provide() {
    return {
      addTab: (tab) => {
        const count = this.tabs.push(tab);

        if (count === 1) {
          this.activeTabHash = tab.hash;
        }
      },
      activeTabHash: computed(() => this.activeTabHash),
    };
  },
});
</script>
