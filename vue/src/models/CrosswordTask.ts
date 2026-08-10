import { z } from 'zod';
import { v4 } from 'uuid';

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
  colorEmptyCells: z.boolean(),
  strings: z.object({
    checkButton: z.string(),
    retryButton: z.string(),
    solutionsButton: z.string(),
    resultMessage: z.string(),
  }),
});
export type CrosswordTask = z.infer<typeof crosswordTaskSchema>;
export function newCrosswordTask(): CrosswordTask {
  return {
    task_type: 'Crossword',
    words: [
      {
        uuid: v4(),
        hint: 'Frucht mit gleichnamiger Farbe.',
        solution: 'Orange',
        x: 4,
        y: 1,
        direction: 'across',
      },
      {
        uuid: v4(),
        hint: 'Krummes Obst mit gelber Schale.',
        solution: 'Banane',
        x: 6,
        y: 0,
        direction: 'down',
      },
      {
        uuid: v4(),
        hint: 'Kleine rote Steinfrucht mit Stiel.',
        solution: 'Kirsche',
        x: 0,
        y: 5,
        direction: 'across',
      },
      {
        uuid: v4(),
        hint: 'Unsinn / Milchprodukt.',
        solution: 'Quark',
        x: 4,
        y: 3,
        direction: 'across',
      },
      {
        uuid: v4(),
        hint: 'Erworben.',
        solution: 'Gekauft',
        x: 8,
        y: 1,
        direction: 'down',
      },
    ],
    colorEmptyCells: false,
    strings: {
      checkButton: 'Überprüfen',
      retryButton: 'Erneut versuchen',
      solutionsButton: 'Lösungen anzeigen',
      resultMessage: ':correct von :total Felder richtig ausgefüllt.',
    },
  };
}
