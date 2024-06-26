import { createGettext } from 'vue3-gettext';
import translations from '@/language/translations.json';
import { memoize } from 'lodash';

const selectedLanguage = Object.values(window.STUDIP.INSTALLED_LANGUAGES).find(
  (lang) => lang.selected
);
const gettextPlugin = createGettext({
  availableLanguages: {
    en: 'English',
    de: 'Deutsch',
  },
  defaultLanguage: selectedLanguage?.path ?? 'de',
  translations: translations,
});

// Cache gettext results.
// This fixes the problem we had where the error 'No translations found for de' was
// being printed excessively often (sometimes 1000s of times per second) during
// certain UI interactions, e.g. when dragging interactions around on top of
// the video in the Interactive Video Editor.
const $gettext = memoize(gettextPlugin.$gettext, (...args) =>
  JSON.stringify(args)
);

const $pgettext = memoize(gettextPlugin.$pgettext, (...args) =>
  JSON.stringify(args)
);

const $npgettext = memoize(gettextPlugin.$npgettext, (...args) =>
  JSON.stringify(args)
);

export { gettextPlugin, $gettext, $pgettext, $npgettext };
