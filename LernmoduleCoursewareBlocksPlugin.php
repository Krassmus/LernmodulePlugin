<?php

use Courseware\CoursewarePlugin;

class LernmoduleCoursewareBlocksPlugin extends StudIPPlugin implements \SystemPlugin,
                                                                       CoursewarePlugin
{
    public function __construct()
    {
        parent::__construct();

        require_once __DIR__ . '/lib/CoursewareBlocks/FillInTheBlanksBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/MemoryBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/QuestionBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/DragTheWordsBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/MarkTheWordsBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/InteractiveVideoBlock.php';
        // TODO Consider using cache-busting hashes so the latest version of
        //   the JS/CSS will always be loaded.  Currently, the webpack build
        //   does not do this.
        $jsRelativePath = '/courseware-blocks-vue2/dist';
        $jsDir = $this->getPluginPath() . $jsRelativePath;
        $jsFiles = array_filter(scandir($jsDir), function ($filename) {
            // The webpack build of the courseware blocks generates both a minified and a
            // non-minified version of the script that registers the blocks' vue2 components
            return str_ends_with($filename, '.js')
                && !str_ends_with($filename, '.min.js');
        });
        foreach ($jsFiles as $jsFile) {
            $url = $this->getPluginUrl() . $jsRelativePath . '/' . $jsFile;
            PageLayout::addScript($url, ['type' => 'module']);
        }
        $cssFiles = array_filter(scandir($jsDir), function ($filename) {
            return str_ends_with($filename, '.css');
        });
        foreach ($cssFiles as $cssFile) {
            $url = $this->getPluginUrl() . $jsRelativePath . '/' . $cssFile;
            PageLayout::addStylesheet($url);
        }

        // Render a script tag which sets global javascript variables needed for the plugin to function
        $factory = new Flexi_TemplateFactory(dirname(__FILE__) . '/views');
        $template = $factory->open('courseware/set_global_variables');
        $template->set_attribute('plugin', $this);
        $script = $template->render();
        \PageLayout::addBodyElements($script);

        // Add CSS to set the correct icons for the blocks in the block adder
        $icon = Icon::create('tan3');
        \PageLayout::addStyle(
            '.cw-blockadder-item.cw-blockadder-item-mark-the-words {
            background-image:url(' . $icon->asImagePath() . ')
        }'
        );
        $icon = Icon::create('tan3');
        \PageLayout::addStyle(
            '.cw-blockadder-item.cw-blockadder-item-drag-the-words {
            background-image:url(' . $icon->asImagePath() . ')
        }'
        );
        $icon = Icon::create('question');
        \PageLayout::addStyle(
            '.cw-blockadder-item.cw-blockadder-item-question {
            background-image:url(' . $icon->asImagePath() . ')
        }'
        );
        $icon = Icon::create('file-office');
        \PageLayout::addStyle(
            '.cw-blockadder-item.cw-blockadder-item-fill-in-the-blanks {
            background-image:url(' . $icon->asImagePath() . ')
        }'
        );

        $icon = Icon::create('content2');
        \PageLayout::addStyle(
            '.cw-blockadder-item.cw-blockadder-item-memory {
            background-image:url(' . $icon->asImagePath() . ')
        }'
        );

        // Add CSS to make the vue3 courseware block's 'draggable' handle look right in Stud.IP 5.3
        \PageLayout::addStyle(
            '.cw-block-item-sortable > .cw-lernmodule-block {
               margin-top: -30px;
        }'
        );

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
        $otherBlockTypes[] = \CoursewareLernmoduleBlocks\QuestionBlock::class;
        $otherBlockTypes[] = \CoursewareLernmoduleBlocks\DragTheWordsBlock::class;
        $otherBlockTypes[] = \CoursewareLernmoduleBlocks\MarkTheWordsBlock::class;
//        $otherBlockTypes[] = \CoursewareLernmoduleBlocks\MemoryBlock::class;
//        $otherBlockTypes[] = \CoursewareLernmoduleBlocks\InteractiveVideoBlock::class;

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
