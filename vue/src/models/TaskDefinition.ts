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
import { v4 } from 'uuid';

// TODO Use zod or another parsing library to define these datatypes
export type TaskDefinition =
  | FillInTheBlanksTask
  | FlashCardTask
  | QuestionTask
  | DragTheWordsTask
  | MarkTheWordsTask
  | MemoryTask;

export type FillInTheBlanksTask = {
  task_type: 'FillInTheBlanks';
  template: string;
  retryAllowed: boolean;
  showSolutionsAllowed: boolean;
  caseSensitive: boolean;
  autoCorrect: boolean;
  allBlanksMustBeFilledForSolutions: boolean;
  acceptTypos: boolean;
  strings: {
    checkButton: string;
    retryButton: string;
    solutionsButton: string;
    fillInAllBlanksMessage: string;
    resultMessage: string;
  };
  feedback: Feedback[];
};

export type Feedback = {
  percentage: number;
  message: string;
};

export type FlashCardTask = {
  task_type: 'FlashCards';
  cards: FlashCard[];
};

export type FlashCard = {
  uuid: string;
  question: string;
  answer: string;
  imageUrl?: string;
  altText?: string;
};

export type MemoryCard = {
  uuid: string;
  imageUrl?: string;
  altText?: string;
};

export type QuestionTask = {
  task_type: 'Question';
  question: string;
  answers: QuestionAnswer[];
  canAnswerMultiple: boolean;
  retryAllowed: boolean;
  randomOrder: boolean;
  showSolutionsAllowed: boolean;
  strings: {
    checkButton: string;
    retryButton: string;
    solutionsButton: string;
  };
};

export type QuestionAnswer = {
  text: string;
  correct: boolean;
  strings: {
    hint: string;
    feedbackSelected: string;
    feedbackNotSelected: string;
  };
};

export type DragTheWordsTask = {
  task_type: 'DragTheWords';
  template: string;
  retryAllowed: boolean;
  showSolutionsAllowed: boolean;
  instantFeedback: boolean;
  allBlanksMustBeFilledForSolutions: boolean;
  alphabeticOrder: boolean;
  strings: {
    checkButton: string;
    retryButton: string;
    solutionsButton: string;
    fillInAllBlanksMessage: string;
    resultMessage: string;
  };
  feedback: Feedback[];
};

export type MarkTheWordsTask = {
  task_type: 'MarkTheWords';
  template: string;
  retryAllowed: boolean;
  showSolutionsAllowed: boolean;
  strings: {
    checkButton: string;
    retryButton: string;
    solutionsButton: string;
    resultMessage: string;
  };
};

export type MemoryTask = {
  task_type: 'Memory';
  cards: MemoryCard[];
  strings: {
    checkButton: string;
    retryButton: string;
    solutionsButton: string;
    resultMessage: string;
  };
};

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
    default:
      throw new Error('Unimplemented task type: ' + type);
  }
}
