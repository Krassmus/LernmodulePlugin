<template>
  <div class="h5pModule">
    <div>
      <span class="h5pQuestion">
        {{ this.task.question }}
      </span>

      <span v-for="answer in task.answers" :key="answer.text">
        {{ answer.text }}
      </span>

      <button @click="onClickCheck" v-if="showCheckButton" class="h5pButton">
        {{ this.task.strings.checkButton }}
      </button>

      <div v-if="showExtraButtons">
        <button
          v-if="!showSolutions && this.task.showSolutionsAllowed"
          @click="onClickShowSolution"
          class="h5pButton"
        >
          {{ this.task.strings.solutionsButton }}
        </button>

        <button
          v-if="this.task.retryAllowed"
          @click="onClickTryAgain"
          class="h5pButton"
        >
          {{ this.task.strings.retryButton }}
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  FlashCardTaskDefinition,
  MultipleChoiceTaskDefinition,
} from '@/models/TaskDefinition';
import { v4 as uuidv4 } from 'uuid';
import { isEqual } from 'lodash';

export default defineComponent({
  name: 'MultipleChoiceViewer',
  props: {
    task: {
      type: Object as PropType<MultipleChoiceTaskDefinition>,
      required: true,
    },
  },
  computed: {},
});
</script>

<style scoped></style>
