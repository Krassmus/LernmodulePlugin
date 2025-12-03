<script setup lang="ts">
import { defineProps, PropType, ref, defineEmits, watch } from 'vue';
import { TravisGoSettings } from '@/models/InteractiveVideoTask';
import { cloneDeep } from 'lodash';

const emit = defineEmits({
  update(payload: { settings: TravisGoSettings; undoBatch?: unknown }) {
    return true;
  },
});

const props = defineProps({
  settings: {
    type: Object as PropType<TravisGoSettings>,
    required: true,
  },
});

const modelSettings = ref<TravisGoSettings>(cloneDeep(props.settings));
watch(
  () => props.settings,
  (settings) => {
    modelSettings.value = cloneDeep(settings);
  },
  { deep: true }
);

function updateSettings(undoBatch: unknown = {}) {
  emit('update', {
    settings: modelSettings.value,
    undoBatch,
  });
}
</script>

<template>
  <label>
    <input type="checkbox" v-model="modelSettings.enabled" />
    {{ $gettext('"Travis Go"-Funktionalität enablen') }}
  </label>
  <template v-if="modelSettings.enabled">
    <label>
      {{ $gettext('Projekttitel') }}
      <input
        type="text"
        v-model="modelSettings.projectTitle"
        @input="updateSettings('editProjectTitle')"
      />
    </label>
    <label>
      {{ $gettext('Projektbeschreibung') }}
      <input
        type="text"
        v-model="modelSettings.projectDescription"
        @input="updateSettings('editProjectDescription')"
      />
    </label>
  </template>
</template>

<style scoped lang="scss"></style>
