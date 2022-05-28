// TODO Use zod or another parsing library to define these datatypes
export type TaskDefinition = FillInTheBlanksDefinition;
export type FillInTheBlanksDefinition = {
  task_type: 'FillInTheBlanks';
  template: string;
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
