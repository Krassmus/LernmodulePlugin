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
      currentTime: 0,
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
    'taskDefinition.startAt': function (startAt: number) {
      (
        this.$refs.videoPlayer as InstanceType<typeof VideoPlayer>
      )?.player!.currentTime(startAt);
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
  <div
    :class="{
      hidden: taskDefinition.video.type !== 'none',
    }"
  >
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
          <FileUpload
            @file-uploaded="onUploadStudipVideo"
            :accept="'video/*'"
          />
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
          <button
            class="button accept"
            @click="onSaveYoutubeVideo"
            :disabled="
              taskDefinition.video.type === 'youtube' &&
              taskDefinition.video.url === youtubeUrlInput
            "
          >
            {{ $gettext('Übernehmen') }}
          </button>
        </div>
      </div>
    </div>
  </div>
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
    <fieldset>
      <legend>{{ $gettext('Einstellungen') }}</legend>
      <label>
        <!-- eslint-disable vue/no-mutating-props -->
        <input type="checkbox" v-model="taskDefinition.autoplay" />
        {{ $gettext('Automatisch abspielen') }}
      </label>
      <div class="start-at-setting">
        <label>
          {{ $gettext('Anfangen um') }}
          <input
            type="number"
            class="wide"
            v-model.number="taskDefinition.startAt"
          />
        </label>
        <button type="button" class="button" @click="onClickUseCurrentTime">
          {{ $gettext('Aktuelle Zeit übernehmen') }}
        </button>
      </div>
    </fieldset>
  </form>
</template>

<style scoped lang="scss">
.picker {
  display: grid;
  grid-template-columns: 1fr 1px 1fr;
  gap: 0.5em;
  .separator {
    background: #d0d7e3;
  }
  &.hidden {
    display: none;
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
  .video-player {
    margin-bottom: 1em;
  }
  .video-preview-actions {
    margin-top: 1em;
    text-align: end;
  }
}
.start-at-setting {
  display: flex;
  gap: 1em;
  align-items: end;
}
input[type='number'].wide {
  max-width: 12em;
}
</style>
