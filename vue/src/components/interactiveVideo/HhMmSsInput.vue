<script lang="ts">
import { defineComponent } from 'vue';
import { $gettext } from '@/language/gettext';

export default defineComponent({
  name: 'HhMmSsInput',
  props: {
    modelValue: {
      type: Number,
      required: true,
    },
    showCentiseconds: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  data() {
    return {
      userInput: '',
    };
  },
  watch: {
    modelValue: {
      immediate: true,
      handler(newValue: number) {
        this.userInput = this.formatSeconds(newValue);
      },
    },
    parsedUserInput() {
      if (this.parsedUserInput instanceof Error) {
        this.$emit('update:error', this.parsedUserInput);
      } else {
        this.$emit('update:error', undefined);
      }
    },
  },
  methods: {
    onBlurUserInput() {
      if (this.parsedUserInput instanceof Error) {
        // Reset the input to a good value
        this.userInput = this.formatSeconds(this.modelValue);
        this.$emit('update:error', undefined);
      } else {
        this.$emit('update:modelValue', this.parsedUserInput);
        this.$emit('update:error', undefined);
      }
    },
    // Format a time in seconds as HH:MM:SS (and optional milliseconds)
    formatSeconds(time: number): string {
      let hours = 0,
        minutes = 0,
        seconds = 0,
        centiseconds = 0;
      while (time >= 3600) {
        time -= 3600;
        hours += 1;
      }
      while (time >= 60) {
        time -= 60;
        minutes += 1;
      }
      if (this.showCentiseconds) {
        while (time >= 1) {
          time -= 1;
          seconds += 1;
        }
        centiseconds = Math.floor(time * 100);
      } else {
        seconds = time;
      }
      function twoDigits(n: number): string {
        return n.toLocaleString('de-DE', {
          minimumIntegerDigits: 2,
          maximumFractionDigits: 0,
        });
      }
      let string = `${twoDigits(hours)}:${twoDigits(minutes)}:${twoDigits(
        seconds
      )}`;
      if (this.showCentiseconds) {
        string = `${string}:${twoDigits(centiseconds)}`;
      }
      return string;
    },
  },
  computed: {
    /**
     * Either the time the user input, converted into seconds, or an Error with
     * a message explaining why the user's input was invalid.
     */
    parsedUserInput(): number | Error {
      // This regex is adapted from https://stackoverflow.com/a/8318367/7359454
      // License CC BY-SA 3.0 https://creativecommons.org/licenses/by-sa/3.0/
      // Accessed and adapted on 10.01.2024 by Ann Yanich
      const timeRegex = /^(?:(?:([0-9]?\d):)?([0-5]?\d):)?([0-5]?\d)$/;
      const match = timeRegex.exec(this.userInput);
      if (!match || match.length !== 4) {
        return new Error(
          $gettext('Die Startposition muss der Syntax HH:MM:SS entsprechen.')
        );
      }
      // match[0] is the time string with colons,
      // match[1] is hours (or undefined if not present),
      // match[2] is minutes (or undefined if not present),
      // and match[3] is seconds (always present).
      if (match[1]) {
        return (
          parseInt(match[1]) * 3600 +
          parseInt(match[2]) * 60 +
          parseInt(match[3])
        );
      } else if (match[2]) {
        return parseInt(match[2]) * 60 + parseInt(match[3]);
      } else {
        return parseInt(match[3]);
      }
    },
  },
});
</script>

<template>
  <input type="text" v-model="userInput" @blur="onBlurUserInput" />
</template>

<style scoped></style>
