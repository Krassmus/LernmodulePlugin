<?php

class H5pController extends PluginController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    public function admin_libraries_action()
    {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
        Navigation::activateItem("/admin/locations/h5p");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5pstudip.js");
        PageLayout::setTitle(dgettext("lernmoduleplugin","H5P-Bibliotheken"));
        $this->libs = H5PLib::findBySQL("1 = 1 ORDER BY name ASC, major_version DESC, minor_version DESC");
    }

    public function activate_library_action()
    {
        if (!$GLOBALS['perm']->have_perm("root") || !Request::isPost()) {
            throw new AccessDeniedException();
        }
        $lib = H5PLib::find(Request::option("lib_id"));
        $lib['allowed'] = Request::int("allowed", 0);
        $lib->store();
        $this->render_text("ok");
    }

    public function simple_view_success_action()
    {
        if (!$GLOBALS['perm']->have_perm("root") || !Request::isPost()) {
            throw new AccessDeniedException();
        }
        $lib = H5PLib::find(Request::option("lib_id"));
        $lib['simple_view_success'] = Request::int("simple_view_success", 0);
        $lib->store();
        $this->render_text("ok");
    }

    public function add_library_action()
    {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            if ($_FILES['file']['size'] > 0) {
                $tmp_path = $GLOBALS['TMP_PATH']."/h5p_libs_".uniqid();
                $success = \Studip\ZipArchive::extractToPath($_FILES['file']['tmp_name'], $tmp_path);
                if ($success) {
                    $lib_paths = array();
                    if (file_exists($tmp_path ."/library.json")) {
                        //einzelne Bibliothek
                        $lib_paths[] = $tmp_path;
                    } else {
                        foreach (scandir($tmp_path) as $file) {
                            if (!in_array($file, array(".", ".."))
                                    && is_dir($tmp_path."/".$file)
                                    && file_exists($tmp_path."/".$file."/library.json")) {
                                $lib_paths[] = $tmp_path."/".$file;
                            }
                        }
                    }
                    $libs_name = array();
                    foreach ($lib_paths as $lib_path) {
                        $json = json_decode(file_get_contents($lib_path."/library.json"), true);
                        $lib_is_new = false;
                        $lib = H5PLib::findVersion($json['machineName'], $json['majorVersion'], $json['minorVersion']);
                        if (!$lib) {
                            $lib = new H5PLib();
                            $lib['name'] = $json['machineName'];
                            $lib['major_version'] = $json['majorVersion'];
                            $lib['minor_version'] = $json['minorVersion'];
                            $lib['patch_version'] = $json['patchVersion'];
                            $lib['allowed'] = Request::int("activate", 0);
                            $lib['runnable'] = $json['runnable'] ? 1 : 0;
                            $lib->store();
                            $lib_is_new = true;
                        }
                        if ((Request::get("overwrite") === "always")
                                || $lib_is_new
                                || ((Request::get("overwrite") === "nonactivated") && !$lib['allowed'])
                                || ((Request::get("overwrite") === "patch") && ($json['patchVersion'] > $lib['patch_version']))) {
                            //copy
                            if (file_exists($lib->getPath())) {
                                rmdirr($lib->getPath());
                            }
                            exec("mv ".escapeshellarg($lib_path)." ".escapeshellarg($lib->getPath()));
                            //rename($lib_path, $lib->getPath());
                            $lib['patch_version'] = $json['patchVersion'];
                            if (Request::int("activate", 0)) {
                                $lib['allowed'] = 1;
                            }
                            $lib->store();
                            $libs_name[] = $lib['name']." ".$lib['major_version'].".".$lib['minor_version'];
                        }
                    }

                    rmdirr($tmp_path);

                }
                unlink($_FILES['file']['tmp_name']);
                if (count($libs_name)) {
                    PageLayout::postSuccess(dgettext("lernmoduleplugin","H5P-Bibliotheken wurden übernommen"), $libs_name);
                } else {
                    PageLayout::postError(dgettext("lernmoduleplugin","Es konnten leider keine H5P-Bibliotheken in der Datei gefunden werden, die übernommen wurden."));
                }
                $this->redirect("h5p/admin_libraries");
            }
        }
    }

    public function export_libraries_action() {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }

        $archive = $GLOBALS['TMP_PATH']."/h5p_all_libs.zip";
        $zip = \Studip\ZipArchive::create($archive);
        $libs = H5PLib::findBySQL("allowed = '1'");
        foreach ($libs as $lib) {
            $zip->addFromPath($lib->getPath(), $lib['name']."-".$lib['major_version'].".".$lib['minor_version']."/");
        }
        $zip->close();

        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=\"H5PLibraries.zip\"");
        header("Content-Length: ".filesize($archive));
        echo file_get_contents($archive);
        unlink($archive);
        die();
    }

    public function export_library_action() {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
        list($name, $version) = explode("-", Request::get("lib"));
        $version = explode(".", $version);

        $lib = H5PLib::findOneBySQL("name = :name AND major_version = :major_version AND minor_version = :minor_version", [
            'name' => $name,
            'major_version' => $version[0],
            'minor_version' => $version[1]
        ]);
        $archive = $GLOBALS['TMP_PATH']."/h5p_lib_".$lib->getId().".zip";
        $zip = \Studip\ZipArchive::create($archive);
        $zip->addFromPath($lib->getPath(), $lib['name']."-".$lib['major_version'].".".$lib['minor_version']."/");
        foreach ($lib->getSubLibs([], true) as $sublib) {
            $zip->addFromPath($sublib->getPath(), $sublib['name']."-".$sublib['major_version'].".".$sublib['minor_version']."/");
        }
        $zip->close();

        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=\"".$lib['name']."-".$lib['major_version'].".".$lib['minor_version'].".zip\"");
        header("Content-Length: ".filesize($archive));
        echo file_get_contents($archive);
        unlink($archive);
        die();
    }

    public function delete_library_action()
    {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
        if (Request::get("lib")) {
            list($name, $version) = explode("-", Request::get("lib"));
            $version = explode(".", $version);

            $lib = H5PLib::findOneBySQL("name = :name AND major_version = :major_version AND minor_version = :minor_version", [
                'name' => $name,
                'major_version' => $version[0],
                'minor_version' => $version[1]
            ]);
            if ($lib) {
                $lib->delete();
                PageLayout::postSuccess(dgettext("lernmoduleplugin", "H5P-Bibliothek wurde gelöscht."));
            }
        } elseif (count(Request::getArray("libs"))) {
            foreach (Request::getArray("libs") as $libdata) {
                list($name, $version) = explode("-", $libdata);
                $version = explode(".", $version);

                $lib = H5PLib::findOneBySQL("name = :name AND major_version = :major_version AND minor_version = :minor_version", [
                    'name' => $name,
                    'major_version' => $version[0],
                    'minor_version' => $version[1]
                ]);
                if ($lib) {
                    $lib->delete();
                }
            }
            PageLayout::postSuccess(dgettext("lernmoduleplugin", "H5P-Bibliotheken wurden gelöscht."));
        }
        $this->redirect("h5p/admin_libraries");
    }



    public function iframe_action($module_id)
    {
        $this->set_layout(null);
        $this->mod = new H5pLernmodul($module_id);
        $this->attempt = new LernmodulAttempt(Request::get("a"));
        if (!$this->mod->isAllowed()) {
            $libs = array();
            foreach (H5PLib::findMany($this->mod->findUnallowedLibraries()) as $lib) {
                $libs[] = $lib['name']." ".$lib['major_version'].".".$lib['minor_version'];
            }
            PageLayout::postInfo(dgettext("lernmoduleplugin","Dieses H5P-Modul benutzt Bibliotheken, die noch nicht freigegeben sind."), $libs);
            $this->render_nothing();
            return;
        }

        $this->lang;
        $this->content = array(
            'id' => $module_id,
            'title' => $this->mod['name']
        );
        $this->styles = array(
            $this->plugin->getPluginURL().'/assets/h5p/h5p.css',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-confirmation-dialog.css',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-core-button.css'
        );
        $this->scripts = array(
            $this->plugin->getPluginURL().'/assets/h5p/jquery.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-event-dispatcher.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-x-api-event.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-x-api.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-content-type.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-confirmation-dialog.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-action-bar.js'
        );
        $this->integration = $this->get_h5p_settings();

        foreach ($this->mod->getCSSFiles() as $css) {
            $this->styles[] = H5PLernmodul::getH5pLibURL()."/".$css;
        }
        foreach ($this->mod->getJSFiles() as $js) {
            $this->scripts[] = H5PLernmodul::getH5pLibURL()."/".$js;
        }
    }

    public function get_h5p_settings()
    {
        $library = $this->mod->getMainLib();
        if (!$library) {
            throw new Exception("Module has no runnable library.");
        }
        $settings = array(
            'baseUrl' => $GLOBALS['ABSOLUTE_URI_STUDIP'],
            'url' => Config::get()->LERNMODUL_DATA_URL
                ? Config::get()->LERNMODUL_DATA_URL."/moduledata/".$this->mod->getId()
                : $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins_packages/RasmusFuhse/LernmodulePluginData/moduledata/".$this->mod->getId(),
            'postUserStatistics' => false,
            'ajax' => array(
                'setFinished' => URLHelper::getURL("plugins.php/lernmoduleplugin/h5p/set_finished/".$this->attempt->getId()),
                'contentUserData' => URLHelper::getURL("plugins.php/lernmoduleplugin/h5p/set_userdata/".$this->attempt->getId())
            ),
            'saveFreq' => 2,
            'siteUrl' => $GLOBALS['ABSOLUTE_URI_STUDIP'],
            'libraryUrl' => H5PLernmodul::getH5pLibURL(), //needed to fetch the library.json via ajax-request
            'l10n' => array(
                'H5P' => array(
                    'fullscreen' => dgettext("lernmoduleplugin","Vollbild"),
                    'disableFullscreen' => dgettext('lernmoduleplugin','Vollbild beenden'),
                    'download' => dgettext('lernmoduleplugin','Download'),
                    'copyrights' => dgettext('lernmoduleplugin','Nutzungsbedingung'),
                    'embed' => dgettext('lernmoduleplugin','Einbetten'),
                    'size' => dgettext('lernmoduleplugin','Größe'),
                    'showAdvanced' => dgettext('lernmoduleplugin','Zeige mehr'),
                    'hideAdvanced' => dgettext('lernmoduleplugin','Zuklappen'),
                    'advancedHelp' => dgettext('lernmoduleplugin','Include this script on your website if you want dynamic sizing of the embedded content:'),
                    'copyrightInformation' => dgettext('lernmoduleplugin','Rights of use'),
                    'close' => dgettext('lernmoduleplugin','Schließen'),
                    'title' => dgettext('lernmoduleplugin','Titel'),
                    'author' => dgettext('lernmoduleplugin','Author'),
                    'year' => dgettext('lernmoduleplugin','Jahr'),
                    'source' => dgettext('lernmoduleplugin','Quelle'),
                    'license' => dgettext('lernmoduleplugin','License'),
                    'thumbnail' => dgettext('lernmoduleplugin','Thumbnail'),
                    'noCopyrights' => dgettext('lernmoduleplugin','No copyright information available for this content.'),
                    'downloadDescription' => dgettext('lernmoduleplugin','Download this content as a H5P file.'),
                    'copyrightsDescription' => dgettext('lernmoduleplugin','View copyright information for this content.'),
                    'embedDescription' => dgettext('lernmoduleplugin','View the embed code for this content.'),
                    'h5pDescription' => dgettext('lernmoduleplugin','Visit H5P.org to check out more cool content.'),
                    'contentChanged' => dgettext('lernmoduleplugin','This content has changed since you last used it.'),
                    'startingOver' => dgettext("lernmoduleplugin","You'll be starting over."),
                    'by' => dgettext('lernmoduleplugin','von'),
                    'showMore' => dgettext('lernmoduleplugin','Mehr anzeigen'),
                    'showLess' => dgettext('lernmoduleplugin','Weniger anzeigen'),
                    'subLevel' => dgettext('lernmoduleplugin','Sublevel'),
                    'confirmDialogHeader' => dgettext('lernmoduleplugin','Aktion bestätigen'),
                    'confirmDialogBody' => dgettext('lernmoduleplugin','Wirklich fortfahren? Änderungen können nicht rückgängig gemacht werden.'),
                    'cancelLabel' => dgettext('lernmoduleplugin','Abbrechen'),
                    'confirmLabel' => dgettext('lernmoduleplugin','Bestätigen'),
                    'licenseU' => dgettext('lernmoduleplugin','Undisclosed'),
                    'licenseCCBY' => dgettext('lernmoduleplugin','Attribution'),
                    'licenseCCBYSA' => dgettext('lernmoduleplugin','Attribution-ShareAlike'),
                    'licenseCCBYND' => dgettext('lernmoduleplugin','Attribution-NoDerivs'),
                    'licenseCCBYNC' => dgettext('lernmoduleplugin','Attribution-NonCommercial'),
                    'licenseCCBYNCSA' => dgettext('lernmoduleplugin','Attribution-NonCommercial-ShareAlike'),
                    'licenseCCBYNCND' => dgettext('lernmoduleplugin','Attribution-NonCommercial-NoDerivs'),
                    'licenseCC40' => dgettext('lernmoduleplugin','4.0 International'),
                    'licenseCC30' => dgettext('lernmoduleplugin','3.0 Unported'),
                    'licenseCC25' => dgettext('lernmoduleplugin','2.5 Generic'),
                    'licenseCC20' => dgettext('lernmoduleplugin','2.0 Generic'),
                    'licenseCC10' => dgettext('lernmoduleplugin','1.0 Generic'),
                    'licenseGPL' => dgettext('lernmoduleplugin','General Public License'),
                    'licenseV3' => dgettext('lernmoduleplugin','Version 3'),
                    'licenseV2' => dgettext('lernmoduleplugin','Version 2'),
                    'licenseV1' => dgettext('lernmoduleplugin','Version 1'),
                    'licensePD' => dgettext('lernmoduleplugin','Public Domain'),
                    'licenseCC010' => dgettext('lernmoduleplugin','CC0 1.0 Universal (CC0 1.0) Public Domain Dedication'),
                    'licensePDM' => dgettext('lernmoduleplugin','Public Domain Mark'),
                    'licenseC' => dgettext('lernmoduleplugin','Copyright'),
                    'contentType' => dgettext('lernmoduleplugin','Inhaltstyp'),
                    'licenseExtras' => dgettext('lernmoduleplugin','License Extras'),
                    'changes' => dgettext('lernmoduleplugin','Changelog'),
                )
            ),
            'hubIsEnabled' => false,
            'reportingIsEnabled' => FALSE,
            'libraryConfig' => null, //$h5p->getLibraryConfig(),
            'crossorigin' => null,
        );

        $settings['user'] = array(
            'name' => $GLOBALS['user']->getFullName(),
            'mail' => $GLOBALS['user']->email
        );
        $libraryname = $library['name']." ".$library['major_version'].".".$library['minor_version'];
        $this->attempt;
        $userdata = $this->attempt['customdata'] ? $this->attempt['customdata']->getArrayCopy() : array();
        $userdata = $userdata['h5pstate'] ? json_encode((array) $userdata['h5pstate']) : "{}";
        $settings['contents'] = array(
            "cid-" . $this->mod->getId() => array(
                'library' => $libraryname, //name of the runnable library with space instead of minus
                'jsonContent' => file_get_contents($this->mod->getPath()."/content/content.json"),
                'fullscreen' => 1,
                'exportUrl' => null,
                'embedCode' => '<iframe src="">',
                'resizeCode' => "<script src=\"".$GLOBALS['ABSOLUTE_URI_STUDIP']."plugins_packages/RasmusFuhse/LernmodulePlugin/assets/h5p/h5p-resizer.js\" charset=\"UTF-8\"><\/script>",
                'url' => "",
                'contentUrl' => Config::get()->LERNMODUL_DATA_URL
                    ? Config::get()->LERNMODUL_DATA_URL."/moduledata/" .$this->mod->getId()."/content"
                    : $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins_packages/RasmusFuhse/LernmodulePluginData/moduledata/".$this->mod->getId()."/content",
                'title' => $this->mod['name'],
                'displayOptions' => array(
                    'frame' => true,
                    'export' => true,
                    'embed' => true,
                    'copyright' => true,
                    'icon' => true
                ),
                'metadata' => array(
                    'title' => $this->mod['name'],
                    'license' => "U"
                ),
                'contentUserData' => array(
                    array('state' => $userdata)
                )
            )
        );
        return $settings;
    }

    public function set_finished_action($attempt_id)
    {
        $this->attempt = LernmodulAttempt::find($attempt_id);
        if ($this->attempt['user_id'] !== $GLOBALS['user']->id) {
            throw new AccessDeniedException();
        }
        $this->attempt['chdate'] = time();
        $this->attempt['successful'] = 1;
        $this->attempt['customdata']['points']['score'] = Request::get("score");
        $this->attempt->store();
        $module_id = Request::option("contentId");
        $score = Request::get("score");
        $max_score = Request::get("maxScore");
        $opened_time = Request::int("opened");
        $finished_time = Request::int("finished");
        $time = Request::get("time");

        if (Context::get()->id) {
            $course_connection = $this->attempt->modul->courseConnection(Context::get()->id);
            if ($course_connection['gradebook_definition']) {
                $instance = \Grading\Instance::findOneBySQL("user_id = :user_id AND definition_id = :definition_id", array(
                    'user_id' => $GLOBALS['user']->id,
                    'definition_id' => $course_connection['gradebook_definition']
                ));
                if (!$instance) {
                    $instance = new \Grading\Instance();
                    $instance['user_id'] = $GLOBALS['user']->id;
                    $instance['definition_id'] = $course_connection['gradebook_definition'];
                    $instance['rawgrade'] = $max_score / $score;
                    $instance->store();
                } elseif ($course_connection['gradebook_rewrite']) {
                    $instance['rawgrade'] = $max_score / $score;
                    $instance->store();
                }

            }
        }

        $this->render_text("");
    }

    public function set_userdata_action($attempt_id) {
        $this->attempt = LernmodulAttempt::find($attempt_id);
        if ($this->attempt['user_id'] !== $GLOBALS['user']->id) {
            throw new AccessDeniedException();
        }
        $this->attempt['processed'] = 1;
        $answer = json_decode(Request::get("data"), true);
        $customdata = $this->attempt['customdata'] ? $this->attempt['customdata']->getArrayCopy() : array();
        $customdata['h5pstate'] = $answer;
        if ($answer['finished']) {
            $this->attempt['successful'] = 1;
            if (Context::get()->id) {
                $course_connection = $this->attempt->modul->courseConnection(Context::get()->id);
                if ($course_connection['gradebook_definition']) {
                    $instance = \Grading\Instance::findOneBySQL("user_id = :user_id AND definition_id = :definition_id", array(
                        'user_id' => $GLOBALS['user']->id,
                        'definition_id' => $course_connection['gradebook_definition']
                    ));
                    if (!$instance) {
                        $instance = new \Grading\Instance();
                        $instance['user_id'] = $GLOBALS['user']->id;
                        $instance['definition_id'] = $course_connection['gradebook_definition'];
                        $instance['rawgrade'] = 1;
                        $instance->store();
                    } elseif ($course_connection['gradebook_rewrite']) {
                        $instance['rawgrade'] = 1;
                        $instance->store();
                    }
                }
            }
        }
        $this->attempt['customdata'] = $customdata;
        $this->attempt['chdate'] = time();
        $this->attempt->store();
        $this->render_text("ok");

        // {"progress":3,"answers":{"corrects":3,"wrongs":0}}
        // {"questions: ["1", "2", ""],"progress":2, "version": 1}
        // {"questions: ["1", "2", "A finish condition"],"progress":2, "version": 1}
        // {"questions: ["1", "2", "A finish condition"],"progress":2, "finished": true, "version": 1}
        // {"answers": [ ... ]}
    }


    public function download_action($module_id) {
        $this->mod = new H5pLernmodul($module_id);

        $archive = $this->mod->getExportFile();

        header("Content-Type: application/h5p");
        header("Content-Disposition: attachment; filename=\"".$this->mod['name'].".h5p\"");
        header("Content-Length: ".filesize($archive));
        echo file_get_contents($archive);
        unlink($archive);
        die();
    }

    public function get_url_action($module_id)
    {
        $this->module = Lernmodul::find($module_id);
        if (!$this->module->isWritable()) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(dgettext("lernmoduleplugin","Direktlink zum Lernmodul"));
        $this->attempt = LernmodulAttempt::getByModule($this->module->getId());
    }

}
