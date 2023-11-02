// Types for the Interactive Video task type.
import { z } from 'zod';
import { TaskDefinition, taskDefinitionSchema } from '@/models/TaskDefinition';

// There are different types of 'interaction' which can be added to the video.

// Pause interaction: Video pauses at a specific point in time
const pauseSchema = z.object({
  type: z.literal('pause'),
  id: z.string(),
  startTime: z.number(), // Seconds
  text: z.string(), // Sanitized HTML from Wysiwyg editor
});

// Overlay: An overlay is shown on top of the video over a span of time
const overlaySchema = z.object({
  type: z.literal('overlay'),
  id: z.string(),
  startTime: z.number(), // Seconds
  endTime: z.number(), // Seconds
  text: z.string(), // Sanitized HTML from Wysiwyg editor
});

// LMB Task interaction: A Lernmodule Block Task (LMB Task) is shown at a given
// point in the video for the student to solve.
// The code needed to express this type in zod is unfortunately a little bit
// cumbersome, because it contains a recursive dependency on the 'TaskDefinition'
// schema. This example is adapted from the documentation of zod:
// https://github.com/colinhacks/zod#recursive-types
const baseLmbTaskSchema = z.object({
  type: z.literal('lmbTask'),
  id: z.string(),
  startTime: z.number(), // Seconds
  endTime: z.number(), // Seconds
});
type LmbTask = z.infer<typeof baseLmbTaskSchema> & {
  taskDefinition: TaskDefinition;
};
const lmbTaskSchema: z.ZodType<LmbTask> = baseLmbTaskSchema.extend({
  taskDefinition: z.lazy(() => taskDefinitionSchema),
});
const interactiveVideoInteractionSchema = z
  .union([pauseSchema, overlaySchema, lmbTaskSchema])
  .refine((data) => data.type === 'pause' || data.endTime > data.startTime, {
    message: 'endTime cannot be earlier than startTime',
    path: ['endTime'],
  });

export const interactiveVideoTaskSchema = z.object({
  task_type: z.literal('InteractiveVideo'),
  video: z.union([
    z.object({
      type: z.literal('none'),
    }),
    z.object({
      type: z.literal('youtube'),
      url: z.string(),
    }),
    z.object({
      type: z.literal('studipFileReference'),
      // TODO check what data needs to be saved for this feature to be implemented
    }),
  ]),
  interactions: z.array(interactiveVideoInteractionSchema),
});
export type Interaction = z.infer<typeof interactiveVideoInteractionSchema>;
export type InteractiveVideoTask = z.infer<typeof interactiveVideoTaskSchema>;
