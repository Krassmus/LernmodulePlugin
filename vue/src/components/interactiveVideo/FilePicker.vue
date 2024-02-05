<!-- This component is a port of the courseware-file-chooser component from
the Stud.IP core 5.4, rewritten in Typescript / Vue 3, removing its dependency
 on the core VueX 3.x store, allowing it to be used in our Vue 3 app. -->
<template>
  <div class="cw-file-chooser">
    <span v-translate>Ordner-Filter</span>
    <FolderPicker allowUserFolders unchoose v-model="selectedFolderId" />
    <span v-translate>Datei</span>
    <select v-model="currentValue" @change="selectFile">
      <option v-show="canBeEmpty" value="">
        <translate>Keine Auswahl</translate>
      </option>
      <option v-for="(file, index) in files" :key="index" :value="file.id">
        {{ file.name }}
      </option>
      <option v-show="files.length === 0" disabled>
        <translate>Keine Dateien vorhanden</translate>
      </option>
    </select>
  </div>
</template>

<script lang="ts">
import FolderPicker from '@/components/interactiveVideo/FolderPicker.vue';
import { mapActions } from 'vuex';

export default {
  name: 'FilePicker',
  components: { FolderPicker },
  props: {
    value: String,
    mimeType: { type: String, default: '' },
    isImage: { type: Boolean, default: false },
    isVideo: { type: Boolean, default: false },
    isAudio: { type: Boolean, default: false },
    isDocument: { type: Boolean, default: false },
    canBeEmpty: { type: Boolean, default: false },
  },
  data() {
    return {
      currentValue: '',
      selectedFolderId: '',
      files: [],
    };
  },
  computed: {
    // ...mapGetters({
    //   fileRefById: 'file-refs/byId',
    //   relatedFileRefs: 'file-refs/related',
    //   urlHelper: 'urlHelper',
    //   relatedTermOfUse: 'terms-of-use/related',
    // }),
    userId(): string {
      // TODO replace the vuex getter 'userId'
      throw new Error('not implemented');
      return '';
    },
  },
  methods: {
    urlHelper() {
      // TODO replace the vuex getter 'urlHelper'
      throw new Error('not implemented');
      return '';
    },
    relatedTermOfUse() {
      // TODO replace the vuex getter 'terms-of-use/related'
      throw new Error('not implemented');
      return '';
    },
    relatedFileRefs() {
      // TODO replace the vuex getter 'file-refs/related'
      throw new Error('not implemented');
      return '';
    },
    fileRefById() {
      // TODO replace the vuex getter 'file-refs/byId'
      throw new Error('not implemented');
      return '';
    },
    loadFileRef(): void {
      // TODO replace the VueX action 'file-refs/loadById'
    },
    loadRelatedFileRefs(): void {
      // TODO replace the VueX action 'file-refs/loadRelated'
    },
    // ...mapActions({
    //   loadFileRef: 'file-refs/loadById',
    //   loadRelatedFileRefs: 'file-refs/loadRelated',
    // }),
    selectFile() {
      this.$emit(
        'selectFile',
        this.files.find((file) => file.id === this.currentValue)
      );
    },
    filterFiles(loadArray) {
      const filterFile = (file) => {
        let fileTermsOfUse = this.relatedTermOfUse({
          parent: file,
          relationship: 'terms-of-use',
        });

        if (
          fileTermsOfUse !== null &&
          fileTermsOfUse.attributes['download-condition'] !== 0
        ) {
          return false;
        }
        if (
          this.mimeType !== '' &&
          this.mimeType !== file.attributes['mime-type']
        ) {
          return false;
        }
        if (this.isImage && !file.attributes['mime-type'].includes('image')) {
          return false;
        }
        const videoConditions = ['video/mp4', 'video/ogg', 'video/webm'];
        if (
          this.isVideo &&
          !videoConditions.some((condition) =>
            file.attributes['mime-type'].includes(condition)
          )
        ) {
          return false;
        }
        const audioConditions = [
          'audio/wav',
          'audio/ogg',
          'audio/webm',
          'audio/flac',
          'audio/mpeg',
          'audio/x-m4a',
          'audio/mp4',
        ];
        if (
          this.isAudio &&
          !audioConditions.some((condition) =>
            file.attributes['mime-type'].includes(condition)
          )
        ) {
          return false;
        }
        const officeConditions = ['application/pdf']; //TODO enable more mime types
        if (
          this.isDocument &&
          !officeConditions.some((condition) =>
            file.attributes['mime-type'].includes(condition)
          )
        ) {
          return false;
        }

        return true;
      };

      return loadArray.filter(filterFile).map((file) => ({
        id: file.id,
        name: file.attributes.name,
        mime_type: file.attributes['mime-type'],
        download_url: this.urlHelper.getURL(
          'sendfile.php',
          { type: 0, file_id: file.id, file_name: file.attributes.name },
          true
        ),
      }));
    },
    async getFolderFiles() {
      const parent = { type: 'folders', id: `${this.selectedFolderId}` };
      const relationship = 'file-refs';
      const options = { include: 'terms-of-use', 'page[limit]': 10000 };
      await this.loadRelatedFileRefs({ parent, relationship, options });

      const files = this.relatedFileRefs({ parent, relationship });
      this.files = this.filterFiles(files);
    },
  },
  async mounted() {
    console.log('mounted');
    if (this.value != '') {
      await this.loadFileRef({ id: this.value });
      const fileRef = this.fileRefById({ id: this.value });

      if (fileRef) {
        this.selectedFolderId = fileRef.relationships.parent.data.id;
        this.currentValue = this.value;
      }
    }
  },
  watch: {
    selectedFolderId() {
      if (this.selectedFolderId !== '') {
        this.getFolderFiles();
      }
    },
    value(newValue, oldValue) {
      if (newValue === '') {
        this.selectedFolderId = '';
        this.currentValue = '';
      }
    },
  },
};
</script>
