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
      let view = this;
      window.STUDIP.wysiwyg.replace(view.$refs.studip_wysiwyg as Element);
      let wysiwyg_editor =
        window.CKEDITOR.instances[(view.$refs.studip_wysiwyg as Element).id];
      wysiwyg_editor.on('blur', function () {
        //console.log('cke blur');
      });
      wysiwyg_editor.on('change', function () {
        view.$emit('update:modelValue', wysiwyg_editor.getData());
      });
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
