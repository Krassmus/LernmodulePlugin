<?php

use Studip\ZipArchive;

class Lernmodul extends SimpleORMap
{

    static public function find($module_id)
    {
        $module = parent::find($module_id);
        if (!$module) {
            return $module;
        }
        $class = ucfirst($module['type'])."Lernmodul";
        if (class_exists($class)) {
            return $class::buildExisting($module->toRawArray());
        } else {
            return $module;
        }
    }

    static public function findByCourse($course_id)
    {
        $statement = DBManager::get()->prepare("
            SELECT lernmodule_module.*
            FROM lernmodule_module
                INNER JOIN lernmodule_courses ON (lernmodule_module.module_id = lernmodule_courses.module_id)
            WHERE lernmodule_courses.seminar_id = :course_id
                AND draft = '0'
            ORDER BY lernmodule_module.name ASC
        ");
        $statement->execute(array('course_id' => $course_id));
        $module = array();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $module[] = Lernmodul::buildExisting($data);
        }
        return $module;
    }

    static public function createCopyFromModule(Lernmodul $module)
    {
        $class = ucfirst($module['type'])."Lernmodul";
        $lernmodul = new $class();
        $lernmodul->setData($module->toRawArray());
        $lernmodul->setId($lernmodul->getNewId());
        mkdir($lernmodul->getPath());
        foreach (scandir($module->getPath()) as $file) {
            if (!in_array($file, ['.', '..'])) {
                $lernmodul->copyr(
                    $module->getPath()."/".$file,
                    $lernmodul->getPath()."/".$file
                );
            }
        }
        $lernmodul->store();
        return $lernmodul;
    }

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_module';
        $config['has_many']['lernmodulcourses'] = [
            'class_name' => 'LernmodulCourse',
            'on_delete'  => 'delete',
            'on_store'   => 'store'
        ];
        $config['serialized_fields']['customdata'] = 'JSONArrayObject';
        $config['registered_callbacks']['after_delete'][] = 'cbDeleteModuleData';
        parent::configure($config);
    }

    /**
     * @throws AccessDeniedException The 'Lernmodul' class claims to have a "has_many" relation to
     *  LernmodulCourse, but I have been unable to find a scenario in which a Lernmodul is assigned to
     *  multiple instances of LernmodulCourse. To be safe, this method throws an exception if a Lernmodul
     *  is supplied that belongs to 0 or multiple LernmodulCourses.
     */
    static public function mayAccess(?User $user, Lernmodul $sorm): bool {
        return self::hasPermInLernmodulCourse('autor', $user, $sorm);
    }

    /**
     * @param string $perm
     * @param User|null $user
     * @param Lernmodul $sorm
     * @return bool True iff user has the given permission in the course corresponding to this Lernmodul.
     * @throws AccessDeniedException The 'Lernmodul' class claims to have a "has_many" relation to
     * LernmodulCourse, but I have been unable to find a scenario in which a Lernmodul is assigned to
     * multiple instances of LernmodulCourse. To be safe, this method throws an exception if a Lernmodul
     * is supplied that belongs to 0 or multiple LernmodulCourses.
     */
    static protected function hasPermInLernmodulCourse(string $perm, ?User $user, Lernmodul $sorm) {
        $courses = LernmodulCourse::findBySQL(
            "module_id = ?",
            [$sorm->module_id]);
        if (count($courses) === 0) {
            throw new AccessDeniedException('The given Lernmodul does not belong to a course, ' .
            'so permission cannot be determined.');
        } else if (count($courses) > 1) {
            throw new AccessDeniedException('The given Lernmodul belongs to multiple courses, ' .
            'so permission cannot be determined.');
        }
        $course = $courses[0];
        $user_id = $user ? $user->id : false;
        return Seminar_Perm::get()->have_studip_perm($perm, $course->seminar_id, $user_id);
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
            PageLayout::postMessage(MessageBox::error(dgettext("lernmoduleplugin","Konnte im Dateisystem keinen Ordner für das Lernmodul anlegen.")));
            return false;
        }


        $success = Studip\ZipArchive::extractToPath($path, $this->getPath());

        if ($success) {
            // Die hochgeladene Datei müsste eine Zip-Datei sein, denn sie konnte
            // unzipped werden.
            if (count(scandir($this->getPath())) < 4) {
                // Hier wird mit einer möglichen Variante des H5P-Dateiformats umgegangen.
                // Es kann sein, dass in der Zip-Datei auf oberste Ebene nichts
                // zu finden ist, außer ein Verzeichnis, wo die Inhalte des Moduls drin sind.
                // Diese Inhalte sollen dann auf $this->getPath() entpackt/verschoben werden.
                foreach (scandir($this->getPath()) as $folder) {
                    if (!in_array($folder, array(".", ".."))) {
                        break;
                    }
                }
                // (Nur wenn $folder wirklich ein Verzeichnis ist)
                // Verschiebe die Inhalte des Verzeichnisses auf $this->getPath().
                if (is_dir($folder)) {
                    $tmp_folder = $this->getPath() . "/" . $this->getId();
                    rename($this->getPath() . "/" . $folder, $tmp_folder);
                    $this->copyr(
                        $tmp_folder,
                        $this->getPath()
                    );
                    rmdirr($tmp_folder);
                }
            }
        } else {
            // Es wurde kein Zip hochgeladen, oder die Zip-Datei konnte nicht extrahiert werden.
            // Wir gehen mal davon aus, dass ein PDF oder ein anderes Dokument hochgeladen wurde.
            rename($path, $this->getPath() . "/" . ($filename ?: "index.html"));
        }

        foreach ($this->scanForFiletypes(array("php", "php3", "php1", "php2", "phtml", "asp", "pl", "py"), null, true) as $php_file) {
            //remove all PHP-files
            @unlink($this->getPath()."/".$php_file);
        }

        if (!$this['image'] || !file_exists($this->getPath()."/".$this['image'])) {
            $images = $this->scanForImages();
            $this['image'] = $images[0];
        }

        if (file_exists($this->getPath() . "/h5p.json")) {
            $this['type'] = "h5p";
        } else if(file_exists($this->getPath() . "/task_definition.json")) {
            $this['type'] = 'vuejs';
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
            PageLayout::postMessage(MessageBox::error(dgettext("lernmoduleplugin","Verzeichnis konnte nicht angelegt werden.")));
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
        $reduce = null;
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
                            $explosion = explode(".", $file);
                            $file_part = array_pop($explosion);
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
        return $files;
    }

    public function copyr($source, $dest) {
        if (is_dir($source)) {
            $dir_handle = opendir($source);
            if (!is_dir($dest)) {
                mkdir($dest);
            }
            while ($file = readdir($dir_handle)) {
                if ($file !== "." && $file !== "..") {
                    if (is_dir($source."/".$file)) {
                        if (!is_dir($dest."/".$file)) {
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
            $datafolder = realpath(__DIR__."/../..")."/LernmodulePluginData";
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
        if ($this['url']) {
            return false;
        } else {
            return URLhelper::getURL("plugins.php/lernmoduleplugin/lernmodule/download/" . $this->getId());
        }
    }

    public function getExportFile()
    {
        $filename = $GLOBALS['TMP_PATH']."/".md5(uniqid()) . ".zip";
        /**
        $zip = new Studip\ZipArchive();
        if (!$zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new FileArchiveManagerException('Error opening new ZIP archive!');
        }
        $zip->setOutputEncoding('UTF-8');
        */
        $zip = \Studip\ZipArchive::create($filename);
        $zip->addFromPath($this->getPath());
        $zip->close();
        return $filename;
    }


}
