import { store, taskEditorStore } from '@/store';
import { Directive } from 'vue';

/**
 * This is a custom directive that you can use like v-model in the Lernmodul-Editor.
 * Like v-model, it is a two-way binding, but rather than directly modifying
 * 'taskDefinition', it saves changes into the undo-redo stack by calling
 * 'taskEditorStore.performEdit'.
 */
export const modelUndoable: Directive = {
  mounted(el, binding, vnode, prevVnode) {
    el.addEventListener('input', (ev: InputEvent) => {
      const path = binding.value;
      const [firstPathEl, ...restOfPath] = path.split('.');
      if (firstPathEl !== 'taskDefinition') {
        throw new Error(
          'The directive "v-model-undoable" can only be applied' +
            'to taskEditorStore.taskDefinition. Example usage: ' +
            '<select v-model-undoable="taskDefinition.task_type">'
        );
      }
      const value = vnode.el.value;
      const newState = JSON.parse(
        JSON.stringify(taskEditorStore.taskDefinition)
      );
      setValueAtPath(newState, restOfPath, value);
      taskEditorStore.performEdit({
        newTaskDefinition: newState,
        undoBatch: path,
      });
      console.log(el, binding, vnode, prevVnode, ev, path, value);
    });
    function setElValueFromStore() {
      el.value = getValueAtPath(taskEditorStore, binding.value.split('.'));
    }
    store.watch(
      (store) => getValueAtPath(taskEditorStore, binding.value.split('.')),
      () => setElValueFromStore()
    );
    setElValueFromStore();
  },
} as const;

function getValueAtPath(o: object, path: Array<string>): unknown {
  return path.reduce(
    (accumulator: any, pathComponent: string) => accumulator[pathComponent],
    o
  );
}

function setValueAtPath(o: object, splitPath: Array<string>, value: any) {
  const [last, ...rest] = [splitPath.pop(), ...splitPath];
  if (last === undefined) {
    throw new Error('Path has no last element');
  }
  const targetObject = rest.reduce(
    (accumulator: Record<string, any>, pathComponent: string) =>
      accumulator[pathComponent],
    o
  );
  targetObject[last] = value;
}
