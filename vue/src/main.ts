import { createApp } from 'vue';
import App from './App.vue';

createApp(App).mount('#app');

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
