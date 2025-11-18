<?php

use Courseware\CoursewarePlugin;
use lib\CoursewareBlocks\DragTheWordsBlock;
use lib\CoursewareBlocks\FillInTheBlanksBlock;
use lib\CoursewareBlocks\FindTheHotspotsBlock;
use lib\CoursewareBlocks\FindTheWordsBlock;
use lib\CoursewareBlocks\InteractiveVideoBlock;
use lib\CoursewareBlocks\MarkTheWordsBlock;
use lib\CoursewareBlocks\MemoryBlock;
use lib\CoursewareBlocks\PairingBlock;
use lib\CoursewareBlocks\QuestionBlock;
use lib\CoursewareBlocks\SequencingBlock;

class LernmoduleCoursewareBlocksPlugin extends StudIPPlugin implements SystemPlugin,
                                                                       CoursewarePlugin
{
    public function __construct()
    {
        parent::__construct();

        require_once __DIR__ . '/lib/CoursewareBlocks/JsonSchemaTrait.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/LernmoduleBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/DragTheWordsBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/FillInTheBlanksBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/FindTheHotspotsBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/FindTheWordsBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/InteractiveVideoBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/MarkTheWordsBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/MemoryBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/PairingBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/QuestionBlock.php';
        require_once __DIR__ . '/lib/CoursewareBlocks/SequencingBlock.php';
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
        $cssRelativePath = '/courseware-blocks-vue2/assets';
        $cssDir = $this->getPluginPath() . $cssRelativePath;
        $cssFiles = array_filter(scandir($cssDir), function ($filename) {
            return str_ends_with($filename, '.css');
        });
        foreach ($cssFiles as $cssFile) {
            $url = $this->getPluginUrl() . $cssRelativePath . '/' . $cssFile;
            PageLayout::addStylesheet($url);
        }

        // Render a script tag which sets global javascript variables needed for the plugin to function
        $factory = new Flexi_TemplateFactory(dirname(__FILE__) . '/views');
        $template = $factory->open('courseware/set_global_variables');
        $template->set_attribute('plugin', $this);
        $script = $template->render();
        PageLayout::addBodyElements($script);

        // Add CSS to set the correct icons for the blocks in the block adder
        $this->addBlockIconCSS('drag-the-words', 'edit');
        $this->addBlockIconCSS('fill-in-the-blanks', 'file-office');
        $this->addBlockIconCSS('find-the-hotspots', 'block-imagemap2');
        $this->addBlockIconCSS('find-the-words', 'tan3');
        $this->addBlockIconCSS('lmb-interactive-video', 'file-video');
        $this->addBlockIconCSS('mark-the-words', 'guestbook');
        $this->addBlockIconCSS('memory', 'tan3');
        $this->addBlockIconCSS('pairing', 'copy');
        $this->addBlockIconCSS('question', 'question');
        $this->addBlockIconCSS('sequencing', 'picture');
        $this->addStylesheet('assets/courseware-block/icons-variables.scss');
    }

    /**
     * @param $blockType
     * @param $iconName
     * Add a CSS rule to the page to set the icon for the given block type in
     * the Courseware block picker.
     */
    function addBlockIconCSS($blockType, $iconName) {
        if (StudipVersion::olderThan('6.0')) {
            $baseCssSelector = '.cw-blockadder-item-list .cw-blockadder-item-wrapper .cw-blockadder-item';
            $icon = Icon::create($iconName);
            PageLayout::addStyle(
                $baseCssSelector . '.cw-blockadder-item-' . $blockType . ' {
            background-image:url(' . $icon->asImagePath() . ')
        }'
            );
        }
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
        $otherBlockTypes[] = DragTheWordsBlock::class;
        $otherBlockTypes[] = FillInTheBlanksBlock::class;
        $otherBlockTypes[] = FindTheHotspotsBlock::class;
        $otherBlockTypes[] = FindTheWordsBlock::class;
        $otherBlockTypes[] = InteractiveVideoBlock::class;
        $otherBlockTypes[] = MarkTheWordsBlock::class;
        $otherBlockTypes[] = MemoryBlock::class;
        $otherBlockTypes[] = PairingBlock::class;
        $otherBlockTypes[] = QuestionBlock::class;
        $otherBlockTypes[] = SequencingBlock::class;

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
