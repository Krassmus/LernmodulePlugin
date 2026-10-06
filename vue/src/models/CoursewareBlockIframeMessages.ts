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
    /**
     * This field should be an instance of taskDefinitionSchema,
     * but we would prefer to handle that validation in its own special place
     * separate from the parsing of iframe messages. So, we don't specify
     * what data type it should have here, allowing us to defer the parsing
     * to a later step.
     */
    task_json: z.unknown(),
  }),
]);

const contextSchema = z.object({
  id: z.string(),
  type: z.string(),
});
export type Context = z.infer<typeof contextSchema>;

// Contains data which should be used to initialize the store for the Courseware block
const initializeCoursewareBlockMessageSchema = z.object({
  type: z.literal('InitializeCoursewareBlock'),
  block: z.object({
    id: z.string(),
    attributes: z.object({
      payload: coursewareBlockPayloadSchema,
    }),
  }),
  canEdit: z.boolean(),
  isTeacher: z.boolean(),
  context: contextSchema,
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
