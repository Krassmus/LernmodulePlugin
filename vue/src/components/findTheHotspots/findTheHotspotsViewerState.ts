import { InjectionKey, Ref } from 'vue';

/**
 * In the Find The Hotspots Viewer, we use provide/inject to pass data to many
 * different components at once without prop drilling.
 * There is a little bit of ceremony required to have the passed data be statically typed.
 * https://vuejs.org/guide/typescript/composition-api.html#typing-provide-inject
 */
export const findTheHotspotsViewerStateSymbol = Symbol(
  'Find The Hotspots Viewer state'
) as InjectionKey<FindTheHotspotsViewerState>;

export interface FindTheHotspotsViewerState {
  clickHotspot(id: string | undefined, x: number, y: number): void;
  clickBackground(x: number, y: number): void;
}
