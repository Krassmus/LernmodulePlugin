// TODO Use zod or another parsing library to define these datatypes
export type TaskDefinition = FillInTheBlanks;
export type FillInTheBlanks = {
  task_type: 'FillInTheBlanks';
  template: string;
};
