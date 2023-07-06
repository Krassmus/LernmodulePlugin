import FillInTheBlanksViewer from '@/components/FillInTheBlanksViewer.vue';
import FillInTheBlanksEditor from '@/components/FillInTheBlanksEditor.vue';
import FlashCardsViewer from '@/components/FlashCardsViewer.vue';
import FlashCardsEditor from '@/components/FlashCardsEditor.vue';
import QuestionEditor from '@/components/QuestionEditor.vue';
import QuestionViewer from '@/components/QuestionViewer.vue';
import DragTheWordsViewer from '@/components/DragTheWordsViewer.vue';
import DragTheWordsEditor from '@/components/DragTheWordsEditor.vue';
import MarkTheWordsViewer from '@/components/MarkTheWordsViewer.vue';
import MarkTheWordsEditor from '@/components/MarkTheWordsEditor.vue';
import MemoryEditor from '@/components/MemoryEditor.vue';
import MemoryViewer from '@/components/MemoryViewer.vue';
import ImagePairingViewer from '@/components/ImagePairingViewer.vue';
import ImagePairingEditor from '@/components/ImagePairingEditor.vue';
import { v4 } from 'uuid';
import { z } from 'zod';

export const feedbackSchema = z.object({
  percentage: z.number(),
  message: z.string(),
});
export type Feedback = z.infer<typeof feedbackSchema>;

export const dragTheWordsTaskSchema = z.object({
  task_type: z.literal('DragTheWords'),
  template: z.string(),
  retryAllowed: z.boolean(),
  showSolutionsAllowed: z.boolean(),
  instantFeedback: z.boolean(),
  allBlanksMustBeFilledForSolutions: z.boolean(),
  alphabeticOrder: z.boolean(),
  strings: z.object({
    checkButton: z.string(),
    retryButton: z.string(),
    solutionsButton: z.string(),
    fillInAllBlanksMessage: z.string(),
    resultMessage: z.string(),
  }),
  feedback: z.array(feedbackSchema),
});
export type DragTheWordsTask = z.infer<typeof dragTheWordsTaskSchema>;

export const markTheWordsTaskSchema = z.object({
  task_type: z.literal('MarkTheWords'),
  template: z.string(),
  retryAllowed: z.boolean(),
  showSolutionsAllowed: z.boolean(),
  strings: z.object({
    checkButton: z.string(),
    retryButton: z.string(),
    solutionsButton: z.string(),
    resultMessage: z.string(),
  }),
});
export type MarkTheWordsTask = z.infer<typeof markTheWordsTaskSchema>;

export const memoryCardSchema = z.object({
  uuid: z.string(),
  imageUrl: z.string().optional(),
  altText: z.string().optional(),
});
export type MemoryCard = z.infer<typeof memoryCardSchema>;

export const memoryTaskSchema = z.object({
  task_type: z.literal('Memory'),
  cards: z.array(memoryCardSchema),
  strings: z.object({
    checkButton: z.string(),
    retryButton: z.string(),
    solutionsButton: z.string(),
    resultMessage: z.string(),
  }),
});
export type MemoryTask = z.infer<typeof memoryTaskSchema>;

export const fillInTheBlanksTaskSchema = z.object({
  task_type: z.literal('FillInTheBlanks'),
  template: z.string(),
  retryAllowed: z.boolean(),
  showSolutionsAllowed: z.boolean(),
  caseSensitive: z.boolean(),
  autoCorrect: z.boolean(),
  allBlanksMustBeFilledForSolutions: z.boolean(),
  acceptTypos: z.boolean(),
  strings: z.object({
    checkButton: z.string(),
    retryButton: z.string(),
    solutionsButton: z.string(),
    fillInAllBlanksMessage: z.string(),
    resultMessage: z.string(),
  }),
  feedback: z.array(feedbackSchema),
});
export type FillInTheBlanksTask = z.infer<typeof fillInTheBlanksTaskSchema>;

export const flashCardSchema = z.object({
  uuid: z.string(),
  question: z.string(),
  answer: z.string(),
  imageUrl: z.string().optional(),
  altText: z.string().optional(),
});
export type FlashCard = z.infer<typeof flashCardSchema>;

export const flashCardTaskSchema = z.object({
  task_type: z.literal('FlashCards'),
  cards: z.array(flashCardSchema),
});
export type FlashCardTask = z.infer<typeof flashCardTaskSchema>;

