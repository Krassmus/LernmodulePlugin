import { createApp } from 'vue';
import LernmoduleEditor from '@/components/LernmoduleEditor.vue';

createApp(LernmoduleEditor).mount('#app');

declare global {
  interface Window {
    STUDIP: {
      ABSOLUTE_URI_STUDIP: string;
      ASSETS_URL: string;
      CSRF_TOKEN: { name: string; value: string };
      H5P: {
        saveRoute: string;
      };
    };
  }
}
