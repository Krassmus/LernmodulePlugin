<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import { $gettext } from '@/language/gettext';
import { InteractiveVideoTask, Video } from '@/models/InteractiveVideoTask';
import FileUpload from '@/components/FileUpload.vue';
import VideoTimeInput from '@/components/interactiveVideo/VideoTimeInput.vue';
import FilePicker, { FilePickerFile } from '@/components/studip/FilePicker.vue';
import { FileRef, fileRefsSchema } from '@/routes/jsonApi';
import { fileDetailsUrl, fileIdToUrl } from '@/models/TaskDefinition';
import { mapActions, mapGetters } from 'vuex';
import {
  TaskEditorState,
  taskEditorStateSymbol,
} from '@/components/taskEditorState';
import { cloneDeep } from 'lodash';

export default defineComponent({
  name: 'SelectVideo',
  props: {
    taskDefinition: {
      type: Object as PropType<InteractiveVideoTask>,
      required: true,
    },
  },
  setup() {
    return {
      taskEditor: inject<TaskEditorState>(taskEditorStateSymbol),
    };
  },
  data() {
    return {
      modelTaskDefinition: cloneDeep(this.taskDefinition),
      youtubeUrlInput: '',
      startPositionInputError: undefined as Error | undefined,
      currentTime: 0,
      selectedFolderId: '',
      selectedFile: undefined as FilePickerFile | undefined,
      selectedFileId: '',
    };
  },
  watch: {
    // Synchronize state taskDefinition -> modelTaskDefinition.
    taskDefinition: {
      immediate: true,
      handler: function (): void {
        this.modelTaskDefinition = cloneDeep(this.taskDefinition);
      },
    },
    'taskDefinition.video': {
      immediate: true,
      handler: async function (value: Video) {
        switch (value.type) {
          case 'none':
            break;
          case 'studipFileReference':
            if (
              this.selectedFile === undefined ||
              this.selectedFile.id !== value.file_id
            ) {
              // Get metadata for file
              await this.loadFileRef({ id: value.file_id });
              const ref = fileRefsSchema.parse(
                this.fileRefById({ id: value.file_id })
              );
              this.selectedFile = {
                id: value.file_id,
                name: ref.attributes.name,
                mime_type: ref.attributes['mime-type'],
                download_url: ref.meta['download-url'],
              };
            }
            break;
          case 'youtube':
            this.youtubeUrlInput = value.url;
        }
      },
    },
  },
  computed: {
    ...mapGetters({
      fileRefById: 'file-refs/byId',
    }),
  },
  methods: {
    fileDetailsUrl,
    fileIdToUrl,
    $gettext,
    ...mapActions({
      loadFileRef: 'file-refs/loadById',
    }),
    // Synchronize state modelTaskDefinition -> taskDefinition.
    updateTaskDefinition(undoBatch?: unknown) {
      this.taskEditor!.performEdit({
        newTaskDefinition: cloneDeep(this.modelTaskDefinition),
        undoBatch: undoBatch ?? {},
      });
    },
    onTimeUpdate(time: number) {
      this.currentTime = time;
    },
    onClickUseCurrentTime() {
      this.modelTaskDefinition.startAt = this.currentTime;
      this.updateTaskDefinition('inputStartAt');
    },
    updateCurrentFile(file: FilePickerFile) {
      this.selectedFile = file;
      this.selectedFileId = file.id;
    },
    onSaveYoutubeVideo() {
      this.modelTaskDefinition.video = {
        type: 'youtube',
        url: this.youtubeUrlInput,
      };
      this.updateTaskDefinition();
    },
    onSaveUploadedFile() {
      if (!this.selectedFile) {
        throw new Error('No selected file');
      }
      this.modelTaskDefinition.video = {
        v: 2,
        type: 'studipFileReference',
        file_id: this.selectedFile.id,
      };
      this.updateTaskDefinition();
    },
    deleteVideo() {
      this.modelTaskDefinition.video = {
        type: 'none',
      };
      this.updateTaskDefinition();
    },
    onUploadStudipVideo(file: FileRef) {
      this.modelTaskDefinition.video = {
        v: 2,
        type: 'studipFileReference',
        file_id: file.id,
      };
      this.updateTaskDefinition();
      this.selectedFile = {
        id: file.id,
        name: file.attributes.name,
        mime_type: file.attributes['mime-type'],
        download_url: file.meta['download-url'],
      };
    },
  },
  components: {
    VideoTimeInput,
    FileUpload,
    VideoPlayer,
    FilePicker,
  },
});
</script>

