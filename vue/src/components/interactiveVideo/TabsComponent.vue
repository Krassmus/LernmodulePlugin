<!-- Adapted from https://zerotomastery.io/blog/tab-component-design-with-vue/ -->
<template>
  <div class="border-4 border-black rounded">
    <ul class="flex flex-nowrap justify-between">
      <li
        class="w-full font-black text-center py-4 cursor-pointer border-b-4 border-black"
        :class="{
          'bg-yellow-50': tab.hash !== activeTabHash,
          'bg-lime-200': tab.hash === activeTabHash,
        }"
        v-for="tab in tabs"
        :key="tab.title"
        @click="activeTabHash = tab.hash"
      >
        {{ tab.title }}
      </li>
    </ul>
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
