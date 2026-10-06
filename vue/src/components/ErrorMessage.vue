<script lang="ts">
import { defineComponent } from 'vue';
import { $gettext } from '../language/gettext';

export default defineComponent({
  name: 'ErrorMessage',
  methods: { $gettext },
  props: {
    error: {
      required: false,
    },
  },
});
</script>

<template>
  <div class="error-message">
    <p>{{ $gettext('Es ist ein Fehler aufgetreten!') }}</p>
    <p>{{ error }}</p>
  </div>
</template>

<style scoped>
.error-message {
  max-height: 200px;
  overflow-y: scroll;
  white-space: pre-wrap;
  font-family: monospace;
}
</style>
