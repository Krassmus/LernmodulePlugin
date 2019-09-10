<?php

class H5PLib extends SimpleORMap
{
    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_h5plibs';
        parent::configure($config);
    }

    static public function findVersion($name, $major_version, $minor_version)
    {
        return self::findOneBySQL("name = :name AND major_version = :major AND minor_version = :minor", array(
            'name' => $name,
            'major' => $major_version,
            'minor' => $minor_version
        ));
    }

    public function getPath()
    {
        if (Config::get()->LERNMODUL_DATA_PATH) {
            $datafolder = Config::get()->LERNMODUL_DATA_PATH;
        } else {
            $datafolder = realpath(__DIR__."/../../..")."/LernmodulePluginData";
            if (!file_exists($datafolder)) {
                $success = mkdir($datafolder);
                if (!$success) {
                    throw new Exception("Konnte Verzeichnis LernmodulePluginData nicht anlegen.");
                }
            }
        }
        $datafolder .= "/h5plibs";
        if (!file_exists($datafolder)) {
            $success = mkdir($datafolder);
            if (!$success) {
                throw new Exception("Konnte Verzeichnis LernmodulePluginData/h5plibs nicht anlegen.");
            }
        }
        return $datafolder."/".$this['name']."-".$this['major_version'].".".$this['minor_version'];
    }

    public function countUsage()
    {
        $statement = DBManager::get()->prepare("
            SELECT COUNT(*) 
            FROM lernmodule_h5plib_module
            WHERE lib_id = ?
        ");
        $statement->execute(array($this->getId()));
        return $statement->fetch(PDO::FETCH_COLUMN, 0);
    }

    public function getSubLibs($not_in_ids = array(), $editor = false)
    {
        $library_json = $this->getPath()."/library.json";
        $sublibs = array();
        if (file_exists($library_json)) {
            $json = json_decode(file_get_contents($library_json), true);
            $dependencies = (array) $json['preloadedDependencies'];
            if ($editor) {
                $dependencies = array_merge($dependencies, (array) $json['editorDependencies']);
            }
            foreach ($dependencies as $dependency) {
                $sublib = H5PLib::findVersion(
                    $dependency['machineName'],
                    $dependency['majorVersion'],
                    $dependency['minorVersion']
                );
                if ($sublib && !in_array($sublib->getId(), $not_in_ids)) {
                    array_unshift($sublibs, $sublib);
                    $not_in_ids[] = $sublib->getId();
                    foreach ($sublib->getSubLibs($not_in_ids, $editor) as $subsublib) {
                        array_unshift($sublibs, $subsublib);
                        $not_in_ids[] = $subsublib->getId();
                    }
                }
            }
            $semantics_json = $this->getPath()."/semantics.json";


            if (file_exists($semantics_json)) {

                $semantics = json_decode(file_get_contents($semantics_json), true);
                $more_libs = $this->fetchSemanticsLibraries($semantics, $not_in_ids);
                foreach ($more_libs as $lib) {
                    $sublibs[] = $lib;
                    $not_in_ids[] = $lib->getId();
                }
            }

        }

        return $sublibs;
    }


    protected function fetchSemanticsLibraries($fields, $not_in_ids = array()) {
        $libs = array();

        foreach ($fields as $semantic) {
            if ($semantic['type'] === "library") {

                foreach ($semantic['options'] as $lib_name) {
                    preg_match("/^(.+)\s+(\d+)\.(\d+)$/", $lib_name, $matches);
                    $sublib = H5PLib::findVersion(
                        $matches[1],
                        $matches[2],
                        $matches[3]
                    );
                    if ($sublib && !in_array($sublib->getId(), $not_in_ids)) {
                        //array_unshift($libs, $sublib);
                        $libs[] = $sublib;
                        $not_in_ids[] = $sublib->getId();
                        foreach ($sublib->getSubLibs($not_in_ids) as $subsublib) {
                            //array_unshift($libs, $subsublib);
                            $libs[] = $subsublib;
                            $not_in_ids[] = $subsublib->getId();
                        }
                    }
                }
            }
            if (isset($semantic['fields']) && count($semantic['fields'])) {
                foreach ($this->fetchSemanticsLibraries($semantic['fields'], $not_in_ids) as $lib) {
                    //array_unshift($libs, $lib);
                    $libs[] = $lib;
                    $not_in_ids[] = $lib->getId();
                }
            } elseif(isset($semantic['field']) && count($semantic['field'])) {
                foreach ($this->fetchSemanticsLibraries(array($semantic['field']), $not_in_ids) as $lib) {
                    //array_unshift($libs, $lib);
                    $libs[] = $lib;
                    $not_in_ids[] = $lib->getId();
                }
            }
        }
        return $libs;
    }

    /**
     * @param $css_or_jss : "js" oder "css"
     * @param bool $editor : are these files for the editor?
     * @return array
     * @throws Exception
     */
    public function getFiles($css_or_jss, $editor = false)
    {
        $library_json = $this->getPath()."/library.json";
        $files = array();
        $dir_name = $this['name'] . "-" . $this['major_version'] . "." . $this['minor_version'];
        if (file_exists($library_json)) {
            $library_json = json_decode(file_get_contents($library_json), true);
            foreach ((array) $library_json[$css_or_jss === "js" ? 'preloadedJs' : "preloadedCss"] as $file) {
                $files[] = $dir_name . "/" . $file['path'];
            }
        }
        if ($editor && ($css_or_jss === "js") && (file_exists($this->getPath()."/presave.js"))) {
            $files[] = $dir_name . "/presave.js";
        }
        return $files;
    }

    public function getLibraryData() {
        $library_json = $this->getPath()."/library.json";
        return json_decode(file_get_contents($library_json), true);
    }

    public function createModWithData($data) {
        //{"params":{"timeline":{"defaultZoomLevel":"0","height":600,"asset":{},"date":[{"asset":{},"startDate":"1623,09,10","endDate":"1640,02,08","headline":"Murad IV","text":"<p>Brutaler Sultan t�tet seine B�rger mit der Arkebuse. Ist halt sein gutes Recht, ne?</p>\n"}],"language":"en","headline":"Passierte das wirklich?","text":""}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"Sultan Murad 4.","title":"Sultan Murad 4."}}
        if ($data && $data['params'] && $data['metadata']['title']) {
            $mod = new H5PLernmodul();
            $mod['name'] = $data['metadata']['title'];
            $mod['user_id'] = $GLOBALS['user']->id;
            $mod['type'] = "h5p";
            $mod->store();

            $path = $mod->getPath();
            if (!file_exists($path)) {
                mkdir($path);
            }

            mkdir($path . "/content");
            file_put_contents($path . "/content/content.json", json_encode($data['params']));
            $h5p = array(
                'title' => $data['metadata']['title'],
                'language' => $data['metadata']['language'],
                'mainLibrary' => $this['name'],
                'embedTypes' => array("div"),
                'license' => $data['metadata']['license'] ?: "U",
                'preloadedDependencies' => array(
                    array(
                        'machineName' => $this['name'],
                        'majorVersion' => $this['major_version'],
                        'minorVersion' => $this['minor_version']
                    )
                )
            );
            file_put_contents($path . "/h5p.json", $h5p);
            return $mod;
        }
        return false;
    }
}