export const questionAnswerSchema = z.object({
  text: z.string(),
  correct: z.boolean(),
  strings: z.object({
    hint: z.string(),
    feedbackSelected: z.string(),
    feedbackNotSelected: z.string(),
  }),
});
export type QuestionAnswer = z.infer<typeof questionAnswerSchema>;

export const questionTaskSchema = z.object({
  task_type: z.literal('Question'),
  question: z.string(),
  answers: z.array(questionAnswerSchema),
  canAnswerMultiple: z.boolean(),
  retryAllowed: z.boolean(),
  randomOrder: z.boolean(),
  showSolutionsAllowed: z.boolean(),
  strings: z.object({
    checkButton: z.string(),
    retryButton: z.string(),
    solutionsButton: z.string(),
  }),
});
export type QuestionTask = z.infer<typeof questionTaskSchema>;

export const imagePairSchema = z.object({
  uuid: z.string(),
  imageUrl: z.string().optional(),
  altText: z.string().optional(),
});
export type ImagePair = z.infer<typeof imagePairSchema>;

export const imagePairingTaskSchema = z.object({
  task_type: z.literal('ImagePairing'),
  imagePairs: z.array(imagePairSchema),
  strings: z.object({
    checkButton: z.string(),
    retryButton: z.string(),
    solutionsButton: z.string(),
    resultMessage: z.string(),
  }),
});
export type ImagePairingTask = z.infer<typeof imagePairingTaskSchema>;

export const taskDefinitionSchema = z.union([
  fillInTheBlanksTaskSchema,
  flashCardTaskSchema,
  questionTaskSchema,
  dragTheWordsTaskSchema,
  markTheWordsTaskSchema,
  memoryTaskSchema,
  imagePairingTaskSchema,
]);
export type TaskDefinition = z.infer<typeof taskDefinitionSchema>;
// Here, a bit of boilerplate is required to create a schema for the union of
// all possible 'task_type' values
export const taskTypeSchema = z.union([
  fillInTheBlanksTaskSchema.shape.task_type,
  flashCardTaskSchema.shape.task_type,
  questionTaskSchema.shape.task_type,
  dragTheWordsTaskSchema.shape.task_type,
  markTheWordsTaskSchema.shape.task_type,
  memoryTaskSchema.shape.task_type,
]);

