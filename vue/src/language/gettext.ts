import { createGettext } from 'vue3-gettext';
import translations from '@/language/translations.json';

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

const $gettext = gettextPlugin.$gettext;

const translationStrings = {
  allowMultipleTries: $gettext('Mehrere Versuche erlauben'),
};

export { gettextPlugin, $gettext, translationStrings };
