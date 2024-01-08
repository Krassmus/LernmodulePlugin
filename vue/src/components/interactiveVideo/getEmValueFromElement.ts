/**
 * Get the equivalent EM value on a given element with a given pixel value.
 *
 * Normally the number of pixel specified should come from the element itself (e.g. element.style.height) since EM is
 * relative.
 *
 * @param {Object} element - The HTML element.
 * @param {Number} pixelValue - The number of pixel to convert in EM on this specific element.
 * @author Nicolas Bouvrette
 * @license CC BY-SA 3.0 https://stackoverflow.com/a/34540037/7359454
 * Adapted by Ann Yanich
 *
 * @returns {Boolean|undefined} The EM value, or undefined if unable to convert.
 */
export default function getEmValueFromElement(
  element: Element,
  pixelValue: number
): number | undefined {
  if (element.parentNode) {
    const parentFontSize = parseFloat(
      window.getComputedStyle(element.parentNode as Element).fontSize
    );
    const elementFontSize = parseFloat(
      window.getComputedStyle(element).fontSize
    );
    const pixelValueOfOneEm =
      (elementFontSize / parentFontSize) * elementFontSize;
    return pixelValue / pixelValueOfOneEm;
  }
  return undefined;
}
