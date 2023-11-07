<!-- Allow us to mutate the prop 'taskDefinition' as much as we want-->
<!-- eslint-disable vue/no-mutating-props -->
<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { InteractiveVideoTask } from '@/models/InteractiveVideoTask';
import { $gettext } from '@/language/gettext';
import TabsComponent from '@/components/interactiveVideo/TabsComponent.vue';
import TabComponent from '@/components/interactiveVideo/TabComponent.vue';
import VideoPlayer from '@/components/interactiveVideo/VideoPlayer.vue';
import AddInteractions from '@/components/interactiveVideo/AddInteractions.vue';

export default defineComponent({
  name: 'InteractiveVideoEditor',
  data() {
    return {
      youtubeUrlInput: '',
    };
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
  props: {
    taskDefinition: {
      type: Object as PropType<InteractiveVideoTask>,
      required: true,
    },
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
  computed: {},
  components: {
    AddInteractions,
    VideoPlayer,
    TabComponent,
    TabsComponent,
  },
});
</script>

<template>
  <TabsComponent>
    <TabComponent :title="$gettext('1. Video auswählen')" icon="video2">
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
      <div v-else>
        Stud.IP video. (Not implemented.) {{ taskDefinition.video }}
      </div>
    </TabComponent>
    <TabComponent
      :title="$gettext('2. Interaktionen hinzufügen')"
      icon="content"
    >
      <AddInteractions :task-definition="taskDefinition" />
    </TabComponent>
  </TabsComponent>
  <div style="display: none">
    Task definition:
    <pre>{{ taskDefinition }}</pre>
  </div>
</template>

<style scoped></style>
