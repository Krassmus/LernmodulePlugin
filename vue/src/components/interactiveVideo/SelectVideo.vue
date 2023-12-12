<!-- Allow us to mutate the prop 'taskDefinition' as much as we want-->
<!-- eslint-disable vue/no-mutating-props -->
<script lang="ts">
import { defineComponent, PropType } from 'vue';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import { $gettext } from '@/language/gettext';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';

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
  },
  components: { VideoPlayer },
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
  <ul class="picker">
    <li>
      <label>
        {{ $gettext('Video hochladen') }}
        <input type="file" @change="onPickVideoFile" />
      </label>
    </li>
    <li class="separator" aria-hidden="true" role="presentation"></li>
    <li>
      <label>
        {{ $gettext('Youtube-URL') }}
        <input
          class="youtube-url-input"
          type="text"
          v-model="youtubeUrlInput"
        />
      </label>
      <button class="button" @click="onSaveYoutubeVideo">
        {{ $gettext('Anwenden') }}
      </button>
    </li>
  </ul>
  <div class="video-preview">
    <div v-if="taskDefinition.video.type === 'none'">
      {{ $gettext('Aktuell kein Video ausgewählt') }}
    </div>
    <div v-else>
      <div v-if="taskDefinition.video.type === 'youtube'">
        <VideoPlayer :task="taskDefinition" />
      </div>
      <div v-else-if="taskDefinition.video.type === 'studip'">
        Stud.IP video. (Not implemented.) {{ taskDefinition.video }}
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
  list-style: none;
  padding: 0 1em 0 0;
  display: flex;
  justify-content: space-between;
  gap: 1em;
  > * {
    flex: 1 1 auto;
  }
  .separator {
    background: #d0d7e3;
    flex: 0 0 1px;
  }
}
.youtube-url-input {
  width: 100%;
  max-width: 48em;
}
.video-preview {
  margin-top: 1em;
  .video-preview-actions {
    margin-top: 1em;
    text-align: end;
  }
}
</style>
