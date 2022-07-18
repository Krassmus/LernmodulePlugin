import FillInTheBlanksViewer from '@/components/FillInTheBlanksViewer.vue';
import FillInTheBlanksEditor from '@/components/FillInTheBlanksEditor.vue';
import FlashCardsViewer from '@/components/FlashCardsViewer.vue';
import FlashCardsEditor from '@/components/FlashCardsEditor.vue';

// TODO Use zod or another parsing library to define these datatypes
export type TaskDefinition =
  | FillInTheBlanksDefinition
  | FlashCardTaskDefinition;

export type FillInTheBlanksDefinition = {
  task_type: 'FillInTheBlanks';
  title: string;
  description: string;
  template: string;
  retryAllowed: boolean;
  showSolutionsAllowed: boolean;
  caseSensitive: boolean;
  autoCorrect: boolean;
  allBlanksMustBeFilledForSolutions: boolean;
  stringCheckButton: string;
  stringRetryButton: string;
  stringSolutionsButton: string;
  stringFillInAllBlanksMessage: string;
  stringResultMessage: string;
  // strings: FillInTheBlanksStrings[];
};

// export type FillInTheBlanksStrings = {
//   checkButton: string;
// };

export type FlashCardTaskDefinition = {
  task_type: 'FlashCards';
  cards: FlashCard[];
};

export type FlashCard = {
  frontText: string;
  backText: string;
};

export function newTask(type: TaskDefinition['task_type']): TaskDefinition {
  switch (type) {
    case 'FillInTheBlanks':
      return {
        task_type: 'FillInTheBlanks',
        title: 'Neuer Lückentext',
        description: 'Fülle die Lücken mit den richtigen Wörtern',
        template: 'Hier entsteht der {Lücken}text.',
        retryAllowed: false,
        showSolutionsAllowed: false,
        caseSensitive: false,
        autoCorrect: false,
        allBlanksMustBeFilledForSolutions: false,
        stringCheckButton: 'Antworten überprüfen',
        stringRetryButton: 'Erneut versuchen',
        stringSolutionsButton: 'Lösungen anzeigen',
        stringFillInAllBlanksMessage:
          'Alle Lücken müssen ausgefüllt sein, um Lösungen anzuzeigen.',
        stringResultMessage: 'Lücken richtig ausgefüllt.',
        // strings: [
        //   {
        //     checkButton: 'Überprüfe',
        //   },
        // ],
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
    default:
      throw new Error('Unimplemented task type: ' + type);
  }
}
