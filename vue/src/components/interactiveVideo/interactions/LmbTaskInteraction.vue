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
    @click="$emit('activateInteraction', interaction)"
  >
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
    border: 4px solid blue;
  }

  > .icon-circle {
    background: #c3abd1;
    shape-outside: circle();
    clip-path: circle();
    width: 3em;
    height: 3em;
  }
}
</style>
