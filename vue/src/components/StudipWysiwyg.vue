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
      const el = this.$refs.studip_wysiwyg as Element;
      // TODO This is a namespaced event, wysiwyg.load -- that's a jquery concept --
      // I guess that we should consider using jquery here instead of onload.
      // not sure what's the cleanest reading way to do this.
      (el as any).onload = (event: any) => {
        console.info('load');
        let wysiwyg_editor = window.STUDIP.wysiwyg.getEditor(
          this.$refs.studip_wysiwyg as Element
        )!;
        wysiwyg_editor.on('blur', () => {
          //console.log('cke blur');
        });
        wysiwyg_editor.model.document.on('change:data', () => {
          this.$emit('update:modelValue', wysiwyg_editor.getData());
        });
      };
      window.STUDIP.wysiwyg.replace(el);
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
