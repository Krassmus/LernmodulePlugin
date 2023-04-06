<?php

use Courseware\CoursewarePlugin;

class LernmoduleCoursewareBlocksPlugin extends StudIPPlugin implements \SystemPlugin, CoursewarePlugin
{
    public function __construct()
    {
        parent::__construct();

        require_once __DIR__ . '/lib/CoursewareBlocks/FillInTheBlanksBlock.php';
        // TODO Set the correct script/css for Vite.  Using cache-busting hashes would be smart.
//        \PageLayout::addScript($this->getPluginUrl() . '/dist/courseware-lernmodule-blocks.umd.min.js');
//        \PageLayout::addStylesheet($this->getPluginURL() . '/dist/courseware-lernmodule-blocks.css');

        // Render a script tag which sets global javascript variables needed for the plugin to function
        // TODO I think this is not needed for the Lernmodule courseware blocks.
//        $factory = new Flexi_TemplateFactory(dirname(__FILE__) . '/views');
//        $template = $factory->open('mindmapeditor/set_global_variables');
//        $template->set_attribute('plugin', $this);
//        $script = $template->render();
//        \PageLayout::addBodyElements($script);
    }

    /**
     * Implement this method to register more block types.
     *
     * You get the current list of block types and must return an updated list
     * containing your own block types.
     *
     * @param array $otherBlockTypes the current list of block types
     *
     * @return array the updated list of block types
     */
    public function registerBlockTypes(array $otherBlockTypes): array
    {
        $otherBlockTypes[] = \CoursewareLernmoduleBlocks\FillInTheBlanksBlock::class;

        return $otherBlockTypes;
    }

    /**
     * Implement this method to register more container types.
     *
     * You get the current list of container types and must return an updated list
     * containing your own container types.
     *
     * @param array $otherContainerTypes the current list of container types
     *
     * @return array the updated list of container types
     */
    public function registerContainerTypes(array $otherContainerTypes): array
    {
        return $otherContainerTypes;
    }


}
