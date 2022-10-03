import FillInTheBlanksViewer from '@/components/FillInTheBlanksViewer.vue';
import FillInTheBlanksEditor from '@/components/FillInTheBlanksEditor.vue';
import FlashCardsViewer from '@/components/FlashCardsViewer.vue';
import FlashCardsEditor from '@/components/FlashCardsEditor.vue';
import QuestionEditor from '@/components/QuestionEditor.vue';
import QuestionViewer from '@/components/QuestionViewer.vue';

// TODO Use zod or another parsing library to define these datatypes
export type TaskDefinition =
  | FillInTheBlanksDefinition
  | FlashCardTaskDefinition
  | QuestionTaskDefinition;

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
  strings: {
    checkButton: string;
    retryButton: string;
  };
};

export type QuestionAnswer = {
  text: string;
  correct: boolean;
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
          resultMessage: 'Lücken richtig ausgefüllt.',
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
          },
          {
            text: 'Merkur',
            correct: true,
          },
          {
            text: 'Venus',
            correct: true,
          },
          {
            text: 'Erde',
            correct: true,
          },
          {
            text: 'Mars',
            correct: true,
          },
          {
            text: 'Io',
            correct: false,
          },
          {
            text: 'Jupiter',
            correct: true,
          },
          {
            text: 'Saturn',
            correct: true,
          },
          {
            text: 'Uranus',
            correct: true,
          },
          {
            text: 'Neptun',
            correct: true,
          },
          {
            text: 'Pluto',
            correct: false,
          },
          {
            text: 'Titan',
            correct: false,
          },
          {
            text: 'Sonne',
            correct: false,
          },
        ],
        canAnswerMultiple: true,
        retryAllowed: true,
        strings: {
          checkButton: 'Antworten überprüfen',
          retryButton: 'Erneut versuchen',
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
    default:
      throw new Error('Unimplemented task type: ' + type);
  }
}
