import { createApp } from 'vue';
import LernmoduleViewer from '@/components/LernmoduleViewer.vue';
import { gettextPlugin } from '@/language/gettext';
import { disableDrag } from '@/directives/vDisableDrag';
import './assets/global.css';

const app = createApp(LernmoduleViewer);
app.directive('disable-drag', disableDrag);
app.use(gettextPlugin);
app.mount('#app');
