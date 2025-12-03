import { InjectionKey, Ref } from 'vue';
import {
  DragState,
  TimelineDragState,
} from '@/components/interactiveVideo/events';

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
  selectInteraction(id: string | undefined): void;
  editInteraction(id: string): void;
  dragInteraction(
    interactionId: string,
    clampedXFraction: number,
    clampedYFraction: number,
    dragState: DragState
  ): void;
  resizeOverlay(
    id: string,
    x: number,
    y: number,
    width: number,
    height: number,
    dragState: DragState
  ): void;
  deleteInteraction(interactionId: string): void;

  dragInteractionTimeline(
    id: string,
    startTime: number,
    dragState: TimelineDragState
  ): void;
  resizeInteractionTimeline(
    id: string,
    type: 'start' | 'end',
    time: number,
    dragState: TimelineDragState
  ): void;
}
