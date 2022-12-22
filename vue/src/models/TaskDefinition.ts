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

// TODO Use zod or another parsing library to define these datatypes
export type TaskDefinition =
  | FillInTheBlanksDefinition
  | FlashCardTaskDefinition
  | QuestionTaskDefinition
  | DragTheWordsTaskDefinition
  | MarkTheWordsTaskDefinition;

export type FillInTheBlanksDefinition = {
  task_type: 'FillInTheBlanks';
  template: string;
  retryAllowed: boolean;
  showSolutionsAllowed: boolean;
  caseSensitive: boolean;
  autoCorrect: boolean;
  allBlanksMustBeFilledForSolutions: boolean;
  strings: {
    checkButton: string;
    retryButton: string;
    solutionsButton: string;
    fillInAllBlanksMessage: string;
    resultMessage: string;
  };
};

export type FlashCardTaskDefinition = {
  task_type: 'FlashCards';
  cards: FlashCard[];
};

export type FlashCard = {
  frontText: string;
  backText: string;
};

export type QuestionTaskDefinition = {
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
  hint: string;
};

export type DragTheWordsTaskDefinition = {
  task_type: 'DragTheWords';
  template: string;
  retryAllowed: boolean;
  showSolutionsAllowed: boolean;
  instantFeedback: boolean;
  strings: {
    checkButton: string;
    retryButton: string;
    solutionsButton: string;
    fillInAllBlanksMessage: string;
    resultMessage: string;
  };
};

export type MarkTheWordsTaskDefinition = {
  task_type: 'MarkTheWords';
  template: string;
  retryAllowed: boolean;
  showSolutionsAllowed: boolean;
  instantFeedback: boolean;
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
        retryAllowed: false,
        showSolutionsAllowed: false,
        caseSensitive: false,
        autoCorrect: false,
        allBlanksMustBeFilledForSolutions: false,
        strings: {
          checkButton: 'Anworten überprüfen',
          retryButton: 'Erneut versuchen',
          solutionsButton: 'Lösungen anzeigen',
          fillInAllBlanksMessage:
            'Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen.',
          resultMessage: ':correct von :total Lücken richtig ausgefüllt.',
        },
      };
    case 'FlashCards':
      return {
        task_type: 'FlashCards',
        cards: [
          {
            frontText: 'Front text',
            backText: 'Back text',
          },
        ],
      };
    case 'Question':
      return {
        task_type: 'Question',
        question: 'Nenne alle Planeten in unserem Sonnensystem.',
        answers: [
          {
            text: 'Mond',
            correct: false,
            hint: '',
          },
          {
            text: 'Merkur',
            correct: true,
            hint: '',
          },
          {
            text: 'Venus',
            correct: true,
            hint: '',
          },
          {
            text: 'Erde',
            correct: true,
            hint: '',
          },
          {
            text: 'Mars',
            correct: true,
            hint: '',
          },
          {
            text: 'Io',
            correct: false,
            hint: '',
          },
          {
            text: 'Jupiter',
            correct: true,
            hint: '',
          },
          {
            text: 'Saturn',
            correct: true,
            hint: '',
          },
          {
            text: 'Uranus',
            correct: true,
            hint: '',
          },
          {
            text: 'Neptun',
            correct: true,
            hint: '',
          },
          {
            text: 'Pluto',
            correct: false,
            hint: 'Hat sich hier was geändert?',
          },
          {
            text: 'Titan',
            correct: false,
            hint: '',
          },
          {
            text: 'Sonne',
            correct: false,
            hint: '',
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
        strings: {
          checkButton: 'Antworten überprüfen',
          retryButton: 'Erneut versuchen',
          solutionsButton: 'Lösungen anzeigen',
          fillInAllBlanksMessage:
            'Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen.',
          resultMessage: ':correct von :total Lücken richtig ausgefüllt.',
        },
      };
    case 'MarkTheWords':
      return {
        task_type: 'MarkTheWords',
        template:
          'The moon is our natural satellite, i.e. it revolves around the Earth!',
        retryAllowed: true,
        showSolutionsAllowed: true,
        instantFeedback: false,
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
    default:
      throw new Error('Unimplemented task type: ' + type);
  }
}
