<template>
  <textarea
    :value="modelValue"
    ref="studip_wysiwyg"
    class="studip-wysiwyg"
    @input="updateValue($event.target.value)"
  />
</template>

<script lang="ts">
// need v-model to provide and get content -> <studip-wysiwyg v-model="content" />
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'studip-wysiwyg',
  props: {
    modelValue: String,
  },
  data() {
    return {
      fallbackActive: false,
    };
  },
  mounted() {
    let ckeInit = this.initCKE();
    if (!ckeInit) {
      this.fallbackActive = true;
    }
  },
  methods: {
    initCKE() {
      if (!window.STUDIP.wysiwyg_enabled) {
        return false;
      }
      const textAreaElement = this.$refs.studip_wysiwyg as HTMLTextAreaElement;
      // Process the jQuery event 'wysiwyg.load' which is triggered asynchronously
      // after the wysiwyg editor is mounted using the 'replace()' method.
      textAreaElement.onload = (event: any) => {
        console.info('load');
        let wysiwyg_editor = window.STUDIP.wysiwyg.getEditor(textAreaElement)!;
        wysiwyg_editor.model.document.on('change:data', () => {
          this.$emit('update:modelValue', wysiwyg_editor.getData());
        });
      };
      // Asynchronous method which does not return a promise >:|
      window.STUDIP.wysiwyg.replace(textAreaElement);
      return true;
    },
    updateValue(value: unknown) {
      if (this.fallbackActive) {
        this.$emit('update:modelValue', value);
      }
    },
  },
});
</script>

<style></style>
