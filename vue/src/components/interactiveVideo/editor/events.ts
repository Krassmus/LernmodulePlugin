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

export type TimelineDragState =
  | { type: 'timeMarker' }
  | { type: 'panTimeline' }
  | {
      type: 'interaction';
      id: string;
      mouseStartPos: [number, number]; // clientX, clientY
      interactionStartTime: number; // Seconds
      interactionDuration: number; // Seconds
    }
  | {
      type: 'interactionStart';
      id: string;
      time: number; // Seconds
    }
  | {
      type: 'interactionEnd';
      id: string;
      time: number; // Seconds
    }
  | undefined;
