import { createStore } from 'vuex';
import { getModule } from 'vuex-module-decorators';
import { TaskEditorModule } from '@/store/modules/taskEditor';
import { CoursewareBlockModule } from '@/store/modules/coursewareBlock';
import axios from 'axios';
const reststateVuex = require('@elan-ev/reststate-vuex');
const mapResourceModules = reststateVuex.mapResourceModules;

const httpClient = axios.create({
  baseURL: window.STUDIP.URLHelper.getURL(`jsonapi.php/v1`, {}, true),
  headers: {
    'Content-Type': 'application/vnd.api+json',
  },
});
export const store = createStore({
  modules: {
    taskEditor: TaskEditorModule,
    coursewareBlock: CoursewareBlockModule,
    ...mapResourceModules({
      names: ['folders'],
      httpClient,
    }),
  },
});
export const taskEditorStore = getModule(TaskEditorModule, store);
export const coursewareBlockStore = getModule(CoursewareBlockModule, store);
