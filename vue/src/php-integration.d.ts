export {};
// Declarations for variables passed from php to the client
declare global {
  interface Window {
    STUDIP: {
      ABSOLUTE_URI_STUDIP: string;
      ASSETS_URL: string;
      CSRF_TOKEN: { name: string; value: string };
      LernmoduleVueJS: {
        saveRoute: string;
        module_id: string;
        block_id?: string;
        // moduleContents should be an instance of 'TaskDefinition', but this
        // should be checked via parsing at runtime.
        moduleContents: unknown;
      };
    };
  }
}
