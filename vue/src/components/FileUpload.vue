<template>
  <span v-if="uploadRequestPromise">{{
    $gettext('Datei wird hochgeladen')
  }}</span>
  <input
    v-else
    ref="fileInput"
    type="file"
    :accept="accept"
    @change="onInputChange"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { uploadFile } from '@/routes';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'FileUpload',
  emits: ['fileUploaded'],
  props: {
    // The 'accept' property passed to the <input type="file"> element.  Default: 'image/*'
    accept: {
      type: String,
      required: false,
      default: 'image/*',
    },
  },
  data() {
    return {
      // Wenn dieser Promise vorhanden ist, heißt das, dass eine HTTP-Anfrage
      // unterwegs ist.
      uploadRequestPromise: undefined as Promise<unknown> | undefined,
    };
  },
  methods: {
    $gettext,
    onInputChange(event: Event): void {
      if (this.uploadRequestPromise) {
        console.warn(
          'FileUpload: onInputChange fired while uploadRequestPromise is already defined.'
        );
        return;
      }
      const input = this.$refs.fileInput as HTMLInputElement;
      const file = input.files?.item(0);
      if (!file) {
        console.warn(
          'FileUpload: onInputChange fired, but input.files[] is empty.'
        );
        return;
      }
      this.uploadRequestPromise = uploadFile(file)
        .then((res) => {
          this.$emit('fileUploaded', res.files[0].url);
          this.uploadRequestPromise = undefined;
        })
        .catch((error) => {
          console.error(error);
          // TODO Display the error somewhere!
          this.uploadRequestPromise = undefined;
        });
    },
  },
});
</script>

<style scoped></style>
