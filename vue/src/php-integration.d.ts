import { Editor } from '@ckeditor/ckeditor5-core';

export {};

// Declarations for variables passed from php to the client
declare global {
  interface Window {
    STUDIP: {
      USER_ID: string;
      URLHelper: {
        getURL: (
          path: string,
          param_object?: any,
          ignore_params?: boolean
        ) => string;
      };
      wysiwyg: {
        replace: (element: Element) => void;
        // TODO Use correct type for ckeditor5 editor instance
        getEditor: (
          element: Element
        ) => (Editor & { getData(): string }) | undefined;
      };
      wysiwyg_enabled: boolean;
      INSTALLED_LANGUAGES: { [name: string]: InstalledLanguage };
      ABSOLUTE_URI_STUDIP: string;
      ASSETS_URL: string;
      CSRF_TOKEN: { name: string; value: string };
      LernmoduleVueJS: {
        infotext: string;
        module: {
          // customdata should be an instance of 'TaskDefinition', but that
          // should be checked via parsing at runtime. That's why customdata is
          // annotated as type 'unknown'.
          customdata: unknown;
          module_id: string;
          name: string;
        };
        block_id?: string;
        saveRoute: string;
        updateAttemptRoute: string;
        LERNMODULE_DEBUG: boolean;
      };
    };
    // TODO use correct types for jQuery
    $: any;
  }
}

export interface InstalledLanguage {
  name: string;
  path: string;
  picture: string;
  selected: boolean;
}
