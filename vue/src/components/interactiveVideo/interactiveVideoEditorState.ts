import { InjectionKey, Ref } from 'vue';

/**
 * In the Interactive Video Editor, we use provide/inject to pass data to many
 * different components at once without prop drilling.
 * There is a little bit of ceremony required to have the passed data be statically typed.
 * https://vuejs.org/guide/typescript/composition-api.html#typing-provide-inject
 */
export const interactiveVideoEditorStateSymbol = Symbol(
  'Interactive Video Editor state'
) as InjectionKey<InteractiveVideoEditorState>;

export interface InteractiveVideoEditorState {
  selectedInteractionId: Ref<string | undefined>;
  selectInteraction(id: string): void;
  editInteraction(id: string): void;
  dragInteraction(
    interactionId: string,
    clampedXFraction: number,
    clampedYFraction: number
  ): void;
  resizeOverlay(
    id: string,
    x: number,
    y: number,
    width: number,
    height: number
  ): void;
  deleteInteraction(interactionId: string): void;

  dragInteractionTimeline(id: string, startTime: number): void;
}
