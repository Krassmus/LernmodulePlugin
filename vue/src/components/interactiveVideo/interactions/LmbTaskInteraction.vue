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
      editor: !!editor,
    }"
    :data-hover-tooltip="printTaskType(interaction.taskDefinition.task_type)"
    @click="!this.editor && $emit('activateInteraction', interaction)"
  >
    <div class="interaction-label">
      {{ printTaskType(interaction.taskDefinition.task_type) }}
    </div>
    <div class="icon-circle-border"></div>
    <div
      class="icon-circle button no-hover-effect"
      :class="iconForTaskType(interaction.taskDefinition.task_type)"
    ></div>
  </button>
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

  $border-width: 0.2em;
  $circle-radius: 3em;

  > .icon-circle {
    position: absolute;
    top: $border-width;
    left: $border-width;
    background: #c3abd1;
    shape-outside: circle();
    clip-path: circle();
    width: $circle-radius;
    height: $circle-radius;
    /* Position the icon centered inside the circle */
    &::before {
      transform: translate(
          calc($circle-radius / 2),
          calc($circle-radius / 2 - 0.6em)
        )
        scale(1.3);
      filter: grayscale(1) contrast(1.5);
    }
  }
  &:hover > .icon-circle {
    background: #e1d5e8;
  }

  > .interaction-label {
    display: flex;
    align-items: center;
    position: absolute;
    left: calc($circle-radius / 2);
    padding-left: calc($circle-radius / 2 + 0.5em + $border-width);
    padding-right: 0.5em;
    top: calc(0.1em + $border-width);
    white-space: nowrap;
    height: calc($circle-radius - 0.2em);
    background: #e2e3e4;
    border-radius: 12px;
  }

  &.selected {
    z-index: 1;
    > .interaction-label {
      border: $border-width solid #3c434e;
      margin-top: calc(-1 * $border-width);
      margin-left: calc(-1 * $border-width);
    }
    .icon-circle-border {
      shape-outside: circle();
      clip-path: circle();
      width: calc($circle-radius + 2 * $border-width);
      height: calc($circle-radius + 2 * $border-width);
      background: #3c434e;
    }
  }

  /* This provides a bigger, visible focus marker when the user tabs between
   interactions using the keyboard or assistive device */
  &:focus:not(.selected) {
    > .icon-circle-border {
      shape-outside: circle();
      clip-path: circle();
      width: calc($circle-radius + 2 * $border-width);
      height: calc($circle-radius + 2 * $border-width);
      background: transparent;
    }
  }

  &.editor:not(.selected) {
    &:hover::before {
      content: attr(data-hover-tooltip);
      display: flex;
      align-items: center;
      white-space: nowrap;
      position: absolute;
      top: -2em;
      height: 1em;
      /* Center the tooltip over the circle with the icon */
      transform: translateX(calc(-50% + $circle-radius / 2));
      padding: 0.35em;
      border-radius: 12px;
      background: black;
      color: white;
      opacity: 0.9;
      z-index: 2;
    }
  }
}
</style>
