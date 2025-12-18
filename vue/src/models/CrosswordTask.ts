import { z } from 'zod';

export const Direction = z.enum(['across', 'down']);

const wordSchema = z.object({
  uuid: z.string(),
  hint: z.string(),
  solution: z.string(),
  x: z.number(),
  y: z.number(),
  direction: Direction,
});
export type Word = z.infer<typeof wordSchema>;

export const crosswordTaskSchema = z.object({
  task_type: z.literal('Crossword'),
  words: z.array(wordSchema),
  strings: z.object({
    checkButton: z.string().default(''),
    retryButton: z.string(),
    solutionsButton: z.string(),
    resultMessage: z.string(),
  }),
});
export type CrosswordTask = z.infer<typeof crosswordTaskSchema>;
