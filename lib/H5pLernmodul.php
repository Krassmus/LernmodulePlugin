<?php

use Studip\ZipArchive;

class H5pLernmodul extends Lernmodul implements CustomLernmodul
{
    static protected function configure($config = array())
    {
        $config['has_and_belongs_to_many']['libs'] = array(
            'class_name' => 'H5PLib',
            'thru_table' => 'lernmodule_h5plib_module',
            'order_by' => 'ORDER BY name ASC'
        );
        parent::configure($config);
    }

    static public function detect($path)
    {
        return true;
    }



    public function afterInstall()
    {
        //initializing new libraries?
        $libs_data = array();
        foreach (scandir($this->getPath()) as $file) {
            if (is_dir($this->getPath() . "/" . $file) && preg_match("/^(.*)\-(\d+)\.(\d+)$/", $file, $matches)) {
                $libs_data[] = array(
                    'machineName' => $matches[1],
                    'majorVersion' => $matches[2],
                    'minorVersion' => $matches[3]
                );
            }
        }

        foreach ($libs_data as $lib_data) {

            $lib = H5PLib::findVersion($lib_data['machineName'], $lib_data['majorVersion'], $lib_data['minorVersion']);
            $lib_path = $this->getPath() . "/" . $lib_data['machineName'] . "-" . $lib_data['majorVersion'] . "." . $lib_data['minorVersion'];
            $lib_json = json_decode(file_get_contents($lib_path . "/library.json"), true);
            if (!$lib && $lib_json) {

                $lib = new H5PLib();
                $lib['name'] = $lib_data['machineName'];
                $lib['major_version'] = $lib_data['majorVersion'];
                $lib['minor_version'] = $lib_data['minorVersion'];
                $lib['patch_version'] = $lib_json['patchVersion'];
                $lib['allowed'] = LernmodulePlugin::mayEditSandbox() ? 1 : 0;
                $lib['runnable'] = $lib_json['runnable'] ? 1 : 0;
                $lib->store();

                if (file_exists($lib_path) && !file_exists($lib->getPath())) {
                    rename($lib_path, $lib->getPath());
                }
            }
            if (file_exists($lib_path)) {
                rmdirr($lib_path);
            }

            $statement = DBManager::get()->prepare("
                INSERT IGNORE INTO lernmodule_h5plib_module
                SET module_id = :module_id,
                    lib_id = :lib_id
            ");
            $statement->execute(array(
                'module_id' => $this->getId(),
                'lib_id' => $lib->getId()
            ));
        }
    }

    public function getEditTemplate() {
        $actions = new ActionsWidget();
        $actions->addLink(
            dgettext("lernmoduleplugin","Im Editor bearbeiten"),
            URLHelper::getURL("plugins.php/lernmoduleplugin/h5peditor/edit/".$this->getId()),
            Icon::create("edit", "clickable")
        );
        Sidebar::Get()->addWidget($actions);
    }

    public function getViewerTemplate($attempt, $game_attendance = null)
    {
        $actions = new ActionsWidget();
        $actions->addLink(
            dgettext("lernmoduleplugin","Vollbild"),
            "#",
            Icon::create("play", "clickable"),
            array('onClick' => "STUDIP.Lernmodule.requestFullscreen(); return false;")
        );
        if ($GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            $actions->addLink(
                dgettext("lernmoduleplugin", "Im Editor bearbeiten"),
                URLHelper::getURL("plugins.php/lernmoduleplugin/h5peditor/edit/" . $this->getId()),
                Icon::create("edit", "clickable")
            );
            $actions->addLink(
                dgettext("lernmoduleplugin","URL kopieren"),
                URLHelper::getURL("plugins.php/lernmoduleplugin/h5p/get_url/".$this->getId()),
                Icon::create("code", "clickable"),
                array(
                    'data-dialog' => "size=auto"
                )
            );
        }
        Sidebar::Get()->addWidget($actions);

        if (!$this->isAllowed()) {
            $libs = array();
            foreach (H5PLib::findMany($this->findUnallowedLibraries()) as $lib) {
                $libs[] = $lib['name']." ".$lib['major_version'].".".$lib['minor_version'];
            }
            PageLayout::postInfo(dgettext("lernmoduleplugin","Dieses H5P-Modul benutzt Bibliotheken, die noch nicht freigegeben sind."), $libs);
            return "";
        } else {
            $templatefactory = new Flexi_TemplateFactory(__DIR__ . "/../views");
            $template = $templatefactory->open("h5p/view.php");
            $template->set_attribute("attempt", $attempt);
            $template->set_attribute("module", $this);
            return $template;
        }
    }

    public function getEvaluationTemplate($course_id)
    {
        return null;
    }

    public function evaluateAttempt($attempt)
    {
        return null;
    }

    public function getLibs($editor = false)
    {
        $json = json_decode(file_get_contents($this->getPath() . "/h5p.json"), true);
        $libs = array();
        $lib_ids = array();
        if ($json) {
            foreach ($json['preloadedDependencies'] as $lib_data) {
                $lib = H5PLib::findVersion($lib_data['machineName'], $lib_data['majorVersion'], $lib_data['minorVersion']);
                if ($lib && !in_array($lib->getId(), $lib_ids)) {
                    $lib_ids[] = $lib->getId();
                    $libs[] = $lib;
                    foreach ($lib->getSubLibs(array(), $editor) as $sublib) {
                        if (!in_array($sublib->getId(), $lib_ids)) {
                            $lib_ids[] = $sublib->getId();
                            array_unshift($libs, $sublib);
                        }
                    }
                }
            }
        }

        //We need to sort these libs in order of their dependencies: (why don't they just rely on onLoad events?)
        //We need a topological sort algorithm for that, because the set of libraries is a non well ordered list.
        $lib_ids = array_map(function ($l) { return $l->getId(); }, $libs);
        $edges = array();
        foreach ($libs as $lib) {
            foreach ($lib->getSubLibs(array(), $editor) as $sublib) {
                $edges[] = array(
                    $sublib->getId(),
                    $lib->getId()
                );
            }
        }
        $lib_ids = LernmodulePlugin::topologicalSort($lib_ids, $edges);
        if ($lib_ids === false) {
            throw new Exception("Could not sort dependencies of H5P module.");
        }
        $libs_sorted = array();
        foreach ($lib_ids as $lib_id) {
            foreach ($libs as $i => $lib) {
                if ($lib->getId() == $lib_id) {
                    $libs_sorted[] = $lib;
                    unset($libs[$i]);
                    break;
                }
            }
        }

        return $libs_sorted;
    }

    /**
     * @param $css_or_jss : "css" or "js"
     * @return array
     * @throws Exception
     */
    protected function getFiles($css_or_jss)
    {
        $files = array();
        foreach ($this->getLibs() as $lib) {
            $files = array_merge($files, $lib->getFiles($css_or_jss));
        }
        return $files;
    }

    public function getJSFiles()
    {
        return $this->getFiles("js");
    }

    public function getCSSFiles()
    {
        return $this->getFiles("css");
    }

    static public function getH5pLibURL()
    {
        if (Config::get()->LERNMODUL_DATA_URL) {
            return Config::get()->LERNMODUL_DATA_URL."/h5plibs";
        } else {
            return $GLOBALS['ABSOLUTE_URI_STUDIP'] . "plugins_packages/RasmusFuhse/LernmodulePluginData/h5plibs";
        }
    }

    public function isAllowed() {
        return count($this->findUnallowedLibraries()) == 0;
    }

    public function findUnallowedLibraries() {
        $statement = DBManager::get()->prepare("
            SELECT lernmodule_h5plib_module.lib_id
            FROM lernmodule_h5plib_module
                INNER JOIN lernmodule_h5plibs ON (lernmodule_h5plibs.lib_id = lernmodule_h5plib_module.lib_id)
            WHERE lernmodule_h5plibs.allowed = '0'
                AND lernmodule_h5plib_module.module_id = :module_id
        ");
        $statement->execute(array('module_id' => $this->getId()));
        $disallowed = $statement->fetchAll(PDO::FETCH_COLUMN);
        return $disallowed;
    }

    public function getDownloadURL() {
        return URLhelper::getURL( "plugins.php/lernmoduleplugin/h5p/download/" . $this->getId());
    }

    public function getMainLib() {
        if (!file_exists($this->getPath() . "/h5p.json")) {
            return null;
        }
        $json = json_decode(file_get_contents($this->getPath() . "/h5p.json"), true);
        $main_lib_name = $json['mainLibrary'];
        foreach ($json['preloadedDependencies'] as $lib_data) {
            if ($lib_data['machineName'] === $main_lib_name) {
                $main_lib_major_version = $lib_data['majorVersion'];
                $main_lib_minor_version = $lib_data['minorVersion'];
                break;
            }
        }
        if (isset($main_lib_major_version)) {
            return H5PLib::findVersion($main_lib_name, $main_lib_major_version, $main_lib_minor_version);
        } else {
            return H5PLib::findOneBySQL("name = ? ORDER BY major_version DESC, minor_version DESC", array($main_lib_name));
        }
    }

    public function updateH5PData($data, $mainlib)
    {
        if ($mainlib && $data && $data['params'] && $data['metadata']['title']) {
            $this['name'] = $data['metadata']['title'];
            $this['user_id'] = $GLOBALS['user']->id;
            $this['type'] = "h5p";
            $this->store();

            $path = $this->getPath();
            if (!file_exists($path)) {
                mkdir($path);
            }

            if (!file_exists($path . "/content")) {
                mkdir($path . "/content");
            }
            if (file_exists($path . "/content/content.json")) {
                unlink($path . "/content/content.json");
            }
            $content = json_encode($data['params']);
            if (file_exists($this->getPath()."/content/assets")) { //delete all old images
                foreach (scandir($this->getPath()."/content/assets") as $file) {
                    $file_ecaped = str_replace(array("/", "\""), array("\\/", "\\\""), "assets/".$file); //json_encode-escaping for one string
                    if ($file !== "." && $file !== ".." && strpos($content, $file_ecaped) === false) {
                        @unlink($this->getPath()."/content/assets/".$file);
                    }
                }
            }
            file_put_contents($path . "/content/content.json", $content);
            $h5p = array(
                'title' => $data['metadata']['title'],
                'language' => $data['metadata']['language'] ?: "und",
                'mainLibrary' => $mainlib['name'],
                'embedTypes' => array("div"),
                'license' => $data['metadata']['license'] ?: "U",
                'preloadedDependencies' => array(
                    array(
                        'machineName' => $mainlib['name'],
                        'majorVersion' => $mainlib['major_version'],
                        'minorVersion' => $mainlib['minor_version']
                    )
                )
            );
            if ($data['metadata']['authors']) {
                $h5p['authors'] = $data['metadata']['authors'];
            }
            if ($data['metadata']['changes']) {
                $h5p['changes'] = $data['metadata']['changes'];
            }
            if ($data['metadata']['yearFrom']) {
                $h5p['yearFrom'] = $data['metadata']['yearFrom'];
            }
            if ($data['metadata']['yearTo']) {
                $h5p['yearTo'] = $data['metadata']['yearTo'];
            }
            if ($data['metadata']['source']) {
                $h5p['source'] = $data['metadata']['source'];
            }
            if ($data['metadata']['licenseExtras']) {
                $h5p['licenseExtras'] = $data['metadata']['licenseExtras'];
            }
            if (file_exists($path . "/h5p.json")) {
                unlink($path . "/h5p.json");
            }
            file_put_contents($path . "/h5p.json", json_encode($h5p));
            return true;
        }
        return false;
    }


    public function getExportFile()
    {
        $filename = $GLOBALS['TMP_PATH']."/".md5(uniqid()) . ".h5p.zip";
        $zip = \Studip\ZipArchive::create($filename);
        $zip->addFromPath($this->getPath());
        foreach ($this->libs as $lib) {
            $zip->addFromPath($lib->getPath(), $lib['name']."-".$lib['major_version'].".".$lib['minor_version']."/");
        }
        $zip->close();

        return $filename;
    }
}
