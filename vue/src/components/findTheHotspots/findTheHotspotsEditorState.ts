import { InjectionKey, Ref } from 'vue';

/**
 * In the Find The Hotspots Editor, we use provide/inject to pass data to many
 * different components at once without prop drilling.
 * There is a little bit of ceremony required to have the passed data be statically typed.
 * https://vuejs.org/guide/typescript/composition-api.html#typing-provide-inject
 */
export const findTheHotspotsEditorStateSymbol = Symbol(
  'Find The Hotspots Editor state'
) as InjectionKey<FindTheHotspotsEditorState>;

export interface FindTheHotspotsEditorState {
  // Some of this stuff was copied over from interactiveVideoEditorState.
  // We will adapt it together to work for Find The Hotspots.
  selectedHotspotId: Ref<string | undefined>;
  selectHotspot(id: string | undefined): void;
  // editInteraction(id: string): void;
  // dragInteraction(
  //     interactionId: string,
  //     clampedXFraction: number,
  //     clampedYFraction: number
  // ): void;
  // resizeOverlay(
  //     id: string,
  //     x: number,
  //     y: number,
  //     width: number,
  //     height: number
  // ): void;
  // deleteInteraction(interactionId: string): void;
  // dragInteractionTimeline(id: string, startTime: number): void;
}