export function newTask(type: TaskDefinition['task_type']): TaskDefinition {
  switch (type) {
    case 'FillInTheBlanks':
      return {
        task_type: 'FillInTheBlanks',
        template: 'Hier entsteht der *Lücken*text.',
        retryAllowed: true,
        showSolutionsAllowed: true,
        caseSensitive: false,
        autoCorrect: false,
        allBlanksMustBeFilledForSolutions: false,
        acceptTypos: true,
        strings: {
          checkButton: 'Anworten überprüfen',
          retryButton: 'Erneut versuchen',
          solutionsButton: 'Lösungen anzeigen',
          fillInAllBlanksMessage:
            'Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen.',
          resultMessage: ':correct von :total Lücken richtig ausgefüllt.',
        },
        feedback: [
          { percentage: 0, message: 'Versuchen Sie es noch einmal.' },
          { percentage: 50, message: 'Gut.' },
          { percentage: 75, message: 'Sehr gut.' },
          { percentage: 100, message: 'Perfekt!' },
        ],
      };
    case 'FlashCards':
      return {
        task_type: 'FlashCards',
        cards: [
          {
            uuid: v4(),
            question: 'Question',
            answer: 'Answer',
          },
        ],
      };
    case 'Question':
      return {
        task_type: 'Question',
        question:
          'Welche dieser Himmelskörper sind Planeten in unserem Sonnensystem?',
        answers: [
          {
            text: 'Mond',
            correct: false,
            strings: {
              hint: '',
              feedbackSelected: 'Dies ist ein Mond, kein Planet.',
              feedbackNotSelected: 'Genau, der Mond ist ein Mond.',
            },
          },
          {
            text: 'Merkur',
            correct: true,
            strings: {
              hint: '',
              feedbackSelected: '',
              feedbackNotSelected: '',
            },
          },
          {
            text: 'Venus',
            correct: true,
            strings: {
              hint: '',
              feedbackSelected: '',
              feedbackNotSelected: '',
            },
          },
          {
            text: 'Mars',
            correct: true,
            strings: {
              hint: '',
              feedbackSelected: '',
              feedbackNotSelected: '',
            },
          },
          {
            text: 'Pluto',
            correct: false,
            strings: {
              hint: 'Hat sich hier was geändert?',
              feedbackSelected: '',
              feedbackNotSelected: '',
            },
          },
          {
            text: 'Titan',
            correct: false,
            strings: {
              hint: '',
              feedbackSelected:
                'Das ist leider nicht richtig. Der Titan ist ein Mond vom Saturn.',
              feedbackNotSelected: 'Genau, der Titan ist ein Mond vom Saturn.',
            },
          },
          {
            text: 'Sonne',
            correct: false,
            strings: {
              hint: '',
              feedbackSelected:
                'Das ist leider nicht richtig. Die Sonne ist ein Stern.',
              feedbackNotSelected: 'Genau, die Sonne ist ein Stern.',
            },
          },
        ],
        canAnswerMultiple: true,
        retryAllowed: true,
        randomOrder: true,
        showSolutionsAllowed: true,
        strings: {
          checkButton: 'Antworten überprüfen',
          retryButton: 'Erneut versuchen',
          solutionsButton: 'Lösungen anzeigen',
        },
      };
    case 'DragTheWords':
      return {
        task_type: 'DragTheWords',
        template: 'Drag the *words* to the matching *gaps*.',
        retryAllowed: true,
        showSolutionsAllowed: true,
        instantFeedback: false,
        allBlanksMustBeFilledForSolutions: false,
        alphabeticOrder: false,
        strings: {
          checkButton: 'Antworten überprüfen',
          retryButton: 'Erneut versuchen',
          solutionsButton: 'Lösungen anzeigen',
          fillInAllBlanksMessage:
            'Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen.',
          resultMessage: ':correct von :total Lücken richtig ausgefüllt.',
        },
        feedback: [
          { percentage: 0, message: 'Versuchen Sie es noch einmal.' },
          { percentage: 50, message: 'Gut.' },
          { percentage: 75, message: 'Sehr gut.' },
          { percentage: 100, message: 'Perfekt!' },
        ],
      };
    case 'MarkTheWords':
      return {
        task_type: 'MarkTheWords',
        template:
          '*The* moon is our natural satellite, *i.e.* it revolves around the *Earth*!',
        retryAllowed: true,
        showSolutionsAllowed: true,
        strings: {
          checkButton: 'Antworten überprüfen',
          retryButton: 'Erneut versuchen',
          solutionsButton: 'Lösungen anzeigen',
          resultMessage: ':correct von :total Wörter richtig ausgewählt.',
        },
      };
    case 'Memory':
      return {
        task_type: 'Memory',
        cards: [
          {
            uuid: v4(),
          },
        ],
        strings: {
          checkButton: 'Antworten überprüfen',
          retryButton: 'Erneut versuchen',
          solutionsButton: 'Lösungen anzeigen',
          resultMessage: ':correct von :total Wörter richtig ausgewählt.',
        },
      };
    default:
      throw new Error('Unimplemented type: ' + type);
  }
}

export function viewerForTaskType(type: TaskDefinition['task_type']) {
  switch (type) {
    case 'FillInTheBlanks':
      return FillInTheBlanksViewer;
    case 'FlashCards':
      return FlashCardsViewer;
    case 'Question':
      return QuestionViewer;
    case 'DragTheWords':
      return DragTheWordsViewer;
    case 'MarkTheWords':
      return MarkTheWordsViewer;
    case 'Memory':
      return MemoryViewer;
    case 'ImagePairing':
      return ImagePairingViewer;
    default:
      throw new Error('Unimplemented task type: ' + type);
  }
}

export function editorForTaskType(type: TaskDefinition['task_type']) {
  switch (type) {
    case 'FillInTheBlanks':
      return FillInTheBlanksEditor;
    case 'FlashCards':
      return FlashCardsEditor;
    case 'Question':
      return QuestionEditor;
    case 'DragTheWords':
      return DragTheWordsEditor;
    case 'MarkTheWords':
      return MarkTheWordsEditor;
    case 'Memory':
      return MemoryEditor;
    case 'ImagePairing':
      return ImagePairingEditor;
    default:
      throw new Error('Unimplemented task type: ' + type);
  }
}
