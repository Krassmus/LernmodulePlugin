/*
 Notice to future editors:
 You must not put any top-level imports into this file, or the globally defined
 types here will cease to function.
 See https://stackoverflow.com/a/51114250/7359454
*/

/**
 * Global variables used to communicate with Stud.IP
 */
interface Window {
  STUDIP: {
    eventBus: {
      on(event: string, callback: (pluginManager: PluginManager) => void): void;
    };
    LernmoduleCoursewareBlocksPlugin: {
      editorUrl: string;
    };
  };
}

interface PluginManager {
  // Why do we have to import the Component type this way?  Here is why...
  addBlock(blockName: string, blockComponent: import('vue').Component): void;
}

interface LernmoduleBlock {
  id: string;
  relationships: {
    container: {
      data: {
        id: string;
      };
    };
  };
  attributes: {
    payload: {
      initialized: boolean;
    };
  };
}

interface CoursewarePluginComponents {
  CoursewareBlockAdderArea: import('vue').Component;
  CoursewareCollapsibleBox: import('vue').Component;
  CoursewareCompanionBox: import('vue').Component;
  CoursewareDefaultBlock: import('vue').Component;
  CoursewareDefaultContainer: import('vue').Component;
  CoursewareFileChooser: import('vue').Component;
  CoursewareTabs: import('vue').Component;
  CoursewareTab: import('vue').Component;
}

interface CoursewareStore {
  dispatch(
    action: 'updateBlockInContainer',
    payload: {
      attributes: LernmoduleBlock['attributes'];
      blockId: LernmoduleBlock['id'];
      containerId: LernmoduleBlock['relationships']['container']['data']['id'];
    }
  ): Promise<void>;
}
