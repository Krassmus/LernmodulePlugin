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
        let ckeditor = window.STUDIP.wysiwyg.getEditor(textAreaElement)!;
        ckeditor.model.document.on('change:data', () => {
          this.$emit('update:modelValue', ckeditor.getData());
        });
        // Override 'enter' to insert <br /> instead of <p></p>.
        // This produces HTML which is more easily parsed in our various learning tasks.
        // E.g. "Fill In The Blanks" turns words surrounded by *asterisks* into gaps.
        // Source: https://github.com/ckeditor/ckeditor5/issues/1141#issuecomment-403403526
        ckeditor.editing.view.document.on(
          'enter',
          (evt, data) => {
            ckeditor.execute('shiftEnter');
            data.preventDefault();
            evt.stop();
          },
          { priority: 'high' }
        );
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
