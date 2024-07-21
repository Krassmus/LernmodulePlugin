<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { AudioElement, fileIdToUrl } from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'AudioElement',
  props: {
    element: {
      type: Object as PropType<AudioElement>,
      required: true,
    },
  },
  data() {
    return {
      durationSeconds: 0,
      currentSeconds: 0,
      playing: false,
    };
  },
  computed: {
    audioUrl(): string {
      return fileIdToUrl(this.element.file_id);
    },
    currentTime() {
      return this.seconds2time(this.currentSeconds);
    },
    durationTime() {
      if (this.durationSeconds > 0) {
        return this.seconds2time(this.durationSeconds);
      }
      return false;
    },
  },
  methods: {
    $gettext,
    onPlayListener() {
      this.playing = true;
    },
    onPauseListener() {
      this.playing = false;
    },
    onClickPlayListener() {
      console.log('onclick play');
      (this.$refs.audio as HTMLAudioElement).play();
    },
    onClickPauseListener() {
      console.log('onclick Pause');
      (this.$refs.audio as HTMLAudioElement).pause();
    },
    onClickStopListener() {
      console.log('onclick stop');
      (this.$refs.audio as HTMLAudioElement).pause();
      (this.$refs.audio as HTMLAudioElement).currentTime = 0;
    },
    onTimeUpdateListener() {
      this.currentSeconds = (this.$refs.audio as HTMLAudioElement).currentTime;
    },
    setDuration() {
      let duration = (this.$refs.audio as HTMLAudioElement).duration;
      if (!isNaN(duration) && isFinite(duration)) {
        this.durationSeconds = duration;
      } else {
        this.durationSeconds = 0;
      }
    },
    onEndedListener() {
      console.log('ended');
      this.playing = false;
    },
    onInputRange() {
      (this.$refs.audio as HTMLAudioElement).currentTime = Number.parseFloat(
        (this.$refs.range as HTMLInputElement).value
      );
    },
    seconds2time(seconds: number) {
      seconds = Math.round(seconds);
      let hours = Math.floor(seconds / 3600);
      let minutes = Math.floor((seconds - hours * 3600) / 60);
      let time = '';
      seconds = seconds - hours * 3600 - minutes * 60;
      if (hours !== 0) {
        time = hours + ':';
      }
      if (minutes !== 0 || time !== '') {
        const minutesString =
          minutes < 10 && time !== '' ? '0' + minutes : String(minutes);
        time += minutesString + ':';
      }
      if (time === '') {
        time = seconds < 10 ? '0:0' + seconds : '0:' + seconds;
      } else {
        time += seconds < 10 ? '0' + seconds : String(seconds);
      }
      return time;
    },
  },
});
</script>

<template>
  <div class="lmb-audio-element">
    <audio
      :src="audioUrl"
      class="lmb-audio-player"
      ref="audio"
      @play="onPlayListener"
      @pause="onPauseListener"
      @timeupdate="onTimeUpdateListener"
      @durationchange="setDuration"
      @ended="onEndedListener"
    />
    <input
      class="cw-audio-range"
      ref="range"
      type="range"
      :value="currentSeconds"
      min="0"
      :max="Math.round(durationSeconds)"
      @input="onInputRange"
    />
    <div class="cw-audio-controls">
      <button
        v-if="!playing"
        class="cw-audio-button cw-audio-playbutton"
        :title="$gettext('Abspielen')"
        @click="onClickPlayListener"
      />
      <button
        v-if="playing"
        class="cw-audio-button cw-audio-pausebutton"
        :title="$gettext('Pause')"
        @click="onClickPauseListener"
      />
      <button
        class="cw-audio-button cw-audio-stopbutton"
        :title="$gettext('Anhalten')"
        @click="onClickStopListener"
      />
    </div>
    <span class="cw-audio-time"
      >{{ currentTime }} {{ durationTime ? '/ ' + durationTime : '' }}</span
    >
  </div>
</template>

<style scoped>
.cw-audio-range {
  margin: 0 5px 10px 0;
  &::-moz-focus-outer {
    border: 0;
  }
  &.ui-widget-content {
    background-color: $base-color;
  }
  .ui-widget-header {
    background-color: $dark-gray-color-5;
  }
  .ui-slider-handle {
    border-radius: 20px;
    width: 1em;
    height: 1.7em;
    top: -0.5em;
    background-color: $dark-gray-color-20;
    border-color: $content-color-40;
    cursor: pointer;
    margin-left: -2px;
  }
}
.cw-audio-button {
  border: solid thin $content-color-40;
  background-color: $white;
  background-repeat: no-repeat;
  background-position: center center;
  background-size: 24px;
  min-height: 27px;
  line-height: 130%;
  padding: 5px 15px 5px 30px;
  cursor: pointer;
  font-size: 14px;
  box-sizing: border-box;
  text-align: center;
  text-decoration: none;
  vertical-align: bottom;
  white-space: nowrap;
  min-width: unset;
  margin: 5px;
  height: 46px;
  width: 46px;
  display: inline-block;

  &:hover {
    background-color: $base-color;
  }
}

.cw-audio-time {
  position: relative;
  top: -1em;
  color: $base-gray;
}
</style>
