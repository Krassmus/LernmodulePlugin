export {};

// Extract a couple of useful types from @types/ckeditor4
export type CKEditorInstance = InstanceType<typeof window.CKEDITOR.editor>;
export type CKEditorConfig = typeof window.CKEDITOR.config;

// Declarations for variables passed from php to the client
declare global {
  interface Window {
    STUDIP: {
      wysiwyg: {
        replace: (element: Element) => void;
      };
      wysiwyg_enabled: boolean;
      INSTALLED_LANGUAGES: { [name: string]: InstalledLanguage };
      ABSOLUTE_URI_STUDIP: string;
      ASSETS_URL: string;
      CSRF_TOKEN: { name: string; value: string };
      LernmoduleVueJS: {
        infotext: string;
        module: {
          // customdata should be an instance of 'TaskDefinition', but this
          // should be checked via parsing at runtime.
          customdata: unknown;
          module_id: string;
          name: string;
        };
        block_id?: string;
        saveRoute: string;
        updateAttemptRoute: string;
      };
    };
  }
}

export interface InstalledLanguage {
  name: string;
  path: string;
  picture: string;
  selected: boolean;
}
