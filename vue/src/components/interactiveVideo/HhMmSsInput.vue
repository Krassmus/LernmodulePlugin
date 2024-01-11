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
    // Format a time in seconds as HH;MM;SS;SS (Centiseconds at end)
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
      while (time >= 1) {
        time -= 1;
        seconds += 1;
      }
      centiseconds = Math.floor(time * 100);
      function twoDigits(n: number): string {
        return n.toLocaleString('de-DE', {
          minimumIntegerDigits: 2,
          maximumFractionDigits: 0,
        });
      }
      let string = `${twoDigits(hours)};${twoDigits(minutes)};${twoDigits(
        seconds
      )};${twoDigits(centiseconds)}`;
      return string;
    },
  },
  computed: {
    /**
     * Either the time the user input, converted into seconds, or an Error with
     * a message explaining why the user's input was invalid.
     */
    parsedUserInput(): number | Error {
      const timeRegexCentiseconds = /^(\d?\d)[;:](\d?\d)[;:](\d?\d)[;:](\d?\d)/;
      const centisecondsMatch = timeRegexCentiseconds.exec(this.userInput);
      if (centisecondsMatch) {
        return (
          parseInt(centisecondsMatch[1]) * 3600 +
          parseInt(centisecondsMatch[2]) * 60 +
          parseInt(centisecondsMatch[3]) +
          parseInt(centisecondsMatch[4]) / 100
        );
      }
      // If the exact format for a time with centiseconds isn't matched, use
      // a more permissive regex.
      // This regex is adapted from https://stackoverflow.com/a/8318367/7359454
      // License CC BY-SA 3.0 https://creativecommons.org/licenses/by-sa/3.0/
      // Accessed and adapted on 10.01.2024 by Ann Yanich
      const timeRegexHhMmSs =
        /^(?:(?:([0-9]?\d)[;:])?([0-5]?\d)[:;])?([0-5]?\d)$/;
      const hhMmSsMatch = timeRegexHhMmSs.exec(this.userInput);
      if (!hhMmSsMatch || hhMmSsMatch.length !== 4) {
        return new Error(
          $gettext(
            'Die Startposition muss der Syntax HH;MM;SS oder HH;MM;SS;SS entsprechen.'
          )
        );
      }
      // match[0] is the time string with colons,
      // match[1] is hours (or undefined if not present),
      // match[2] is minutes (or undefined if not present),
      // and match[3] is seconds (always present).
      if (hhMmSsMatch[1]) {
        return (
          parseInt(hhMmSsMatch[1]) * 3600 +
          parseInt(hhMmSsMatch[2]) * 60 +
          parseInt(hhMmSsMatch[3])
        );
      } else if (hhMmSsMatch[2]) {
        return parseInt(hhMmSsMatch[2]) * 60 + parseInt(hhMmSsMatch[3]);
      } else {
        return parseInt(hhMmSsMatch[3]);
      }
    },
  },
});
</script>

<template>
  <input type="text" v-model="userInput" @blur="onBlurUserInput" />
</template>

<style scoped></style>
