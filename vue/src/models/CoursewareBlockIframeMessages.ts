import { z } from 'zod';
import { taskDefinitionSchema, taskTypeSchema } from '@/models/TaskDefinition';

// Messages sent by webpack during development.  We can ignore them
const webpackMessageSchema = z.union([
  z.object({
    type: z.union([
      z.literal('webpackProgress'),
      z.literal('webpackOk'),
      z.literal('webpackClose'),
      z.literal('webpackInvalid'),
      z.literal('webpackErrors'),
      z.literal('webpackStillOk'),
    ]),
  }),
  z.string().startsWith('webpackHotUpdate'), // e.g. 'webpackHotUpdate324efae9d340c78'
]);

// Messages sent by the iFrameSizer library.  We can ignore them
const iFrameSizerMessageSchema = z.string().startsWith('[iFrameSizer]');

// Indicates that the edit UI should be shown or hidden
const showEditChangeMessageSchema = z.object({
  type: z.literal('ShowEditChange'),
  state: z.boolean(),
});

// This corresponds to the JSON Schema schema defined in LernmoduleBlock.json.
const coursewareBlockPayloadSchema = z.union([
  z.object({
    initialized: z.literal(false),
    task_type: taskTypeSchema,
  }),
  z.object({
    initialized: z.literal(true),
    task_json: taskDefinitionSchema,
  }),
]);

// Contains data which should be used to initialize the store for the Courseware block
const initializeCoursewareBlockMessageSchema = z.object({
  type: z.literal('InitializeCoursewareBlock'),
  block: z.object({
    attributes: z.object({
      payload: coursewareBlockPayloadSchema,
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
