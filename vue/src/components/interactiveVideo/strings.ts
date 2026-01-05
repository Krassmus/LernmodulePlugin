import { $gettext } from '@/language/gettext';

const strings = {
  forbiddenToNestInteractiveVideos: $gettext(
    'Es ist nicht erlaubt, ein ' +
      'Interactive Video in ein anderes Interactive Video einzufügen.'
  ),
  notAnOverlayError: $gettext(
    'Die ausgeführte Aktion ist nur für Overlay-Elemente gültig.'
  ),
  interactionNotFoundError: $gettext(
    'Es wurde keine Interaktion mit der angegebenen ID gefunden.'
  ),
  postCouldNotBeParsedError: $gettext(
    'Dieser Post konnte nicht geladen werden.'
  ),
  couldNotLoadPostsError: $gettext(
    'Die Posts unter dem Video konnten nicht geladen werden.'
  ),
} as const;
export default strings;
