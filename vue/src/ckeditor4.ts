// Extract a couple of useful types from @types/ckeditor4
export type CKEditorInstance = InstanceType<typeof window.CKEDITOR.editor>;
export type CKEditorConfig = typeof window.CKEDITOR.config;

/**
 * @return The standard CKEditor config from Stud.IP's wysiwyg module.
 */
export function getDefaultEditorConfig(): CKEditorConfig {
  // The method 'wysiwyg.getDefaultConfig' requires you to provide a
  // jQuery-ified element as its first argument.
  try {
    const fakeTextArea = window.$(document.createElement('textarea'));
    return window.STUDIP.wysiwyg.getDefaultConfig(fakeTextArea);
  } catch (e) {
    console.warn(
      'An error occurred while getting the default CKEditor ' +
        'config.  An empty config will be used instead.'
    );
    return {};
  }
}

export function getMyEditorConfig(): CKEditorConfig {
  const config = getDefaultEditorConfig();
  config.toolbarStartupExpanded = true;

  // The 'studip-floatbar' plugin causes the CKEditor to grow very large
  // when you scroll past it in the form editor, so we'll disable it. - Ann
  config.extraPlugins = config.extraPlugins?.replace(',studip-floatbar', '');
  // At the Uni Oldenburg, studip-floatbar is replaced by uol-floatbar
  // via the Stud.IP plugin "UOLLayoutPlugin"
  config.extraPlugins = config.extraPlugins?.replace(',uol-floatbar', '');
  // Fix errors from stud.ip's built-in event handlers which assume that the
  // shared spaces plugin is configured
  config.sharedSpaces = {};

  return config;
}