import { Directive } from 'vue';

export const disableDrag: Directive = {
  mounted(el: HTMLElement) {
    // Set the parent element to non-draggable
    el.setAttribute('draggable', 'false');

    // Function to recursively disable dragging for all child elements
    const setDraggableFalse = (element: HTMLElement) => {
      element.setAttribute('draggable', 'false');
      element.childNodes.forEach((child) => {
        if (child instanceof HTMLElement) {
          setDraggableFalse(child);
        }
      });
    };

    setDraggableFalse(el);
  },
} as const;
