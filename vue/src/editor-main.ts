import { createApp } from 'vue';
import LernmoduleEditor from '@/components/LernmoduleEditor.vue';
import { taskEditorStore } from '@/store';

taskEditorStore.initialize();
createApp(LernmoduleEditor).mount('#app');
