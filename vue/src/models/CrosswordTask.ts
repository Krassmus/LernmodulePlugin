import { z } from 'zod';

const wordSchema = z.object({
  uuid: z.string(),
  hint: z.string(),
  solution: z.string(),
  x: z.number(),
  y: z.number(),
  direction: z.string(),
});
export type Word = z.infer<typeof wordSchema>;

export const crosswordTaskSchema = z.object({
  task_type: z.literal('Crossword'),
  words: z.array(wordSchema),
  size: z.number().optional().default(12),
  strings: z.object({
    checkButton: z.string().default(''),
    retryButton: z.string(),
    solutionsButton: z.string(),
    resultMessage: z.string(),
  }),
});
export type CrosswordTask = z.infer<typeof crosswordTaskSchema>;
