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
    forceSoftBreaks: {
      type: Boolean,
      required: false,
      default: false,
    },
    removeWrappingPTag: {
      type: Boolean,
      required: false,
      default: false,
    },
    disableAutoformat: {
      type: Boolean,
      required: false,
      default: false,
    },
    insertHtmlComment: {
      type: Boolean,
      required: false,
      default: false,
    },
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
  watch: {
    modelValue(value: string) {
      if (!this.fallbackActive) {
        // Copy the new value into CKEditor (only if it's changed)
        const textAreaElement = this.$refs
          .studip_wysiwyg as HTMLTextAreaElement;
        const ckeditor = window.STUDIP.wysiwyg.getEditor(textAreaElement)!;
        const currentData = this.getAdjustedCkeditorData();
        if (currentData !== value) {
          ckeditor.data.set(value);
        }
      }
    },
  },
  methods: {
    initCKE() {
      const textAreaElement = this.$refs.studip_wysiwyg as HTMLTextAreaElement;
      // Process the jQuery event 'wysiwyg.load' which is triggered asynchronously
      // after the wysiwyg editor is mounted using the 'replace()' method.
      textAreaElement.onload = (event: any) => {
        console.info('load');
        let ckeditor = window.STUDIP.wysiwyg.getEditor(textAreaElement)!;
        ckeditor.model.document.on('change:data', () => {
          let data = this.getAdjustedCkeditorData();
          this.$emit('update:modelValue', data);
        });
        ckeditor.ui.focusTracker.on(
          'change:isFocused',
          (event, name, value) => {
            this.$emit('focus', { event, name, value });
          }
        );
        ckeditor.editing.view.document.on(
          'enter',
          (evt, data) => {
            if (this.forceSoftBreaks) {
              // Override 'enter' to insert <br /> instead of <p></p> in the editor.
              // This produces HTML which is more easily parsed in our various learning tasks.
              // Source: https://github.com/ckeditor/ckeditor5/issues/1141#issuecomment-403403526
              ckeditor.execute('shiftEnter');
              data.preventDefault();
              evt.stop();
            }
          },
          { priority: 'high' }
        );
        if (this.disableAutoformat) {
          // Disable the autoformat plugin so that *asterisks* surrounding text
          // are not automatically converted into italic formatting.
          // We use this syntax in the editor for various tasks, such as Fill In The Blanks.
          // (It might be nicer to disable this using 'removePlugins' option in
          // the config for CKEditor5, but the interface of STUDIP.wysiwyg.replace()
          // does not allow us to alter the CKEditor5 config.)
          const autoformat = ckeditor.plugins.get('Autoformat');
          (autoformat as unknown as { isEnabled: boolean }).isEnabled = false;
        }
      };
      // This asynchronous method does not return a promise.  Instead, it fires
      // a 'load' event, which is processed in the above event handler. >:|
      window.STUDIP.wysiwyg.replace(textAreaElement);
      return true;
    },
    updateValue(value: unknown) {
      if (this.fallbackActive) {
        this.$emit('update:modelValue', value);
      }
    },
    getAdjustedCkeditorData(): string {
      const textAreaElement = this.$refs.studip_wysiwyg as HTMLTextAreaElement;
      let ckeditor = window.STUDIP.wysiwyg.getEditor(textAreaElement)!;
      let data = ckeditor.getData();
      if (this.removeWrappingPTag) {
        // Remove the <p> tag wrapping the editor's contents.
        // These tags are inserted automatically by CKEditor5.
        // It appears to be a WONTFIX on their end.
        // See https://github.com/ckeditor/ckeditor5/issues/1537
        data = data.replace(/<p>(.*?)/, '$1').replace(/(.*?)<\/p>/, '$1');
      }
      if (this.insertHtmlComment) {
        /**
         Insert <!--HTML--> at the start of the text so it is recognized by studip as a
         raw HTML that should be inserted as-is into a PHP page when formatted
         using htmlReady.
         */
        if (!window.STUDIP.wysiwyg.hasHtmlMarker(data)) {
          data = window.STUDIP.wysiwyg.markAsHtml(data);
        }
      }
      return data;
    },
  },
});
</script>

<style></style>
