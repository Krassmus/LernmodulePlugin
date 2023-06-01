import { z } from 'zod';

// Messages sent by webpack during development.  We can ignore them
const webpackMessageSchema = z.object({
  type: z.union([
    z.literal('webpackProgress'),
    z.literal('webpackOk'),
    z.literal('webpackClose'),
    z.literal('webpackInvalid'),
  ]),
});

// Messages sent by the iFrameSizer library.  We can ignore them
const iFrameSizerMessageSchema = z.string().startsWith('[iFrameSizer]');

// Indicates that the edit UI should be shown or hidden
const showEditChangeMessageSchema = z.object({
  type: z.literal('ShowEditChange'),
  state: z.boolean(),
});

// Contains data which should be used to initialize the store for the Courseware block
const initializeCoursewareBlockMessageSchema = z.object({
  type: z.literal('InitializeCoursewareBlock'),
  block: z.object({
    attributes: z.object({
      payload: z.object({
        initialized: z.boolean(),
        task_json: z.unknown(),
      }),
    }),
  }),
  canEdit: z.boolean(),
  isTeacher: z.boolean(),
});
// Contains data which should be used to initialize the store for the Courseware block
export type InitializeMessage = z.infer<
  typeof initializeCoursewareBlockMessageSchema
>;

// Messages which may be posted to the iframe in which the Vue 3 CoursewareBlock
// component is embedded
export const iframeMessageSchema = z.union([
  initializeCoursewareBlockMessageSchema,
  showEditChangeMessageSchema,
  webpackMessageSchema,
  iFrameSizerMessageSchema,
]);
