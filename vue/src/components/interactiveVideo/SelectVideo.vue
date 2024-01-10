<!-- Allow us to mutate the prop 'taskDefinition' as much as we want-->
<!-- eslint-disable vue/no-mutating-props -->
<script lang="ts">
import { defineComponent, PropType } from 'vue';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import { $gettext } from '@/language/gettext';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import FileUpload from '@/components/FileUpload.vue';
import { UploadedFile } from '@/routes';

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
      startAtInput: '',
      currentTime: 0,
    };
  },
  computed: {
    /**
     * Either the time the user input, converted into seconds, or an Error with
     * a message explaining why the user's input was invalid.
     */
    parsedStartAtInput(): number | Error {
      // This regex is adapted from https://stackoverflow.com/a/8318367/7359454
      // License CC BY-SA 3.0 https://creativecommons.org/licenses/by-sa/3.0/
      // Accessed and adapted on 10.01.2024 by Ann Yanich
      const timeRegex = /^(?:(?:([0-9]?\d):)?([0-5]?\d):)?([0-5]?\d)$/;
      const match = timeRegex.exec(this.startAtInput);
      if (!match || match.length !== 4) {
        return new Error(
          $gettext('Die Startposition muss der Syntax HH:MM:SS entsprechen.')
        );
      }
      // match[0] is the time string with colons,
      // match[1] is hours (or empty string if not present),
      // match[2] is minutes (or empty string if not present),
      // and match[3] is seconds (always present).
      if (match[1] !== '') {
        return (
          parseInt(match[1]) * 3600 +
          parseInt(match[2]) * 60 +
          parseInt(match[3])
        );
      } else if (match[2] !== '') {
        return parseInt(match[2]) * 60 + parseInt(match[3]);
      } else {
        return parseInt(match[3]);
      }
    },
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
    'taskDefinition.startAt': {
      immediate: true,
      handler: function (startPosition: number) {
        this.startAtInput = formatSecondsToHhMmSs(startPosition);
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
    onBlurStartPositionInput() {
      if (this.parsedStartAtInput instanceof Error) {
        // Reset the input to a good value
        this.startAtInput = formatSecondsToHhMmSs(this.taskDefinition.startAt);
      } else {
        // Save the value into the task definition
        this.taskDefinition.startAt = this.parsedStartAtInput;
      }
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
        <div class="start-at-setting-flex">
          <label>
            {{ $gettext('Anfangen um') }}
            <input
              type="text"
              v-model="startAtInput"
              @blur="onBlurStartPositionInput"
            />
          </label>
          <button type="button" class="button" @click="onClickUseCurrentTime">
            {{ $gettext('Aktuelle Position setzen') }}
          </button>
        </div>
        <pre>{{ parsedStartAtInput }}</pre>
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
