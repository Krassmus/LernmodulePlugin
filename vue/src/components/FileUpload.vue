<template>
  <span v-if="uploadRequestPromise">{{
    $gettext('Datei wird hochgeladen')
  }}</span>
  <div v-else>
    <input
      ref="fileInput"
      type="file"
      :accept="accept"
      @change="onInputChange"
    />
    <p class="validation-error" v-for="error in errors" :key="error">
      {{ error }}
    </p>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { $gettext } from '@/language/gettext';
import { mapActions, mapGetters } from 'vuex';
import {
  createFile,
  createFolder,
  getFolders,
  FileRef,
  FolderRef,
} from '@/routes/jsonApi';

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
      uploadFolderName: 'Lernmodule Uploads',
    };
  },
  async mounted() {
    const folders = await getFolders({ userId: this.userId });

    const lernmoduleUploads = folders.find(
      (f) => f.attributes.name === this.uploadFolderName
    );

    if (lernmoduleUploads) {
      console.log('Found', this.uploadFolderName, 'folder.');
      this.selectedFolderId = lernmoduleUploads.id;
    } else {
      console.log(
        'Folder',
        this.uploadFolderName,
        "not found. Trying to create it under the user's root folder."
      );
      try {
        const rootFolder = folders.find(
          (f) => f.attributes['folder-type'] === 'RootFolder'
        );

        if (!rootFolder) {
          const errorMessage = $gettext(
            'Das Stammverzeichnis des Benutzers konnte nicht gefunden werden. ' +
              'Es können keine Dateien hochgeladen werden.'
          );
          this.errors.push(errorMessage);
          console.error(errorMessage);
          return;
        }

        if (rootFolder) {
          console.log(
            'Found user root folder',
            rootFolder.attributes.name,
            '(' + rootFolder.attributes['folder-type'] + ')'
          );
        }

        const newFolder = await createFolder({
          userId: this.userId,
          name: this.uploadFolderName,
          parentFolderId: rootFolder.id, // Use the root folder ID
          folderType: 'PublicFolder',
        });

        if (newFolder) {
          console.log(
            'Created folder',
            newFolder.attributes.name,
            'in folder',
            rootFolder.attributes.name
          );
        }

        this.selectedFolderId = newFolder.id;
      } catch (error) {
        const errorMessage = $gettext(
          'Das Verzeichnis' +
            this.uploadFolderName +
            'konnte nicht erstellt werden. ' +
            'Es können keine Dateien hochgeladen werden.'
        );
        this.errors.push(errorMessage);
        console.error(errorMessage, error);
      }
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
      let loadedUserFolders: FolderRef[] = [];
      let userFolders: FolderRef[] =
        this.relatedFolders({
          parent: this.userObject,
          relationship: 'folders',
        }) ?? [];
      userFolders.forEach((folder: FolderRef) => {
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
          /*
           TODO #23 Check for common errors and translate them so they are
             understandable for users. E.g. AxiosError 413 (File too large).
          */
          console.error(error);
          this.errors.push(error);
          this.uploadRequestPromise = undefined;
        });
    },
  },
});
</script>

<style scoped></style>
