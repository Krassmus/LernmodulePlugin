/*
 This file contains type declarations for global variables defined in Stud.IP
 and in our plugin.

 Notice:
 Normally, it would be a .d.ts file, but I could not figure out how to get
 @juit/vue-ts-checker to detect it, so I have renamed it to be a .ts file.
 That is a bit unconventional and means that we have to import this file by
 hand somewhere for the type declarations to take effect.
 I have chosen to import it in editor-main.ts.
*/

// Ensure that this file will be treated as a module by Typescript.
// See https://stackoverflow.com/a/56577324/7359454
export {};

declare global {
  interface Window {
    STUDIP: {
      ABSOLUTE_URI_STUDIP: string;
      ASSETS_URL: string;
      CSRF_TOKEN: { name: string; value: string };
      LernmoduleVueJS: {
        module: {
          // customdata should be an instance of 'TaskDefinition', but this
          // should be checked via parsing at runtime.
          customdata: unknown;
          module_id: string;
          name: string;
        };
        saveRoute: string;
        block_id?: string;
      };
    };
  }
}