<template>
  <template v-if="taskDefinition.video.type === 'none'">
    <p>
      {{
        $gettext(
          'Lade ein Video hoch oder füge einen Link zu einem Youtube-Video ein.'
        )
      }}
    </p>
    <div class="picker">
      <form class="default">
        <fieldset>
          <legend>
            {{ $gettext('Stud.IP-Video') }}
          </legend>
          <label>
            {{ $gettext('Neues Video hochladen') }} <br />
            <FileUpload
              @file-uploaded="onUploadStudipVideo"
              :accept="'video/*'"
            />
          </label>
          <label>
            {{ $gettext('Vorhandenes Video auswählen') }}
            <FilePicker
              v-model="selectedFileId"
              is-video
              @selectFile="updateCurrentFile"
            />
          </label>
          <div class="bottom-actions">
            <button
              type="button"
              class="button accept"
              @click="onSaveUploadedFile"
            >
              {{ $gettext('Übernehmen') }}
            </button>
          </div>
          <!--          <div style="white-space: pre-wrap">-->
          <!--            {{ { selectedFile, selectedFileId } }}-->
          <!--          </div>-->
        </fieldset>
      </form>
      <form class="default">
        <fieldset>
          <legend>
            {{ $gettext('Youtube-Video') }}
          </legend>
          <label>
            {{ $gettext('Youtube-URL') }}
            <input
              class="youtube-url-input"
              type="text"
              v-model="youtubeUrlInput"
            />
          </label>
          <div class="bottom-actions">
            <button
              type="submit"
              class="button accept"
              @click="onSaveYoutubeVideo"
            >
              {{ $gettext('Übernehmen') }}
            </button>
          </div>
        </fieldset>
      </form>
    </div>
  </template>
  <div class="video-preview" v-if="taskDefinition.video.type !== 'none'">
    <VideoPlayer
      ref="videoPlayer"
      :task="taskDefinition"
      class="video-player"
      @timeupdate="onTimeUpdate"
    />
    <p v-if="taskDefinition.video.type === 'youtube'">
      {{ $gettext('Youtube-Video: ') }}
      <a :href="taskDefinition.video.url" target="_blank">
        {{ taskDefinition.video.url }}</a
      >
    </p>
    <p v-else-if="taskDefinition.video.type === 'studipFileReference'">
      {{ $gettext('Hochgeladenes Video: ') }}
      <a :href="fileDetailsUrl(taskDefinition.video.file_id)" target="_blank">
        <template v-if="selectedFile">
          {{ selectedFile.name }}
        </template>
        <template v-else>
          {{ taskDefinition.video.file_id }}
        </template>
      </a>
    </p>
    <div class="video-preview-actions">
      <button class="button trash" @click="deleteVideo">
        {{ $gettext('Video löschen') }}
      </button>
    </div>
  </div>
  <form class="form default">
    <!-- Prevent implicit submission of the form -->
    <button
      type="submit"
      disabled
      style="display: none"
      aria-hidden="true"
    ></button>
    <fieldset>
      <legend>{{ $gettext('Einstellungen') }}</legend>
      <label>
        <input
          type="checkbox"
          v-model="modelTaskDefinition.autoplay"
          @input="updateTaskDefinition"
        />
        {{ $gettext('Automatisch abspielen') }}
      </label>
      <div class="start-at-setting">
        <div class="start-at-setting-flex">
          <label>
            {{ $gettext('Anfangen um') }}
            <VideoTimeInput
              :class="{ invalid: !!startPositionInputError }"
              :aria-invalid="!!startPositionInputError"
              v-model="modelTaskDefinition.startAt"
              @input="updateTaskDefinition('inputStartAt')"
              @update:error="(error) => (startPositionInputError = error)"
            />
          </label>
          <button type="button" class="button" @click="onClickUseCurrentTime">
            {{ $gettext('Aktuelle Position setzen') }}
          </button>
        </div>
        <div class="errors" v-if="startPositionInputError">
          {{ startPositionInputError }}
        </div>
      </div>
      <label v-if="false">
        <!-- hidden until 'disable navigation' feature is ready -->
        {{ $gettext('Navigation deaktivieren') }}
        <select
          v-model="modelTaskDefinition.disableNavigation"
          @input="updateTaskDefinition"
        >
          <option :value="'not disabled'">
            {{ $gettext('Nicht deaktiviert') }}
          </option>
          <option :value="'forward disabled'">
            {{ $gettext('Vorspulen deaktiviert') }}
          </option>
          <option :value="'forward and backward disabled'">
            {{ $gettext('Vorspulen und Rückspulen deaktiviert') }}
          </option>
        </select>
      </label>
    </fieldset>
  </form>
</template>

<style scoped lang="scss">
.picker {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5em;
  &.hidden {
    display: none;
  }
  > form {
    > fieldset {
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      .bottom-actions {
        display: flex;
        justify-content: flex-end;
        button.button {
          margin-right: 0.6em;
          margin-bottom: 0;
          margin-top: 0;
        }
      }
    }
  }
  padding-bottom: 1em;
}
.youtube-url-input {
  width: 100%;
  max-width: 48em;
  box-sizing: border-box;
}
.video-preview {
  .video-player {
    margin-bottom: 1em;
  }
  .video-preview-actions {
    margin-top: 1em;
    text-align: end;
  }
}
.start-at-setting {
  > .start-at-setting-flex {
    display: flex;
    gap: 1em;
    align-items: flex-end;
  }
}
input[type='number'].wide {
  max-width: 12em;
}
</style>
