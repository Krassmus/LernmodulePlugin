import { store, taskEditorStore } from '@/store';
import { Directive, DirectiveBinding } from 'vue';

/**
 * This is a custom directive that you can use like v-model in the Lernmodul-Editor.
 * Like v-model, it is a two-way binding, but rather than directly modifying
 * 'taskDefinition', it saves changes into the undo-redo stack by calling
 * 'taskEditorStore.performEdit'.
 * TODO: Can I get better stack traces when errors are thrown inside of
 *   directive hooks?  Right now, they do not show what line of the .vue file
 *   caused the errors to be thrown.
 */
export const modelUndoable: Directive = {
  mounted(
    el: HTMLElement,
    binding: DirectiveBinding<unknown>,
    vnode,
    prevVnode
  ) {
    const validEl = validateElement(el);

    el.addEventListener('input', (ev: Event) => {
      const splitPath = parsePath(binding);
      const [firstPathEl, ...restOfPath] = splitPath;
      const value = vnode.el.value;
      const newState = JSON.parse(
        JSON.stringify(taskEditorStore.taskDefinition)
      );
      setValueAtPath(newState, restOfPath, value);
      taskEditorStore.performEdit({
        newTaskDefinition: newState,
        undoBatch: splitPath,
      });
      console.log(el, binding, vnode, prevVnode, ev, splitPath, value);
    });

    function setElValueFromStore() {
      const splitPath = parsePath(binding);
      const storeValue = getValueAtPath(taskEditorStore, splitPath);
      const validatedStoreValue = validateBoundValue(storeValue);
      validEl.value = validatedStoreValue.toString();
    }
    store.watch(
      () => {
        const splitPath = parsePath(binding);
        return getValueAtPath(taskEditorStore, splitPath);
      },
      () => setElValueFromStore()
    );
    setElValueFromStore();
  },
} as const;

/**
 * Make sure that the binding supplied by the user is a valid one.
 * It should be a path to a field of 'taskEditorStore.taskDefinition',
 * e.g. 'taskDefinition.task_type'.
 * @param binding - See https://vuejs.org/guide/reusability/custom-directives.html#directive-hooks
 */
function parsePath(binding: DirectiveBinding<unknown>): Array<string> {
  if (typeof binding.value !== 'string') {
    throw new Error(bindingMustBeStringError);
  }
  const splitPath = binding.value.split('.');
  const firstPathEl = splitPath[0];
  if (firstPathEl !== 'taskDefinition') {
    throw new Error(bindingMustUseTaskDefinitionError + binding.value);
  }
  return splitPath;
}

function validateElement(
  el: HTMLElement
): HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement {
  if (
    el instanceof HTMLInputElement ||
    el instanceof HTMLTextAreaElement ||
    el instanceof HTMLSelectElement
  ) {
    return el;
  }
  throw new Error(invalidElementTypeError);
}

function validateBoundValue(val: unknown): string | number | boolean {
  if (
    typeof val !== 'string' &&
    typeof val !== 'number' &&
    typeof val !== 'boolean'
  ) {
    throw new Error(boundValueMustBePrimitiveError + typeof val);
  }
  return val;
}

function getValueAtPath(o: object, path: Array<string>): unknown {
  return path.reduce((accumulator: any, pathComponent: string) => {
    if (!Object.prototype.hasOwnProperty.call(accumulator, pathComponent)) {
      throw new Error(invalidPathError + path.join('.'));
    }
    return accumulator[pathComponent];
  }, o);
}

function setValueAtPath(o: object, splitPath: Array<string>, value: any) {
  const [lastPathComponent, ...restOfPath] = [splitPath.pop(), ...splitPath];
  if (lastPathComponent === undefined) {
    throw new Error('Path has no last element');
  }
  const targetObject = restOfPath.reduce(
    (accumulator: Record<string, any>, pathComponent: string) =>
      accumulator[pathComponent],
    o
  );
  targetObject[lastPathComponent] = value;
}

// TODO translate these strings in Weblate.
const bindingMustBeStringError =
  'The value supplied to v-model-undoable must be a string. ' +
  'Example: <select v-model-undoable="\'taskDefinition.task_type\'">';

const bindingMustUseTaskDefinitionError =
  'The string supplied to v-model-undoable must be a path which begins with ' +
  '"taskDefinition", and the path must be wrapped in two sets of quotes. \n' +
  'Example usage: <select v-model-undoable="\'taskDefinition.task_type\'">\n' +
  'You supplied the following string: ';

const boundValueMustBePrimitiveError =
  'The value bound with v-model-undoable must be a string, number or boolean.  ' +
  'Other datatypes are not currently supported.  The value found is of type ';

const invalidPathError =
  'No field was found in the store under the given path.  Did you make a ' +
  'typo?  Path: ';

const invalidElementTypeError =
  'v-model-undoable can only be used on <input>, <textarea> and <select> elements';
