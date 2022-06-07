import FillInTheBlanksViewer from '@/components/FillInTheBlanksViewer.vue';
import FillInTheBlanksEditor from '@/components/FillInTheBlanksEditor.vue';

// TODO Use zod or another parsing library to define these datatypes
export type TaskDefinition = FillInTheBlanksDefinition | MemoryTaskDefinition;
export type FillInTheBlanksDefinition = {
  task_type: 'FillInTheBlanks';
  template: string;
};
export type MemoryTaskDefinition = {
  task_type: 'Memory';
  cards: MemoryCard[];
};
export type MemoryCard = {
  frontText: string;
  backText: string;
};

export function newTask(type: TaskDefinition['task_type']): TaskDefinition {
  switch (type) {
    case 'FillInTheBlanks':
      return {
        task_type: 'FillInTheBlanks',
        template: 'Template goes {here}.',
      };
    default:
      throw new Error('Unimplemented type: ' + type);
  }
}

export function viewerForTaskType(type: TaskDefinition['task_type']) {
  switch (type) {
    case 'FillInTheBlanks':
      return FillInTheBlanksViewer;
    default:
      throw new Error('Unimplemented task type: ' + type);
  }
}

export function editorForTaskType(type: TaskDefinition['task_type']) {
  switch (type) {
    case 'FillInTheBlanks':
      return FillInTheBlanksEditor;
    default:
      throw new Error('Unimplemented task type: ' + type);
  }
}
