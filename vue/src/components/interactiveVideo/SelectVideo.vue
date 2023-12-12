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
  {{
    $gettext(
      'Lade ein Video hoch oder füge einen Link zu einem Youtube-Video ein.'
    )
  }}
  <div>
    <!--        <label>-->
    <!--          {{ $gettext('Video hochladen') }}-->
    <!--          <input type="file" @change="onPickVideoFile" />-->
    <!--        </label>-->
    <label>
      {{ $gettext('Youtube-URL verwenden') }}
      <input type="text" v-model="youtubeUrlInput" />
    </label>
    <button @click="onSaveYoutubeVideo">Speichern</button>
  </div>
  <div v-if="taskDefinition.video.type === 'none'">
    {{ $gettext('Kein Video ausgewählt') }}
  </div>
  <div v-else-if="taskDefinition.video.type === 'youtube'">
    <div>
      <button @click="deleteVideo">{{ $gettext('Video löschen') }}</button>
    </div>
    <VideoPlayer :task="taskDefinition" />
  </div>
  <div v-else>Stud.IP video. (Not implemented.) {{ taskDefinition.video }}</div>
</template>

<style scoped></style>
