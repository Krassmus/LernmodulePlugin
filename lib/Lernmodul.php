<?php

class Lernmodul extends SimpleORMap {

    static public function findByCourse($course_id)
    {
        $statement = DBManager::get()->prepare("
            SELECT lernmodule_module.*
            FROM lernmodule_module
                INNER JOIN lernmodule_courses ON (lernmodule_module.module_id = lernmodule_courses.module_id)
            WHERE lernmodule_courses.seminar_id = :course_id
            ORDER BY lernmodule_module.name ASC
        ");
        $statement->execute(array('course_id' => $course_id));
        $module = array();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $module[] = Lernmodul::buildExisting($data);
        }
        return $module;
    }

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_module';
        $config['serialized_fields']['customdata'] = 'JSONArrayObject';
        $config['registered_callbacks']['after_delete'][] = 'cbDeleteModuleData';
        parent::configure($config);
    }

    public function courseConnection($course_id)
    {
        return new LernmodulCourse(array($this->getId(), $course_id));
    }

    public function copyModule($path, $filename = null)
    {
        if (file_exists($this->getPath())) {
            $success = rmdirr($this->getPath());
        }

        $success = mkdir($this->getPath());
        if (!$success) {
            PageLayout::postMessage(MessageBox::error(_("Konnte im Dateisystem keinen Ordner für das Lernmodul anlegen.")));
            return false;
        }


        $success = Studip\ZipArchive::extractToPath($path, $this->getPath());

        if ($success) {
            if (count(scandir($this->getPath())) < 4) {
                //Falls nur der Ordner auf oberster Ebene liegt:
                foreach (scandir($this->getPath()) as $folder) {
                    if (!in_array($folder, array(".", ".."))) {
                        break;
                    }
                }
                $tmp_folder = $this->getPath() . "/" . $this->getId();
                rename($this->getPath() . "/" . $folder, $tmp_folder);
                $this->copyr(
                    $tmp_folder,
                    $this->getPath()
                );
                rmdirr($tmp_folder);
            }
        } else {
            //falls ein PDF oder anderes Dokument hochgeladen wurde
            rename($path, $this->getPath() . "/" . ($filename ?: "index.html"));
        }


        foreach ($this->scanForFiletypes(array("php", "php3", "php1", "php2", "phtml", "asp"), null, true) as $php_file) {
            //remove all PHP-files
            @unlink($php_file);
        }

        if (!$this['image'] || !file_exists($this->getPath()."/".$this['image'])) {
            $images = $this->scanForImages();
            $this['image'] = $images[0];
        }

        if (file_exists($this->getPath() . "/h5p.json")) {
            $this['type'] = "h5p";
        } elseif(file_exists($this->getPath() . "/imsmanifest.xml")) {
            $this['type'] = "scorm";
        } else {
            $this['type'] = "html";
        }


        if (file_exists($this->getPath())) {
            $this['url'] = null;
            $this->store();

            $class = ucfirst($this['type'])."Lernmodul";
            $module = new $class($this->getId());
            $module->afterInstall();
        } else {
            PageLayout::postMessage(MessageBox::error(_("Verzeichnis konnte nicht angelegt werden.")));
            $this->delete();
            return false;
        }
        return $success;
    }

    public function cbDeleteModuleData()
    {
        rmdirr($this->getPath());
    }

    public function scanForImages()
    {
        return $this->scanForFiletypes(array("png", "jpg", "jpeg"));
    }

    public function scanForFiletypes($filetypes = array(), $path = null, $all = false)
    {
        if (!$path) {
            $path = $this->getPath();
            $reduce = strlen($path) + 1;
        }
        $files = array();
        if (file_exists($path)) {
            foreach (scandir($path) as $file) {
                if (!in_array($file, array(".", ".."))) {
                    if (!is_dir($path . "/" . $file)) {
                        if ($all || ($file[0] !== ".")) {
                            $file_part = array_pop(explode(".", $file));
                            if (in_array(strtolower($file_part), $filetypes)) {
                                $files[] = $path . "/" . $file;
                            }
                        }
                    }
                }
            }
            sort($files);
            foreach (scandir($path) as $file) {
                if (!in_array($file, array(".", ".."))) {
                    if (is_dir($path . "/" . $file)) {
                        if ($all || ($file[0] !== ".")) {
                            foreach ($this->scanForFiletypes($filetypes, $path . "/" . $file, $all) as $image) {
                                $files[] = $image;
                            }
                        }
                    }
                }
            }
        }
        if ($reduce) {
            foreach ($files as $key => $file) {
                $files[$key] = substr($file, $reduce);
            }
        }
        if ($GLOBALS['FILESYSTEM_UTF8']) {
            $files = studip_utf8decode($files);
        }
        return $files;
    }

    protected function copyr($source, $dest) {
        if(is_dir($source)) {
            $dir_handle=opendir($source);
            while($file=readdir($dir_handle)){
                if($file!="." && $file!=".."){
                    if(is_dir($source."/".$file)){
                        if(!is_dir($dest."/".$file)){
                            mkdir($dest."/".$file);
                        }
                        $this->copyr($source."/".$file, $dest."/".$file);
                    } else {
                        copy($source."/".$file, $dest."/".$file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            copy($source, $dest);
        }
    }

    public function getPath()
    {
        if (Config::get()->LERNMODUL_DATA_PATH) {
            $datafolder = Config::get()->LERNMODUL_DATA_PATH;
        } else {
            $datafolder = realpath(__DIR__."/../../LernmodulePluginData");
            if (!file_exists($datafolder)) {
                $success = mkdir($datafolder);
                if (!$success) {
                    throw new Exception("Konnte Verzeichnis LernmodulePluginData nicht anlegen.");
                }
            }
        }
        $datafolder .= "/moduledata";
        if (!file_exists($datafolder)) {
            $success = mkdir($datafolder);
            if (!$success) {
                throw new Exception("Konnte Verzeichnis LernmodulePluginData/moduledata nicht anlegen.");
            }
        }
        return $datafolder."/".$this->getId();
    }

    public function getDataURL()
    {
        if (Config::get()->LERNMODUL_DATA_URL) {
            return Config::get()->LERNMODUL_DATA_URL."/moduledata/".$this->getId();
        } else {
            return $GLOBALS['ABSOLUTE_URI_STUDIP'] . "plugins_packages/RasmusFuhse/LernmodulePluginData/moduledata/" . $this->getId();
        }
    }

    public function setDependencies($module_ids, $seminar_id)
    {
        LernmodulDependency::deleteBySQL("seminar_id = ? AND module_id = ?", array(
            $seminar_id,
            $this->getId()
        ));
        foreach ($module_ids as $module_id) {
            $dependency = new LernmodulDependency();
            $dependency['seminar_id'] = $seminar_id;
            $dependency['module_id'] = $this->getId();
            $dependency['depends_from_module_id'] = $module_id;
            $dependency->store();
        }
    }

    public function getDependencies($seminar_id)
    {
        return LernmodulDependency::findBySQL("module_id = ? AND seminar_id = ?", array(
            $this->getId(),
            $seminar_id
        ));
    }

    /**
     * unused
     * @param $course_id
     */
    public function addToCourse($course_id)
    {
        if (!$this->getId()) {
            $this->setId($this->getNewId());
        }
        $statement = DBManager::get()->prepare("
            INSERT IGNORE INTO lernmodule_courses
            SET seminar_id = :course_id,
                module_id = :module_id
        ");
        $statement->execute(array(
            'course_id' => $course_id,
            'module_id' => $this->getId()
        ));
    }

    public function isWritable()
    {
        if ($GLOBALS['perm']->have_perm("admin")) {
            return true;
        }
        $statement = DBManager::get()->prepare("
            SELECT 1
            FROM lernmodule_courses
                INNER JOIN seminar_user ON (lernmodule_courses.seminar_id = seminar_user.Seminar_id AND (seminar_user.status IN ('tutor', 'dozent')))
            WHERE seminar_user.user_id = :user_id
                AND lernmodule_courses.module_id = :module_id
            LIMIT 1
        ");
        $statement->execute(array(
            'module_id' => $this->getId(),
            'user_id' => $GLOBALS['user']->id
        ));
        return (bool) $statement->fetch();
    }

    public function getDownloadURL() {
        return URLhelper::getURL( "plugins.php/lernmoduleplugin/lernmodule/download/" . $this->getId());
    }


}