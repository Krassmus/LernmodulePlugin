import { createApp } from 'vue';
import LernmoduleViewer from '@/components/LernmoduleViewer.vue';
import { gettextPlugin } from '@/language/gettext';
import './assets/global.css';

const app = createApp(LernmoduleViewer);
app.use(gettextPlugin);
app.mount('#app');
