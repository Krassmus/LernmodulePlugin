export interface VideoMetadata {
  length: number;
}

export type DragState =
  | {
      type: 'dragInteraction';
      interactionId: string;
      mouseStartPos: [number, number]; // clientX, clientY
      interactionStartPos: [number, number]; // fraction x, fraction y
    }
  | {
      type: 'resizeInteraction';
      interactionId: string;
      mouseStartPos: [number, number]; // clientX, clientY
      interactionStartPos: [number, number]; // fraction x, fraction y
      interactionStartSize: [number, number]; // fraction x, fraction y
      handle: string;
    }
  | undefined;
