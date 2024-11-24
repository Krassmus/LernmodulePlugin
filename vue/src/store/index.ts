import { createStore } from 'vuex';
import { getModule } from 'vuex-module-decorators';
import { TaskEditorModule } from '@/store/modules/taskEditor';
import { CoursewareBlockModule } from '@/store/modules/coursewareBlock';
import { RequestQueueModule } from '@/store/modules/requestQueue';
import { httpClient } from '@/routes/jsonApi';
const reststateVuex = require('@elan-ev/reststate-vuex');
const mapResourceModules = reststateVuex.mapResourceModules;

export const store = createStore({
  modules: {
    taskEditor: TaskEditorModule,
    coursewareBlock: CoursewareBlockModule,
    requestQueue: RequestQueueModule,
    // Dynamically generated store modules used to access Stud.IP's JSON API
    ...mapResourceModules({
      names: [
        'courses',
        'course-memberships',
        'courseware-blocks',
        'courseware-block-comments',
        'courseware-block-feedback',
        'courseware-clipboards',
        'courseware-containers',
        'courseware-instances',
        'courseware-public-links',
        'courseware-structural-elements',
        'courseware-structural-element-comments',
        'courseware-structural-element-feedback',
        'courseware-task-feedback',
        'courseware-task-groups',
        'courseware-tasks',
        'courseware-templates',
        'courseware-user-data-fields',
        'courseware-user-progresses',
        'courseware-units',
        'files',
        'file-refs',
        'folders',
        'lti-tools',
        'status-groups',
        'users',
        'institutes',
        'institute-memberships',
        'semesters',
        'sem-classes',
        'sem-types',
        'terms-of-use',
        'user-data-field',
        'studip-properties',
      ],
      httpClient,
    }),
  },
});
// These variables have type information attached to them, thanks to
// the library vuex-module-decorators.
export const taskEditorStore = getModule(TaskEditorModule, store);
export const coursewareBlockStore = getModule(CoursewareBlockModule, store);
export const requestQueueStore = getModule(RequestQueueModule, store);
