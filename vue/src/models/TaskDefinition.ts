import FillInTheBlanksViewer from '@/components/FillInTheBlanksViewer.vue';
import FillInTheBlanksEditor from '@/components/FillInTheBlanksEditor.vue';
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
import ImageSequencingViewer from '@/components/ImageSequencingViewer.vue';
import ImageSequencingEditor from '@/components/ImageSequencingEditor.vue';
import { v4 } from 'uuid';
import { z } from 'zod';
import InteractiveVideoViewer from '@/components/interactiveVideo/InteractiveVideoViewer.vue';
import InteractiveVideoEditor from '@/components/interactiveVideo/InteractiveVideoEditor.vue';
import { interactiveVideoTaskSchema } from '@/models/InteractiveVideoTask';
import { $gettext } from '@/language/gettext';

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
  imageUrl: z.string(),
  altText: z.string(),
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

export const imageSchema = z.object({
  uuid: z.string(),
  imageUrl: z.string(),
  altText: z.string(),
});
export type Image = z.infer<typeof imageSchema>;

export const imagePairSchema = z.object({
  uuid: z.string(),
  draggableImage: imageSchema,
  targetImage: imageSchema,
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

export const imageSequencingTaskSchema = z.object({
  task_type: z.literal('ImageSequencing'),
  images: z.array(imageSchema),
  strings: z.object({
    checkButton: z.string(),
    retryButton: z.string(),
    solutionsButton: z.string(),
    resultMessage: z.string(),
  }),
});
export type ImageSequencingTask = z.infer<typeof imageSequencingTaskSchema>;

export const taskDefinitionSchema = z.union([
  fillInTheBlanksTaskSchema,
  questionTaskSchema,
  dragTheWordsTaskSchema,
  markTheWordsTaskSchema,
  memoryTaskSchema,
  imagePairingTaskSchema,
  imageSequencingTaskSchema,
  interactiveVideoTaskSchema,
]);
export type TaskDefinition = z.infer<typeof taskDefinitionSchema>;

// Allowed lmb task types to be inserted in the Interactive Video Editor.
// Excludes the type 'interactive video' to avoid a recursive reference, which
// is cumbersome to solve in Typescript/Zod.
// I at first used the workaround described at https://github.com/colinhacks/zod#recursive-types
// but I later ran into hard-to-resolve typechecking errors when I started to
// use .optional() inside of schemas. I decided this is the better option for now. -Ann
export const taskDefinitionSchemaMinusInteractiveVideo = z.union([
  fillInTheBlanksTaskSchema,
  questionTaskSchema,
  dragTheWordsTaskSchema,
  markTheWordsTaskSchema,
  memoryTaskSchema,
  imagePairingTaskSchema,
  imageSequencingTaskSchema,
]);

// Here, a bit of boilerplate is required to create a schema for the union of
// all possible 'task_type' values
export const taskTypeSchema = z.union([
  fillInTheBlanksTaskSchema.shape.task_type,
  questionTaskSchema.shape.task_type,
  dragTheWordsTaskSchema.shape.task_type,
  markTheWordsTaskSchema.shape.task_type,
  memoryTaskSchema.shape.task_type,
  imagePairingTaskSchema.shape.task_type,
  imageSequencingTaskSchema.shape.task_type,
  interactiveVideoTaskSchema.shape.task_type,
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
            imageUrl: '',
            altText: '',
          },
        ],
        strings: {
          checkButton: 'Antworten überprüfen',
          retryButton: 'Erneut versuchen',
          solutionsButton: 'Lösungen anzeigen',
          resultMessage: ':correct von :total Wörter richtig ausgewählt.',
        },
      };
    case 'ImagePairing':
      return {
        task_type: 'ImagePairing',
        imagePairs: [
          {
            uuid: v4(),
            draggableImage: {
              uuid: v4(),
              imageUrl: '',
              altText: '',
            },
            targetImage: {
              uuid: v4(),
              imageUrl: '',
              altText: '',
            },
          },
        ],
        strings: {
          checkButton: 'Antworten überprüfen',
          retryButton: 'Erneut versuchen',
          solutionsButton: 'Lösungen anzeigen',
          resultMessage: ':correct von :total Wörter richtig ausgewählt.',
        },
      };
    case 'ImageSequencing':
      return {
        task_type: 'ImageSequencing',
        images: [
          {
            uuid: v4(),
            imageUrl: '',
            altText: '',
          },
        ],
        strings: {
          checkButton: 'Antworten überprüfen',
          retryButton: 'Erneut versuchen',
          solutionsButton: 'Lösungen anzeigen',
          resultMessage: ':correct von :total Wörter richtig ausgewählt.',
        },
      };
    case 'InteractiveVideo':
      return {
        task_type: 'InteractiveVideo',
        interactions: [],
        video: {
          type: 'none',
        },
        autoplay: false,
        startAt: 0,
      };
    default:
      throw new Error('Unimplemented type: ' + type);
  }
}

export function viewerForTaskType(type: TaskDefinition['task_type']) {
  switch (type) {
    case 'FillInTheBlanks':
      return FillInTheBlanksViewer;
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
    case 'ImageSequencing':
      return ImageSequencingViewer;
    case 'InteractiveVideo':
      return InteractiveVideoViewer;
    default:
      throw new Error('Unimplemented task type: ' + type);
  }
}

export function editorForTaskType(type: TaskDefinition['task_type']) {
  switch (type) {
    case 'FillInTheBlanks':
      return FillInTheBlanksEditor;
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
    case 'ImageSequencing':
      return ImageSequencingEditor;
    case 'InteractiveVideo':
      return InteractiveVideoEditor;
    default:
      throw new Error('Unimplemented task type: ' + type);
  }
}

export function printTaskType(type: TaskDefinition['task_type']): string {
  switch (type) {
    case 'ImageSequencing':
      return $gettext('Image Sequencing');
    case 'Memory':
      return $gettext('Memory');
    case 'FillInTheBlanks':
      return $gettext('Fill In The Blanks');
    case 'Question':
      return $gettext('Question');
    case 'DragTheWords':
      return $gettext('Drag The Words');
    case 'MarkTheWords':
      return $gettext('Mark The Words');
    case 'ImagePairing':
      return $gettext('Image Pairing');
    case 'InteractiveVideo':
      return $gettext('Interactive Video');
  }
}

/**
 * @return true if the viewer for the given task type should be visible
 * while the editor for that task type is open.
 */
export function showViewerAboveEditor(
  type: TaskDefinition['task_type']
): boolean {
  return type !== 'InteractiveVideo';
}

export function iconForTaskType(type: TaskDefinition['task_type']): string {
  switch (type) {
    case 'Memory':
      break;
    case 'FillInTheBlanks':
      return 'file-office';
    case 'Question':
      return 'question';
    case 'DragTheWords':
      return 'tan3';
    case 'MarkTheWords':
      return 'tan3';
    case 'ImagePairing':
      break;
    case 'ImageSequencing':
      break;
    case 'InteractiveVideo':
      break;
  }
  return 'question';
}
