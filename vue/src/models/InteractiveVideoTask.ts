// Types for the Interactive Video task type.
import { z } from 'zod';
import {
  iconForTaskType,
  printTaskType,
  taskDefinitionSchemaMinusInteractiveVideo,
} from '@/models/TaskDefinition';
import { $gettext } from '@/language/gettext';
import { wysiwygUploadedFileSchema } from '@/routes/lernmodule';

// There are different types of 'interaction' which can be added to the video.
// TODO Maybe put the 'base' attributes shared by all types of interaction all in one place.
// TODO I think it may be wiser to use 'startTime' and 'duration' rather than 'startTime' and 'endTime'.
// I chose to use 'start' and 'end' because it made the ui in one place easier to implement
// (v-model for 'end time' input field), but I have noticed that I must calculate a duration
// by hand in many other places anyway.

/**
 * @deprecated -- Text field renamed to content_wysiwyg to simplify implementation
 * of file export in PHP.
 */
const overlaySchema_v1 = z.object({
  type: z.literal('overlay'),
  id: z.string(),
  startTime: z.number(), // Seconds
  endTime: z.number(), // Seconds
  x: z.number(), // Position, as a fraction of video width, between 0 and 1
  y: z.number(), // Position, as a fraction of video height, between 0 and 1
  width: z.number(), // Width, as a fraction of video width, between 0 and 1
  height: z.number(), // Height, as a fraction of video width, between 0 and 1
  text: z.string(), // Sanitized HTML from Wysiwyg editor
  pauseWhenVisible: z.boolean().optional().default(true),
});
const overlaySchema_v2 = overlaySchema_v1.omit({ text: true }).extend({
  v: z.literal(2), // Schema version
  content_wysiwyg: z.string(), // Sanitized HTML from Wysiwyg editor
});
// Overlay: An overlay is shown on top of the video over a span of time
const overlaySchema = z
  .union([overlaySchema_v1, overlaySchema_v2])
  .transform((val): z.infer<typeof overlaySchema_v2> => {
    if (!Object.hasOwn(val, 'v')) {
      // Migration from v1 to v2
      const val_v1 = val as z.infer<typeof overlaySchema_v1>;
      const { text, ...val_v1_rest } = val_v1;
      const val_v2 = {
        v: 2,
        content_wysiwyg: text,
        ...val_v1_rest,
      } as z.infer<typeof overlaySchema_v2>;
      console.log(
        'Migrating from overlaySchema_v1 to overlaySchema_v2',
        'v1:',
        val_v1,
        'v2:',
        val_v2
      );
      return val_v2;
    } else {
      return val as z.infer<typeof overlaySchema_v2>;
    }
  });
export type OverlayInteraction = z.infer<typeof overlaySchema_v2>;

// LMB Task interaction: A Lernmodule Block Task (LMB Task) is shown at a given
// point in the video for the student to solve.
const lmbTaskInteractionSchema = z.object({
  type: z.literal('lmbTask'),
  id: z.string(),
  startTime: z.number(), // Seconds
  endTime: z.number(), // Seconds
  x: z.number(), // Position, as a fraction of video width, between 0 and 1
  y: z.number(), // Position, as a fraction of video height, between 0 and 1
  taskDefinition: z.lazy(() => taskDefinitionSchemaMinusInteractiveVideo),
  pauseWhenVisible: z.boolean().optional().default(true),
});
export type LmbTaskInteraction = z.infer<typeof lmbTaskInteractionSchema>;

const interactiveVideoInteractionSchema = z
  .union([overlaySchema, lmbTaskInteractionSchema])
  .refine((data) => data.endTime > data.startTime, {
    message: 'endTime cannot be earlier than startTime',
    path: ['endTime'],
  });
export type Interaction = z.infer<typeof interactiveVideoInteractionSchema>;

const noVideoSchema = z.object({
  type: z.literal('none'),
});
export type NoVideo = z.infer<typeof noVideoSchema>;
const youtubeVideoSchema = z.object({
  type: z.literal('youtube'),
  url: z.string(),
});
export type YoutubeVideo = z.infer<typeof youtubeVideoSchema>;

/**
 * @deprecated -- File is now stored as ID instead of url,name,type.
 */
