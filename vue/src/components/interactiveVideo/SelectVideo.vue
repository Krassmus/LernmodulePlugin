<!-- Allow us to mutate the prop 'taskDefinition' as much as we want-->
<!-- eslint-disable vue/no-mutating-props -->
<script lang="ts">
import { defineComponent, PropType } from 'vue';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import { $gettext } from '@/language/gettext';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import FileUpload from '@/components/FileUpload.vue';
import { WysiwygUploadedFile } from '@/routes/lernmodule';
import VideoTimeInput from '@/components/interactiveVideo/VideoTimeInput.vue';
import FilePicker from '@/components/courseware-components-ported-to-vue3/FilePicker.vue';

function formatSecondsToHhMmSs(time: number): string {
  let hours = 0,
    minutes = 0,
    seconds = 0;
  while (time > 3600) {
    time -= 3600;
    hours += 1;
  }
  while (time > 60) {
    time -= 60;
    minutes += 1;
  }
  seconds = time;
  function twoDigits(n: number): string {
    return n.toLocaleString('de-DE', {
      minimumIntegerDigits: 2,
      maximumFractionDigits: 0,
    });
  }
  return `${twoDigits(hours)}:${twoDigits(minutes)}:${twoDigits(seconds)}`;
}

interface JsonApiFile {
  id: string;
  name: string;
  mime_type: string;
  download_url: string;
}

export default defineComponent({
  name: 'SelectVideo',
  props: {
    taskDefinition: {
      type: Object as PropType<InteractiveVideoTask>,
      required: true,
    },
  },
  data() {
    return {
      youtubeUrlInput: '',
      startPositionInputError: undefined as Error | undefined,
      currentTime: 0,
      selectedFolderId: '',
      selectedFile: undefined as JsonApiFile | undefined,
      selectedFileId: '',
    };
  },
  watch: {
    'taskDefinition.video': {
      immediate: true,
      handler: function (value) {
        if (value.type === 'youtube') {
          this.youtubeUrlInput = value.url;
        } else {
          this.youtubeUrlInput = '';
        }
      },
    },
  },
  methods: {
    $gettext,
    onTimeUpdate(time: number) {
      this.currentTime = time;
    },
    onClickUseCurrentTime() {
      this.taskDefinition.startAt = this.currentTime;
    },
    updateCurrentFile(file: JsonApiFile) {
      this.selectedFile = file;
      this.selectedFileId = file.id;
    },
    onSaveYoutubeVideo() {
      this.taskDefinition.video = {
        type: 'youtube',
        url: this.youtubeUrlInput,
      };
    },
    onSaveUploadedFile() {
      if (!this.selectedFile) {
        return;
      }
      this.taskDefinition.video = {
        type: 'studipFileReference',
        file: {
          name: this.selectedFile.name,
          type: this.selectedFile.mime_type,
          url: this.selectedFile.download_url,
        },
      };
    },
    deleteVideo() {
      this.taskDefinition.video = {
        type: 'none',
      };
    },
    onUploadStudipVideo(file: WysiwygUploadedFile) {
      this.taskDefinition.video = {
        type: 'studipFileReference',
        file,
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
            {{ $gettext('Neues Video hochladen') }}
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
          <div class="youtube-url-actions">
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
          <div class="youtube-url-actions">
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
      <a :href="taskDefinition.video.file.url" target="_blank">{{
        taskDefinition.video.file.name
      }}</a>
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
        <!-- eslint-disable vue/no-mutating-props -->
        <input type="checkbox" v-model="taskDefinition.autoplay" />
        {{ $gettext('Automatisch abspielen') }}
      </label>
      <div class="start-at-setting">
        <div class="start-at-setting-flex">
          <label>
            {{ $gettext('Anfangen um') }}
            <VideoTimeInput
              :class="{ invalid: !!startPositionInputError }"
              :aria-invalid="!!startPositionInputError"
              v-model="taskDefinition.startAt"
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
        <select v-model="taskDefinition.disableNavigation">
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
      .youtube-url-actions {
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
