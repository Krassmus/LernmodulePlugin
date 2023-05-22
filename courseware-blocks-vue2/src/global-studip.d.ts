/**
 * Global variables used to communicate with Stud.IP
 */
interface Window {
  STUDIP: {
    eventBus: {
      on(event: string, callback: (pluginManager: PluginManager) => void): void;
    };
  };
}

interface PluginManager {
  // Why do we have to import the Component type this way?  Here is why...
  // https://stackoverflow.com/a/51114250/7359454
  addBlock(blockName: string, blockComponent: import('vue').Component): void;
}
