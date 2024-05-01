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
  <p class="validation-error" v-for="error in errors" :key="error">
    {{ error }}
  </p>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { $gettext } from '@/language/gettext';
import { mapActions, mapGetters } from 'vuex';
import { createFile, FileRef, Folder } from '@/routes/jsonApi';

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
      selectedFolderId: undefined as string | undefined,
      errors: [] as string[],
    };
  },
  async mounted() {
    await this.getUserFolders();
    const wysiwigUploads = this.loadedUserFolders.find(
      (f) => f.attributes.name === 'Wysiwyg Uploads'
    );
    if (wysiwigUploads) {
      this.selectedFolderId = wysiwigUploads.id;
    } else {
      // TODO just create the folder if it's not present.
      // TODO Consider using a separate folder, like "Courseware Uploads"?
      const error = $gettext(
        'Das Verzeichnis "Wysiwyg Uploads" ist nicht vorhanden. ' +
          'Es können keine Dateien hochgeladen werden.'
      );
      this.errors.push(error);
      console.error(error);
    }
  },
  computed: {
    ...mapGetters({
      relatedFolders: 'folders/related',
    }),
    userId(): string {
      // Replaces the vuex getter 'userId'
      return window.STUDIP.USER_ID;
    },
    userObject() {
      return { type: 'users', id: `${this.userId}` };
    },
    loadedUserFolders() {
      let loadedUserFolders: Folder[] = [];
      let userFolders: Folder[] =
        this.relatedFolders({
          parent: this.userObject,
          relationship: 'folders',
        }) ?? [];
      userFolders.forEach((folder: Folder) => {
        if (folder.attributes['folder-type'] === 'PublicFolder') {
          loadedUserFolders.push(folder);
        }
      });

      return loadedUserFolders;
    },
  },
  methods: {
    $gettext,
    ...mapActions({
      _loadRelatedFolders: 'folders/loadRelated',
    }),
    // Typed shim for vuex action 'folders/loadRelated'
    loadRelatedFolders(params: {
      parent: {
        type: string;
        id: string;
      };
      relationship: string;
      options: {
        'page[limit]': number;
      };
    }): Promise<unknown> {
      return this._loadRelatedFolders(params);
    },
    async getUserFolders() {
      const parent = this.userObject;
      const relationship = 'folders';
      const options = { 'page[limit]': 10000 };
      return await this.loadRelatedFolders({ parent, relationship, options });
    },
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
      if (!this.selectedFolderId) {
        const error = $gettext(
          'Kein Ordner ist ausgewählt. Die Datei kann nicht hochgeladen werden.'
        );
        console.error(error);
        this.errors.push(error);
        return;
      }
      this.errors = [];
      this.uploadRequestPromise = createFile({
        file: {
          attributes: {
            name: file.name,
          },
        },
        fileData: file,
        folder: { id: this.selectedFolderId },
      })
        .then((fileRef: FileRef) => {
          this.$emit('fileUploaded', fileRef);
          this.uploadRequestPromise = undefined;
          this.errors = [];
        })
        .catch((error) => {
          console.error(error);
          this.errors.push(error);
          this.uploadRequestPromise = undefined;
        });
    },
  },
});
</script>

<style scoped></style>
