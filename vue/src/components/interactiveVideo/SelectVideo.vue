<!-- Allow us to mutate the prop 'taskDefinition' as much as we want-->
<!-- eslint-disable vue/no-mutating-props -->
<script lang="ts">
import { defineComponent, PropType } from 'vue';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import { $gettext } from '@/language/gettext';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import FileUpload from '@/components/FileUpload.vue';
import { UploadedFile } from '@/routes';

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
    };
  },
  watch: {
    'taskDefinition.video': {
      immediate: true,
      handler: function (value) {
        if (value.type === 'youtube') {
          this.youtubeUrlInput = value.url;
        }
      },
    },
  },
  methods: {
    $gettext,
    onSaveYoutubeVideo() {
      this.taskDefinition.video = {
        type: 'youtube',
        url: this.youtubeUrlInput,
      };
    },
    deleteVideo() {
      this.taskDefinition.video = {
        type: 'none',
      };
    },
    onUploadStudipVideo(file: UploadedFile) {
      this.taskDefinition.video = {
        type: 'studipFileReference',
        file,
      };
    },
  },
  components: { FileUpload, VideoPlayer },
});
</script>

<template>
  <p>
    {{
      $gettext(
        'Lade ein Video hoch oder füge einen Link zu einem Youtube-Video ein.'
      )
    }}
  </p>
  <div class="picker">
    <div>
      <label>
        {{ $gettext('Video hochladen') }}
        <FileUpload @file-uploaded="onUploadStudipVideo" :accept="'video/*'" />
      </label>
    </div>
    <div class="separator" aria-hidden="true" role="presentation"></div>
    <div>
      <label>
        {{ $gettext('Youtube-URL') }}
        <input
          class="youtube-url-input"
          type="text"
          v-model="youtubeUrlInput"
        />
      </label>
      <div class="youtube-url-actions">
        <button class="button" @click="onSaveYoutubeVideo">
          {{ $gettext('Übernehmen') }}
        </button>
      </div>
    </div>
  </div>
  <div class="video-preview">
    <p style="text-align: center" v-if="taskDefinition.video.type === 'none'">
      {{ $gettext('Aktuell ist kein Video ausgewählt.') }}
    </p>
    <div v-else>
      <div v-if="taskDefinition.video.type === 'youtube'">
        <VideoPlayer :task="taskDefinition" />
      </div>
      <div v-else-if="taskDefinition.video.type === 'studipFileReference'">
        <VideoPlayer :task="taskDefinition" />
      </div>
      <div class="video-preview-actions">
        <button class="button trash" @click="deleteVideo">
          {{ $gettext('Video löschen') }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.picker {
  display: grid;
  grid-template-columns: 1fr 1px 1fr;
  gap: 0.5em;
  .separator {
    background: #d0d7e3;
  }
}
.youtube-url-actions {
  display: flex;
  justify-content: flex-end;
  button.button {
    margin-right: 0;
  }
}
.youtube-url-input {
  width: 100%;
  max-width: 48em;
  box-sizing: border-box;
}
.video-preview {
  margin-top: 1em;
  .video-preview-actions {
    margin-top: 1em;
    text-align: end;
  }
}
</style>
