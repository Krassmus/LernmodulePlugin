<?php

require_once __DIR__."/../vendor/h5p-php-library/h5p.classes.php";

class H5pController extends PluginController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    public function editor_action($module_id = null)
    {
        if (!Context::get()->id || !$GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            throw new AccessDeniedException();
        }
    }

    public function admin_libraries_action()
    {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
        Navigation::activateItem("/admin/locations/h5p");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5pstudip.js");
        PageLayout::setTitle(_("H5P-Bibliotheken"));
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
                            rename($lib_path, $lib->getPath());
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
                    PageLayout::postSuccess(_("H5P-Bibliotheken wurden übernommen"), $libs_name);
                } else {
                    PageLayout::postError(_("Es konnten leider keine H5P-Bibliotheken in der Datei gefunden werden, die übernommen wurden."));
                }
                $this->redirect("h5p/admin_libraries");
            }
        }
    }

    public function iframe_action($module_id)
    {
        $this->set_layout(null);
        $this->mod = new H5pLernmodul($module_id);
        if (!$this->mod->isAllowed()) {
            $libs = array();
            foreach (H5PLib::findMany($this->mod->findUnallowedLibraries()) as $lib) {
                $libs[] = $lib['name']." ".$lib['major_version'].".".$lib['minor_version'];
            }
            PageLayout::postInfo(_("Dieses H5P-Modul benutzt Bibliotheken, die noch nicht freigegeben sind."), $libs);
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
            $this->styles[] = $this->mod->getH5pLibURL()."/".$css;
        }
        foreach ($this->mod->getJSFiles() as $js) {
            $this->scripts[] = $this->mod->getH5pLibURL()."/".$js;
        }
    }

    public function get_h5p_settings()
    {
        $libs = $this->mod->getLibs();
        $library = $this->mod->getMainLib();
        if (!$library) {
            throw new Exception("Module has no runnable library.");
        }
        $settings = array(
            'baseUrl' => $GLOBALS['ABSOLUTE_URI_STUDIP'],
            'url' => $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins_packages/RasmusFuhse/LernmodulePluginData/moduledata/".$this->mod->getId(),
            'postUserStatistics' => false,
            'ajax' => array(
                'setFinished' => URLHelper::getURL("plugins.php/lernmoduleplugin/h5p/set_finished"),
                'contentUserData' => ('admin-ajax.php?token=' . ('h5p_contentuserdata') . '&action=h5p_contents_user_data&content_id=:contentId&data_type=:dataType&sub_content_id=:subContentId')
            ),
            'saveFreq' => 30,
            'siteUrl' => $GLOBALS['ABSOLUTE_URI_STUDIP'],
            'l10n' => array(
                'H5P' => array(
                    'fullscreen' => _("Vollbild"),
                    'disableFullscreen' => _('Vollbild beenden'),
                    'download' => _('Download'),
                    'copyrights' => _('Nutzungsbedingung'),
                    'embed' => _('Einbetten'),
                    'size' => _('Größe'),
                    'showAdvanced' => _('Zeige mehr'),
                    'hideAdvanced' => _('Zuklappen'),
                    'advancedHelp' => _('Include this script on your website if you want dynamic sizing of the embedded content:'),
                    'copyrightInformation' => _('Rights of use'),
                    'close' => _('Schließen'),
                    'title' => _('Titel'),
                    'author' => _('Author'),
                    'year' => _('Jahr'),
                    'source' => _('Quelle'),
                    'license' => _('License'),
                    'thumbnail' => _('Thumbnail'),
                    'noCopyrights' => _('No copyright information available for this content.'),
                    'downloadDescription' => _('Download this content as a H5P file.'),
                    'copyrightsDescription' => _('View copyright information for this content.'),
                    'embedDescription' => _('View the embed code for this content.'),
                    'h5pDescription' => _('Visit H5P.org to check out more cool content.'),
                    'contentChanged' => _('This content has changed since you last used it.'),
                    'startingOver' => _("You'll be starting over."),
                    'by' => _('von'),
                    'showMore' => _('Mehr anzeigen'),
                    'showLess' => _('Weniger anzeigen'),
                    'subLevel' => _('Sublevel'),
                    'confirmDialogHeader' => _('Aktion bestätigen'),
                    'confirmDialogBody' => _('Wirklich fortfahren? Änderungen können nicht rückgängig gemacht werden.'),
                    'cancelLabel' => _('Abbrechen'),
                    'confirmLabel' => _('Bestätigen'),
                    'licenseU' => _('Undisclosed'),
                    'licenseCCBY' => _('Attribution'),
                    'licenseCCBYSA' => _('Attribution-ShareAlike'),
                    'licenseCCBYND' => _('Attribution-NoDerivs'),
                    'licenseCCBYNC' => _('Attribution-NonCommercial'),
                    'licenseCCBYNCSA' => _('Attribution-NonCommercial-ShareAlike'),
                    'licenseCCBYNCND' => _('Attribution-NonCommercial-NoDerivs'),
                    'licenseCC40' => _('4.0 International'),
                    'licenseCC30' => _('3.0 Unported'),
                    'licenseCC25' => _('2.5 Generic'),
                    'licenseCC20' => _('2.0 Generic'),
                    'licenseCC10' => _('1.0 Generic'),
                    'licenseGPL' => _('General Public License'),
                    'licenseV3' => _('Version 3'),
                    'licenseV2' => _('Version 2'),
                    'licenseV1' => _('Version 1'),
                    'licensePD' => _('Public Domain'),
                    'licenseCC010' => _('CC0 1.0 Universal (CC0 1.0) Public Domain Dedication'),
                    'licensePDM' => _('Public Domain Mark'),
                    'licenseC' => _('Copyright'),
                    'contentType' => _('Inhaltstyp'),
                    'licenseExtras' => _('License Extras'),
                    'changes' => _('Changelog'),
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
        $settings['contents'] = array(
            "cid-" . $this->mod->getId() => array(
                'library' => $libraryname, //name of the runnable library with space instead of minus
                'jsonContent' => file_get_contents($this->mod->getPath()."/content/content.json"),
                'fullscreen' => 0,
                'exportUrl' => null,
                'embedCode' => '<iframe src="">',
                'resizeCode' => null,
                'url' => "",
                'contentUrl' => $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins_packages/RasmusFuhse/LernmodulePluginData/moduledata/".$this->mod->getId()."/content",
                'title' => $this->mod['name'],
                'displayOptions' => array(
                    'frame' => false,
                    'export' => false,
                    'embed' => false,
                    'copyright' => false,
                    'icon' => false
                ),
                'metadata' => array(
                    'title' => $this->mod['name'],
                    'license' => "U"
                ),
                'contentUserData' => array(
                    array('state' => "{}")
                )
            )
        );
        return $settings;
    }

    public function set_finished_action()
    {
        $module_id = Request::option("contentId");
        $score = Request::get("score");
        $max_score = Request::get("maxScore");
        $opened_time = Request::int("opened");
        $finished_time = Request::int("finished");
        $time = Request::get("time");

        $this->render_text("");
    }

    public function download_action($module_id) {
        $this->mod = new H5pLernmodul($module_id);

        $archive = $GLOBALS['TMP_PATH']."/h5p_".$module_id.".zip";
        $zip = \Studip\ZipArchive::create($archive);
        $zip->addFromPath($this->mod->getPath());
        foreach ($this->mod->libs as $lib) {
            $zip->addFromPath($lib->getPath(), $lib['name']."-".$lib['major_version'].".".$lib['minor_version']."/");
        }
        $zip->close();

        header("Content-Type: application/h5p");
        header("Content-Disposition: attachment; filename=\"".$this->mod['name'].".h5p\"");
        header("Content-Length: ".filesize($archive));
        echo file_get_contents($archive);
        unlink($archive);
        die();
    }


}