const studipFileVideoSchema_v1 = z.object({
  type: z.literal('studipFileReference'),
  file: wysiwygUploadedFileSchema,
});
const studipFileVideoSchema_v2 = z.object({
  v: z.literal(2),
  type: z.literal('studipFileReference'),
  file_id: z.string(),
});
const studipFileVideoSchema = z
  .union([studipFileVideoSchema_v1, studipFileVideoSchema_v2])
  .transform((val) => {
    if (!Object.hasOwn(val, 'v')) {
      // Migration from v1 to v2
      const val_v1 = val as z.infer<typeof studipFileVideoSchema_v1>;
      const urlParams = new URLSearchParams(val_v1.file.url);
      const file_id = urlParams.get('file_id');
      if (!file_id) {
        throw new Error(
          'Could not migrate studip file reference schema v1 to v2. The query param ' +
            `"file_id" was not found in the string file.url: "${val_v1.file.url}"`
        );
      }
      const val_v2 = {
        v: 2,
        type: 'studipFileReference',
        file_id,
      } as z.infer<typeof studipFileVideoSchema_v2>;
      console.log(
        'Migrating from studipFileVideoSchema_v1 to studipFileVideoSchema_v2',
        'v1:',
        val_v1,
        'v2:',
        val_v2
      );
      return val_v2;
    } else {
      return val as z.infer<typeof studipFileVideoSchema_v2>;
    }
  });
export type StudipFileVideo = z.infer<typeof studipFileVideoSchema>;

const videoSchema = z.union([
  noVideoSchema,
  youtubeVideoSchema,
  studipFileVideoSchema,
]);
export type Video = z.infer<typeof videoSchema>;

export const travisGoSettingsSchema = z.object({
  enabled: z.boolean(),
  projectTitle: z.string(),
  projectDescription: z.string(),
});
export type TravisGoSettings = z.infer<typeof travisGoSettingsSchema>;

export const interactiveVideoTaskSchema = z.object({
  task_type: z.literal('InteractiveVideo'),
  video: videoSchema,
  autoplay: z.boolean().optional().default(false),
  startAt: z.number().optional().default(0),
  disableNavigation: z
    .union([
      z.literal('not disabled'),
      z.literal('forward disabled'),
      z.literal('forward and backward disabled'),
    ])
    .default('not disabled'),
  interactions: z.array(interactiveVideoInteractionSchema),
  travisGoSettings: travisGoSettingsSchema.optional().default(
    (): TravisGoSettings => ({
      enabled: false,
      projectTitle: '',
      projectDescription: '',
    })
  ),
});
export type InteractiveVideoTask = z.infer<typeof interactiveVideoTaskSchema>;

export function printInteractionType(interaction: Interaction): string {
  switch (interaction.type) {
    case 'lmbTask':
      return printTaskType(interaction.taskDefinition.task_type);
    case 'overlay':
      return $gettext('Einblendung');
  }
}

export function iconForInteraction(interaction: Interaction): string {
  switch (interaction.type) {
    case 'overlay':
      return 'item';
    case 'lmbTask':
      return iconForTaskType(interaction.taskDefinition.task_type);
  }
}

export const resizeHandles = [
  'left',
  'top-left',
  'top',
  'top-right',
  'right',
  'bottom-right',
  'bottom',
  'bottom-left',
] as const;
export type ResizeHandle = typeof resizeHandles[number];

const permissionsSchema = z.object({
  mayEdit: z.boolean(),
  mayDelete: z.boolean(),
});

const travisGoPostTypeSchema = z.union([
  z.literal('meta'),
  z.literal('image'),
  z.literal('audio'),
  z.literal('text'),
]);
export type TravisGoPostType = z.infer<typeof travisGoPostTypeSchema>;
const travisGoPostEditableKeys = {
  video_id: z.string(),
  video_type: z.enum(['lernmodule_module', 'cw_blocks']),
  start_time: z.coerce.number(),
  end_time: z.coerce.number().nullable(),
  contents: z.string(),
  post_type: travisGoPostTypeSchema,
};

export const travisGoPostJsonApiSchema = z.object({
  attributes: z.object({
    id: z.string(),
    mk_user_id: z.string(),
    mkdate: z.string().datetime({ offset: true }),
    chdate: z.string().datetime({ offset: true }),
    ...travisGoPostEditableKeys,
  }),
  meta: z.object({ permissions: permissionsSchema }),
});
export type TravisGoPostJsonApi = z.infer<typeof travisGoPostJsonApiSchema>;

export const createPostRequestSchema = z.object(travisGoPostEditableKeys);
export type CreatePostRequest = z.infer<typeof createPostRequestSchema>;

const travisGoCommentEditableKeys = {
  post_id: z.string(),
  contents: z.string(),
};

export const travisGoCommentJsonApiSchema = z.object({
  attributes: z.object({
    id: z.string(),
    mk_user_id: z.string(),
    mkdate: z.string().datetime({ offset: true }),
    chdate: z.string().datetime({ offset: true }),
    ...travisGoCommentEditableKeys,
  }),
  meta: z.object({ permissions: permissionsSchema }),
});
export type TravisGoCommentJsonApi = z.infer<
  typeof travisGoCommentJsonApiSchema
>;

export const createCommentRequestSchema = z.object(travisGoCommentEditableKeys);
export type CreateCommentRequest = z.infer<typeof createCommentRequestSchema>;
