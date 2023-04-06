<template>
  <span v-if="uploadRequestPromise">{{
    $gettext('Datei wird hochgeladen')
  }}</span>
  <input
    v-else
    ref="fileInput"
    type="file"
    accept="image/*"
    @change="onInputChange"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { uploadImage, UploadImageResponse } from '@/routes';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'ImageUpload',
  emits: ['imageUploaded'],
  data() {
    return {
      // Wenn dieser Promise vorhanden ist, heißt das, dass eine HTTP-Anfrage
      // unterwegs ist.
      uploadRequestPromise: undefined as Promise<unknown> | undefined,
    };
  },
  methods: {
    $gettext,
    onInputChange(event: InputEvent): void {
      if (this.uploadRequestPromise) {
        console.warn(
          'ImageUpload: onInputChange fired while uploadRequestPromise is already defined.'
        );
        return;
      }
      const input = this.$refs.fileInput as HTMLInputElement;
      const file = input.files?.item(0);
      if (!file) {
        console.warn(
          'ImageUpload: onInputChange fired, but input.files[] is empty.'
        );
        return;
      }
      this.uploadRequestPromise = uploadImage(file)
        .then((res) => {
          this.$emit('imageUploaded', res.files[0].url);
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
