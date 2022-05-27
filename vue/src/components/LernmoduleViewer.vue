<template>
  <h1>Viewer (rendered with vue.js)</h1>
  <div>
    Variables passed from server:
    <pre>{{ LernmoduleVueJS }}</pre>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'LernmoduleViewer',
  computed: {
    LernmoduleVueJS: () => window.STUDIP.LernmoduleVueJS,
  },
});
</script>

<style scoped></style>
