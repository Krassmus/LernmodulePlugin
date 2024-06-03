<!-- This component is a port of the courseware-folder-chooser component from
the Stud.IP core 5.4, rewritten in Typescript / Vue 3, removing its dependency
 on the core VueX store. -->
<template>
  <select v-model="currentValue" @change="changeSelection">
    <option v-if="unchoose" value="">
      <translate>kein Ordner ausgewählt</translate>
    </option>
    <optgroup
      v-if="this.context.type === 'courses'"
      :label="textOptGroupCourse"
    >
      <option
        v-for="folder in loadedCourseFolders"
        :key="folder.id"
        :value="folder.id"
      >
        {{ folder.attributes.name }}
      </option>
    </optgroup>
    <optgroup v-if="allowUserFolders" :label="textOptGroupUser">
      <option
        v-for="folder in loadedUserFolders"
        :key="folder.id"
        :value="folder.id"
      >
        {{ folder.attributes.name }}
      </option>
    </optgroup>
  </select>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Context } from '@/models/CoursewareBlockIframeMessages';
import { coursewareBlockStore } from '@/store';
import { mapActions, mapGetters } from 'vuex';
import { FolderRef } from '@/routes/jsonApi';

function filterCourseFolders(
  folders: FolderRef[],
  { allowHomeworkFolders }: { allowHomeworkFolders: boolean }
) {
  const validatedParents = new Map();

  return folders.filter((folder: FolderRef) => {
    if (validateParentFolder(folder)) {
      switch (folder.attributes['folder-type']) {
        case 'HiddenFolder':
          if (folder.attributes['data-content']['download_allowed'] === 1) {
            return true;
          }
          break;
        case 'HomeworkFolder':
          if (allowHomeworkFolders) {
            return true;
          }
          break;
        default:
          return true;
      }
    }
  });

  function validateParentFolder(folder: FolderRef): boolean {
    let isValid = true;
    if (folder?.relationships?.parent) {
      let parentId = folder.relationships.parent.data.id;
      if (validatedParents.has(parentId)) {
        isValid = validatedParents.get(parentId);
      } else {
        let parent = folders.find((f) => f.id === parentId);
        if (parent) {
          isValid = hiddenParentFolderValidation(parent);
          validatedParents.set(parentId, isValid);
        }
      }
    }
    return isValid;
  }

  function hiddenParentFolderValidation(parentFolder: FolderRef): boolean {
    if (parentFolder.attributes['folder-type'] === 'HiddenFolder') {
      return false;
    } else if (parentFolder?.relationships?.parent) {
      // Recursively validating the parents.
      return validateParentFolder(parentFolder);
    } else {
      return true;
    }
  }
}

export default defineComponent({
  name: 'FolderPicker',
  props: {
    value: {
      type: String,
      required: false,
      default: '',
    },
    allowUserFolders: { type: Boolean, default: false },
    allowHomeworkFolders: { type: Boolean, default: false },
    unchoose: { type: Boolean, default: false },
  },
  data() {
    return {
      currentValue: '',
      textOptGroupCourse: this.$gettext('Dateibereich dieser Veranstaltung'),
      textOptGroupUser: this.$gettext('Persönlicher Dateibereich'),
    };
  },
  computed: {
    ...mapGetters({
      relatedFolders: 'folders/related',
    }),
    context(): Context {
      // Replaces the vuex getter 'context'
      return coursewareBlockStore.studipContext;
    },
    userId(): string {
      // Replaces the vuex getter 'userId'
      return window.STUDIP.USER_ID;
    },
    courseObject() {
      return { type: 'courses', id: `${this.context.id}` };
    },
    userObject() {
      return { type: 'users', id: `${this.userId}` };
    },
    loadedCourseFolders() {
      return filterCourseFolders(
        this.relatedFolders({
          parent: this.courseObject,
          relationship: 'folders',
        }) ?? [],
        {
          allowHomeworkFolders: this.allowHomeworkFolders,
        }
      );
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

    changeSelection() {
      this.$emit('input', this.currentValue);
    },

    getCourseFolders() {
      const parent = this.courseObject;
      const relationship = 'folders';
      const options = { 'page[limit]': 10000 };

      return this.loadRelatedFolders({ parent, relationship, options });
    },

    getUserFolders() {
      const parent = this.userObject;
      const relationship = 'folders';
      const options = { 'page[limit]': 10000 };

      return this.loadRelatedFolders({ parent, relationship, options });
    },

    confirmSelectedFolder() {
      const folders: FolderRef[] = this.loadedUserFolders.concat(
        this.loadedCourseFolders
      );

      let folder = folders.find((folder) => folder.id === this.currentValue);

      if (this.currentValue !== '' && folder === undefined) {
        this.currentValue = '';
        this.changeSelection();
      }
    },
  },
  async mounted() {
    this.currentValue = this.value;
    if (this.context.type !== 'users') {
      await this.getCourseFolders();
    }
    await this.getUserFolders();
    this.confirmSelectedFolder();
  },
  watch: {
    value() {
      this.currentValue = this.value;
    },
  },
});
</script>
