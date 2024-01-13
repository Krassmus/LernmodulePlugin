<script lang="ts">
import { defineComponent, inject, PropType } from 'vue';
import {
  EditorState,
  editorStateSymbol,
} from '@/components/interactiveVideo/editorState';
import { Interaction, LmbTaskInteraction } from '@/models/InteractiveVideoTask';
import { iconForTaskType, printTaskType } from '../../../models/TaskDefinition';

export default defineComponent({
  name: 'LmbTaskInteraction',
  methods: { iconForTaskType, printTaskType },
  setup() {
    return {
      editor: inject<EditorState>(editorStateSymbol),
    };
  },
  props: {
    interaction: {
      type: Object as PropType<LmbTaskInteraction>,
      required: true,
    },
  },
});
</script>

<template>
  <button
    type="button"
    class="lmb-task-interaction"
    :class="{
      selected: editor?.selectedInteractionId.value === interaction.id,
    }"
    @click="!this.editor && $emit('activateInteraction', interaction)"
  >
    <div class="interaction-label">
      {{ printTaskType(interaction.taskDefinition.task_type) }}
    </div>
    <div
      class="icon-circle button"
      :class="iconForTaskType(interaction.taskDefinition.task_type)"
    ></div>
  </button>
  <!--  <div-->
  <!--    v-if="editor"-->
  <!--    :class="{-->
  <!--      selected: editor?.selectedInteractionId.value === interaction.id,-->
  <!--    }"-->
  <!--  >-->
  <!--    {{ printTaskType(interaction.taskDefinition.task_type) }}-->
  <!--  </div>-->
  <!--  <button-->
  <!--    v-else-->
  <!--    type="button"-->
  <!--    @click="$emit('activateInteraction', interaction)"-->
  <!--  >-->
  <!--    {{ printTaskType(interaction.taskDefinition.task_type) }}-->
  <!--  </button>-->
</template>

<style scoped lang="scss">
button.lmb-task-interaction {
  /* CSS Reset for button styles */
  padding: 0;
  border: none;
  font: inherit;
  color: inherit;
  background-color: transparent;
  cursor: pointer;
  box-sizing: border-box;

  &.selected {
    > .interaction-label {
      border: 2px solid #3c434e;
      box-sizing: border-box;
    }
  }

  > .icon-circle {
    background: #c3abd1;
    shape-outside: circle();
    clip-path: circle();
    width: 3em;
    height: 3em;
    &::before {
      transform: translate(1.5em, 0.9em) scale(1.3);
      filter: grayscale(1) contrast(1.5);
    }
  }
  > .interaction-label {
    display: flex;
    align-items: center;
    position: absolute;
    left: 1.5em;
    padding-left: 1.7em;
    padding-right: 0.5em;
    top: 0.1em;
    white-space: nowrap;
    height: 2.8em;
    background: #e2e3e4;
  }
}
</style>
