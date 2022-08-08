import { createApp } from 'vue';
import LernmoduleViewer from '@/components/LernmoduleViewer.vue';
import { gettextPlugin } from '@/language/gettext';

const app = createApp(LernmoduleViewer);
app.use(gettextPlugin);
app.mount('#app');
