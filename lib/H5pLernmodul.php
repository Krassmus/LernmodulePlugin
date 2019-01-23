<?php

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
        $json = json_decode(file_get_contents($this->getPath() . "/h5p.json"), true);
        if ($json) {
            $libs_data = array();
            $lib_names = array();
            $editor_libs = array();

            foreach ($json['preloadedDependencies'] as $lib_data) {
                $lib_name = $lib_data['machineName']."-".$lib_data['majorVersion'].".".$lib_data['minorVersion'];
                if (!in_array($lib_name, $lib_names)) {
                    $lib_names[] = $lib_names;
                    $libs_data[] = $lib_data;

                    foreach ($this->getSubLibsAfterInstall($lib_name, $lib_names) as $sublib_data) {
                        $lib_name = $sublib_data['machineName']."-".$sublib_data['majorVersion'].".".$sublib_data['minorVersion'];
                        if (!in_array($lib_name, $lib_names)) {
                            $lib_names[] = $lib_names;
                            $libs_data[] = $sublib_data;
                        }
                    }
                }
            }

            foreach ($json['preloadedDependencies'] as $lib_data) {
                $dir_name = $lib_data['machineName']."-".$lib_data['majorVersion'].".".$lib_data['minorVersion'];
                $libs_data[$dir_name] = $lib_data;
                $lib_path = $this->getPath()."/".$lib_data['machineName']."-".$lib_data['majorVersion'].".".$lib_data['minorVersion'];
                if (file_exists($lib_path."/library.json")) {
                    $library_json = json_decode(file_get_contents($lib_path . "/library.json"), true);
                    foreach ((array) $library_json['preloadedDependencies'] as $lib_data2) {
                        $dir_name = $lib_data['machineName'] . "-" . $lib_data['majorVersion'] . "." . $lib_data['minorVersion'];
                        $libs_data[$dir_name] = $lib_data2;
                    }
                    //editorDependencies ?
                    foreach ((array) $library_json['editorDependencies'] as $lib_data2) {
                        $dir_name = $lib_data['machineName'] . "-" . $lib_data['majorVersion'] . "." . $lib_data['minorVersion'];
                        $editor_libs[$dir_name] = $lib_data2;
                    }
                    if ($library_json['runnable']) {
                        $libs_data[$dir_name]['runnable'] = 1;
                    }
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
                    $lib['allowed'] = $GLOBALS['perm']->have_perm("root") ? 1 : 0;
                    $lib['runnable'] = $lib_data['runnable'] ? 1 : 0;
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
    }

    public function getSubLibsAfterInstall($lib_name, $without_names = array())
    {
        $lib_path = $this->getPath() . "/" . $lib_name;
        $libs = array();
        if (file_exists($lib_path."/library.json")) {
            $library_json = json_decode(file_get_contents($lib_path . "/library.json"), true);
            foreach ((array) $library_json['preloadedDependencies'] as $lib_data2) {
                $sublib_name = $lib_data2['machineName'] . "-" . $lib_data2['majorVersion'] . "." . $lib_data2['minorVersion'];
                if (!in_array($sublib_name, $without_names)) {
                    $libs[] = $lib_data2;
                    $without_names[] = $sublib_name;
                    foreach ($this->getSubLibsAfterInstall($lib_name, $without_names) as $sublib) {
                        $libs[] = $sublib;
                        $sublib_name2 = $sublib['machineName'] . "-" . $sublib['majorVersion'] . "." . $sublib['minorVersion'];
                        $without_names[] = $sublib_name2;
                    }
                }
            }
            //editorDependencies ?
        }
    }

    public function getEditTemplate() {}

    public function getViewerTemplate($attempt, $game_attendance = null)
    {
        $actions = new ActionsWidget();
        $actions->addLink(
            _("Vollbild"),
            "#",
            Icon::create("play", "clickable"),
            array('onClick' => "STUDIP.Lernmodule.requestFullscreen(); return false;")
        );
        Sidebar::Get()->addWidget($actions);

        $templatefactory = new Flexi_TemplateFactory(__DIR__."/../views");
        $template = $templatefactory->open("h5p/view.php");
        $template->set_attribute("module", $this);
        return $template;
    }

    public function getEvaluationTemplate($course_id)
    {
        return null;
    }

    public function evaluateAttempt($attempt)
    {
        return null;
    }

    public function getLibs()
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
                    foreach ($lib->getSubLibs() as $sublib) {
                        if (!in_array($sublib->getId(), $lib_ids)) {
                            $lib_ids[] = $sublib->getId();
                            $libs[] = $sublib;
                        }
                    }
                }
            }
        }
        return $libs;
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

    public function getH5pLibURL()
    {
        if (Config::get()->LERNMODUL_DATA_URL) {
            return Config::get()->LERNMODUL_DATA_URL."/h5plibs";
        } else {
            return $GLOBALS['ABSOLUTE_URI_STUDIP'] . "plugins_packages/RasmusFuhse/LernmodulePluginData/h5plibs";
        }
    }
}