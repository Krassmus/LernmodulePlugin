import { InjectionKey, Ref } from 'vue';

/**
 * In the Interactive Video Editor, we use provide/inject to pass data to many
 * different components at once without prop drilling.
 * There is a little bit of ceremony required to have the passed data be statically typed.
 * https://vuejs.org/guide/typescript/composition-api.html#typing-provide-inject
 */
export const editorStateSymbol = Symbol(
  'Interactive Video Editor state'
) as InjectionKey<EditorState>;

export interface EditorState {
  selectedInteractionId: Ref<string | undefined>;
  selectInteraction(id: string): void;
  dragInteraction(
    interactionId: string,
    clampedXFraction: number,
    clampedYFraction: number
  ): void;

  dragInteractionTimeline(id: string, startTime: number): void;
}
