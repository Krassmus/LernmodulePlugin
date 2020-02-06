<?php

class H5peditorController extends PluginController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    public function edit_action($module_id = null)
    {
        if (!Context::get()->id || !$GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            throw new AccessDeniedException();
        }
        if (!$module_id) {
            $this->mod = new H5pLernmodul();
            $this->mod['draft'] = 1;
            $this->mod['type'] = "h5p";
            $this->mod->store();
            $this->redirect(PluginEngine::getURL($this->plugin, array(), "h5peditor/edit/".$this->mod->getId()));
            return;
        }
        Navigation::activateItem("/course/lernmodule/overview");
        $this->mod = H5pLernmodul::find($module_id);
        if (Request::isPost()) {
            list($machineName, $version) = explode(" ", Request::get("library"));
            list($majorVersion, $minorVersion) = explode(".", $version);
            $lib = H5PLib::findVersion($machineName, $majorVersion, $minorVersion);
            $this->mod->updateH5PData(json_decode(Request::get("parameters"), true), $lib);
            $connection = $this->mod->courseConnection(Context::get()->id);
            if ($connection->isNew()) {
                $block = LernmodulBlock::find(Request::option("block_id"));
                $connection['block_id'] = Request::option("block_id");
                $connection['position'] = count($block->coursemodules) + 1;
                $connection->store();
            }
            if ($this->mod['draft']) {
                $this->mod['draft'] = 0;
                $this->mod->store();
            }
            if ($this->mod) {
                PageLayout::postSuccess(_("Lernmodul wurde gespeichert"));
                $this->redirect(PluginEngine::getURL($this->plugin, array(), "lernmodule/view/".$this->mod->getId()));
                return;
            } else {
                PageLayout::postError(_("Lernmodul konnte nicht gespeichert werden. Daten unvollständig."));
            }
        }


        if (!$this->mod['draft']) {
            $mainlib = $this->mod->getMainLib();
            $this->library = $mainlib['name']." ".$mainlib['major_version'].".".$mainlib['minor_version'];
            $h5p_json = json_decode(file_get_contents($this->mod->getPath()."/h5p.json"), true);
            $this->params = array(
                "params" => json_decode(file_get_contents($this->mod->getPath()."/content/content.json"), true),
                "metadata" => array(
                    'title' => $h5p_json['title'],
                    'license' => $h5p_json['license'],
                    'authors' => $h5p_json['authors'],
                    'extraTitle' => $h5p_json['title'],
                    'changes' => $h5p_json['changes']
                )
            );
        }

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
            $this->plugin->getPluginURL().'/assets/h5p/h5p-action-bar.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5peditor-editor.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-editor.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5peditor.js',
            $this->plugin->getPluginURL().'/assets/h5p/language/de.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-display-options.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-toggle.js'
        );

        foreach ($this->styles as $style) {
            PageLayout::addStylesheet($style);
        }
        foreach ($this->scripts as $script) {
            PageLayout::addScript($script);
        }

        if ($this->mod['draft']) {
            PageLayout::setTitle(_("Neues Lernmodul erstellen"));
        } else {
            PageLayout::setTitle(_("Lernmodul bearbeiten"));
        }

        $this->integration = $this->get_h5p_editor_settings();
    }

    public function get_h5p_editor_settings()
    {
        $settings = array(
            'baseUrl' => $GLOBALS['ABSOLUTE_URI_STUDIP'],
            'url' => Config::get()->LERNMODUL_DATA_URL
                ? Config::get()->LERNMODUL_DATA_URL."/moduledata/". $this->mod->getId()
                : $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins_packages/RasmusFuhse/LernmodulePluginData/moduledata/".$this->mod->getId(),
            'postUserStatistics' => false,
            'ajax' => array(
                'setFinished' => null, //URLHelper::getURL("plugins.php/lernmoduleplugin/h5p/set_finished/"),
                'contentUserData' => null, //URLHelper::getURL("plugins.php/lernmoduleplugin/h5p/set_userdata/")
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
        $settings['core'] = array(
            'styles' => array(
                $this->plugin->getPluginURL()."/assets/h5p/h5p.css",
                $this->plugin->getPluginURL()."/assets/h5p/h5p-confirmation-dialog.css",
                $this->plugin->getPluginURL()."/assets/h5p/h5p-core-button.css",
                $this->plugin->getPluginURL()."/assets/h5peditor_studip.css"
            ),
            'scripts' => array(
                $this->plugin->getPluginURL()."/assets/h5p/jquery.js",
                $this->plugin->getPluginURL()."/assets/h5p/h5p.js",
                $this->plugin->getPluginURL()."/assets/h5p/h5p-event-dispatcher.js",
                $this->plugin->getPluginURL()."/assets/h5p/h5p-x-api-event.js",
                $this->plugin->getPluginURL()."/assets/h5p/h5p-x-api.js",
                $this->plugin->getPluginURL()."/assets/h5p/h5p-content-type.js",
                $this->plugin->getPluginURL()."/assets/h5p/h5p-confirmation-dialog.js",
                $this->plugin->getPluginURL()."/assets/h5p/h5p-action-bar.js"
            )
        );

        $settings['editor'] = array(
            'filesPath' => $this->mod->getDataURL()."/content",
            "fileIcon" => array(
                "path" => "http://localhost/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/images/binary-file.png",
                "width" => 50,
                "height" => 50
            ),
            'ajaxPath' => URLHelper::getURL("plugins.php/lernmoduleplugin/h5peditor/ajax", array('cid' => Context::get()->id, 'module_id' => $this->mod->getId() ,'cmd' => ""), true), //path to load libraries
            'libraryUrl' => "http://localhost/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/",
            'copyrightSemantics' => array(
                'name' => "copyright",
                'type' => "group",
                'label' => _("Copyright Informationen"),
                'fields' => array(
                    array(
                        'name' => "title",
                        'type' => "text",
                        'label' => _("Titel"),
                        'placeholder' => _("La Gioconda"),
                        'optional' => true
                    ),
                    array(
                        'name' => "author",
                        'type' => "text",
                        'label' => _("Autor"),
                        'placeholder' => _("Leonardo da Vinci"),
                        'optional' => true
                    ),
                    array(
                        'name' => "year",
                        'type' => "text",
                        'label' => "Jahr(e)",
                        'placehholder' => _("1503 - 1517"),
                        'optional' => true
                    ),
                    array(
                        'name' => "source",
                        'type' => "text",
                        'label' => _("Quelle"),
                        'placeholder' => "http:\/\/en.wikipedia.org\/wiki\/Mona_Lisa",
                        'optional' => true,
                        'regexp' => array(
                            "pattern" => "^http[s]?:\/\/.+",
                            'modifiers' => "i"
                        )
                    ),
                    array(
                        'name' => "license",
                        'type' => "select",
                        'label' => _("Lizenz"),
                        'default' => "U",
                        'options' => array(
                            array(
                                'value' => 'U',
                                'label' => _('Unbestimmt'),
                            ),
                            array(
                                'value' => "CC BY",
                                'label' => "Attribution",
                                'versions' => array(
                                    array(
                                        'value' => '4.0',
                                        'label' => _('4.0 International'),
                                    ),
                                    array(
                                        'value' => '3.0',
                                        'label' => _('3.0 Unported'),
                                    ),
                                    array(
                                        'value' => '2.5',
                                        'label' => _('2.5 Generic'),
                                    ),
                                    array(
                                        'value' => '2.0',
                                        'label' => _('2.0 Generic'),
                                    ),
                                    array(
                                        'value' => '1.0',
                                        'label' => _('1.0 Generic'),
                                    ),
                                )
                            ),
                            array(
                                'value' => "CC BY-SA",
                                'label' => "Attribution-ShareAlike",
                                'versions' => array(
                                    array(
                                        'value' => '4.0',
                                        'label' => _('4.0 International'),
                                    ),
                                    array(
                                        'value' => '3.0',
                                        'label' => _('3.0 Unported'),
                                    ),
                                    array(
                                        'value' => '2.5',
                                        'label' => _('2.5 Generic'),
                                    ),
                                    array(
                                        'value' => '2.0',
                                        'label' => _('2.0 Generic'),
                                    ),
                                    array(
                                        'value' => '1.0',
                                        'label' => _('1.0 Generic'),
                                    ),
                                )
                            ),
                            array(
                                'value' => "CC BY-ND",
                                'label' => "Attribution-NoDerivs",
                                'versions' => array(
                                    array(
                                        'value' => '4.0',
                                        'label' => _('4.0 International'),
                                    ),
                                    array(
                                        'value' => '3.0',
                                        'label' => _('3.0 Unported'),
                                    ),
                                    array(
                                        'value' => '2.5',
                                        'label' => _('2.5 Generic'),
                                    ),
                                    array(
                                        'value' => '2.0',
                                        'label' => _('2.0 Generic'),
                                    ),
                                    array(
                                        'value' => '1.0',
                                        'label' => _('1.0 Generic'),
                                    ),
                                )
                            ),
                            array(
                                'value' => "CC BY-NC",
                                'label' => "Attribution-NonCommercial",
                                'versions' => array(
                                    array(
                                        'value' => '4.0',
                                        'label' => _('4.0 International'),
                                    ),
                                    array(
                                        'value' => '3.0',
                                        'label' => _('3.0 Unported'),
                                    ),
                                    array(
                                        'value' => '2.5',
                                        'label' => _('2.5 Generic'),
                                    ),
                                    array(
                                        'value' => '2.0',
                                        'label' => _('2.0 Generic'),
                                    ),
                                    array(
                                        'value' => '1.0',
                                        'label' => _('1.0 Generic'),
                                    ),
                                )
                            ),
                            array(
                                'value' => "CC BY-NC-SA",
                                'label' => "Attribution-NonCommercial-ShareAlike",
                                'versions' => array(
                                    array(
                                        'value' => '4.0',
                                        'label' => _('4.0 International'),
                                    ),
                                    array(
                                        'value' => '3.0',
                                        'label' => _('3.0 Unported'),
                                    ),
                                    array(
                                        'value' => '2.5',
                                        'label' => _('2.5 Generic'),
                                    ),
                                    array(
                                        'value' => '2.0',
                                        'label' => _('2.0 Generic'),
                                    ),
                                    array(
                                        'value' => '1.0',
                                        'label' => _('1.0 Generic'),
                                    ),
                                )
                            ),
                            array(
                                'value' => "CC BY-NC-ND",
                                'label' => "Attribution-NonCommercial-NoDerivs",
                                'versions' => array(
                                    array(
                                        'value' => '4.0',
                                        'label' => _('4.0 International'),
                                    ),
                                    array(
                                        'value' => '3.0',
                                        'label' => _('3.0 Unported'),
                                    ),
                                    array(
                                        'value' => '2.5',
                                        'label' => _('2.5 Generic'),
                                    ),
                                    array(
                                        'value' => '2.0',
                                        'label' => _('2.0 Generic'),
                                    ),
                                    array(
                                        'value' => '1.0',
                                        'label' => _('1.0 Generic'),
                                    ),
                                )
                            ),
                            array(
                                'value' => "GNU GPL",
                                'label' => "General Public License",
                                'versions' => array(
                                    array(
                                        'value' => 'v3',
                                        'label' => _('Version 3'),
                                    ),
                                    array(
                                        'value' => 'v2',
                                        'label' => _('Version 2'),
                                    ),
                                    array(
                                        'value' => 'v1',
                                        'label' => _('Version 1'),
                                    )
                                )
                            ),
                            array(
                                'value' => "PD",
                                'label' => "Public Domain",
                                'versions' => array(
                                    array(
                                        'value' => '-',
                                        'label' => _('-'),
                                    ),
                                    array(
                                        'value' => 'CC0 1.0',
                                        'label' => _('CC0 1.0 Universal'),
                                    ),
                                    array(
                                        'value' => 'CC PDM',
                                        'label' => _('Public Domain Mark'),
                                    )
                                )
                            ),
                            array(
                                'value' => "C",
                                'label' => "Copyright"
                            )
                        )
                    ),
                    array(
                        'name' => "version",
                        'type' => "select",
                        'label' => _("Lizenzversion"),
                        'options' => array()
                    )
                )
            ),
            'metadataSemantics' => array(
                array(
                    'name' => 'title',
                    'type' => 'text',
                    'label' => _('Titel'),
                    'placeholder' => 'La Gioconda',
                ),
                array(
                    'name' => 'license',
                    'type' => 'select',
                    'label' => _('Lizenz'),
                    'default' => 'U',
                    'options' =>
                        array(
                            array(
                                'value' => 'U',
                                'label' => _('Unbestimmt'),
                            ),
                            array(
                                'type' => 'optgroup',
                                'label' => _('Creative Commons'),
                                'options' =>
                                    array(
                                        array(
                                            'value' => 'CC BY',
                                            'label' => _('Attribution (CC BY)'),
                                            'versions' =>
                                                array(
                                                    array(
                                                        'value' => '4.0',
                                                        'label' => _('4.0 International'),
                                                    ),
                                                    array(
                                                        'value' => '3.0',
                                                        'label' => _('3.0 Unported'),
                                                    ),
                                                    array(
                                                        'value' => '2.5',
                                                        'label' => _('2.5 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '2.0',
                                                        'label' => _('2.0 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '1.0',
                                                        'label' => _('1.0 Generic'),
                                                    ),
                                                ),
                                        ),
                                        array(
                                            'value' => 'CC BY-SA',
                                            'label' => _('Attribution-ShareAlike (CC BY-SA)'),
                                            'versions' =>
                                                array(
                                                    array(
                                                        'value' => '4.0',
                                                        'label' => _('4.0 International'),
                                                    ),
                                                    array(
                                                        'value' => '3.0',
                                                        'label' => _('3.0 Unported'),
                                                    ),
                                                    array(
                                                        'value' => '2.5',
                                                        'label' => _('2.5 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '2.0',
                                                        'label' => _('2.0 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '1.0',
                                                        'label' => _('1.0 Generic'),
                                                    ),
                                                ),
                                        ),
                                        array(
                                            'value' => 'CC BY-ND',
                                            'label' => _('Attribution-NoDerivs (CC BY-ND)'),
                                            'versions' =>
                                                array(
                                                    array(
                                                        'value' => '4.0',
                                                        'label' => _('4.0 International'),
                                                    ),
                                                    array(
                                                        'value' => '3.0',
                                                        'label' => _('3.0 Unported'),
                                                    ),
                                                    array(
                                                        'value' => '2.5',
                                                        'label' => _('2.5 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '2.0',
                                                        'label' => _('2.0 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '1.0',
                                                        'label' => _('1.0 Generic'),
                                                    ),
                                                ),
                                        ),
                                        array(
                                            'value' => 'CC BY-NC',
                                            'label' => _('Attribution-NonCommercial (CC BY-NC)'),
                                            'versions' =>
                                                array(
                                                    array(
                                                        'value' => '4.0',
                                                        'label' => _('4.0 International'),
                                                    ),
                                                    array(
                                                        'value' => '3.0',
                                                        'label' => _('3.0 Unported'),
                                                    ),
                                                    array(
                                                        'value' => '2.5',
                                                        'label' => _('2.5 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '2.0',
                                                        'label' => _('2.0 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '1.0',
                                                        'label' => _('1.0 Generic'),
                                                    ),
                                                ),
                                        ),
                                        array(
                                            'value' => 'CC BY-NC-SA',
                                            'label' => _('Attribution-NonCommercial-ShareAlike (CC BY-NC-SA)'),
                                            'versions' =>
                                                array(
                                                    array(
                                                        'value' => '4.0',
                                                        'label' => _('4.0 International'),
                                                    ),
                                                    array(
                                                        'value' => '3.0',
                                                        'label' => _('3.0 Unported'),
                                                    ),
                                                    array(
                                                        'value' => '2.5',
                                                        'label' => _('2.5 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '2.0',
                                                        'label' => _('2.0 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '1.0',
                                                        'label' => _('1.0 Generic'),
                                                    ),
                                                ),
                                        ),
                                        array(
                                            'value' => 'CC BY-NC-ND',
                                            'label' => _('Attribution-NonCommercial-NoDerivs (CC BY-NC-ND)'),
                                            'versions' =>
                                                array(
                                                    array(
                                                        'value' => '4.0',
                                                        'label' => _('4.0 International'),
                                                    ),
                                                    array(
                                                        'value' => '3.0',
                                                        'label' => _('3.0 Unported'),
                                                    ),
                                                    array(
                                                        'value' => '2.5',
                                                        'label' => _('2.5 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '2.0',
                                                        'label' => _('2.0 Generic'),
                                                    ),
                                                    array(
                                                        'value' => '1.0',
                                                        'label' => _('1.0 Generic'),
                                                    ),
                                                ),
                                        ),
                                        array(
                                            'value' => 'CC0 1.0',
                                            'label' => _('Public Domain Dedication (CC0)'),
                                        ),
                                        array(
                                            'value' => 'CC PDM',
                                            'label' => _('Public Domain Mark (PDM)'),
                                        ),
                                    ),
                            ),
                            array(
                                'value' => 'GNU GPL',
                                'label' => _('General Public License v3'),
                            ),
                            array(
                                'value' => 'PD',
                                'label' => _('Gemeinfrei'),
                            ),
                            array(
                                'value' => 'ODC PDDL',
                                'label' => _('Public Domain Dedication and Licence'),
                            ),
                            array(
                                'value' => 'C',
                                'label' => _('Copyright'),
                            ),
                        ),
                ),
                array(
                    'name' => 'licenseVersion',
                    'type' => 'select',
                    'label' => _('Lizenzversion'),
                    'options' =>
                        array(
                            array(
                                'value' => '4.0',
                                'label' => _('4.0 International'),
                            ),
                            array(
                                'value' => '3.0',
                                'label' => _('3.0 Unported'),
                            ),
                            array(
                                'value' => '2.5',
                                'label' => _('2.5 Generic'),
                            ),
                            array(
                                'value' => '2.0',
                                'label' => _('2.0 Generic'),
                            ),
                            array(
                                'value' => '1.0',
                                'label' => _('1.0 Generic'),
                            ),
                        ),
                    'optional' => true,
                ),
                array(
                    'name' => 'yearFrom',
                    'type' => 'number',
                    'label' => _('Jahre (ab)'),
                    'placeholder' => '1991',
                    'min' => '-9999',
                    'max' => '9999',
                    'optional' => true,
                ),
                array(
                    'name' => 'yearTo',
                    'type' => 'number',
                    'label' => _('Jahre (bis)'),
                    'placeholder' => '1992',
                    'min' => '-9999',
                    'max' => '9999',
                    'optional' => true,
                ),
                array(
                    'name' => 'source',
                    'type' => 'text',
                    'label' => _('Quelle'),
                    'placeholder' => 'https://',
                    'optional' => true,
                ),
                array(
                    'name' => 'authors',
                    'type' => 'list',
                    'field' =>
                        array(
                            'name' => 'author',
                            'type' => 'group',
                            'fields' =>
                                array(
                                    0 =>
                                        array(
                                            'label' => _("Autorenname"),
                                            'name' => 'name',
                                            'optional' => true,
                                            'type' => 'text',
                                        ),
                                    array(
                                        'name' => 'role',
                                        'type' => 'select',
                                        'label' => _("Rolle des Autors"),
                                        'default' => 'Author',
                                        'options' =>
                                            array(
                                                array(
                                                    'value' => 'Author',
                                                    'label' => _('Autor'),
                                                ),
                                                array(
                                                    'value' => 'Editor',
                                                    'label' => _('Editor'),
                                                ),
                                                array(
                                                    'value' => 'Licensee',
                                                    'label' => _('Lizenzgeber'),
                                                ),
                                                array(
                                                    'value' => 'Originator',
                                                    'label' => _('Originator'),
                                                ),
                                            ),
                                    ),
                                ),
                        ),
                ),
                array(
                    'name' => 'licenseExtras',
                    'type' => 'text',
                    'widget' => 'textarea',
                    'label' => _('Lizenz-Zusatzinformationen'),
                    'optional' => true,
                    'description' => _('Weitere Informationen bezüglich der Lizenz'),
                ),
                array(
                    'name' => 'changes',
                    'type' => 'list',
                    'field' =>
                        array(
                            'name' => 'change',
                            'type' => 'group',
                            'label' => _('Changelog'),
                            'fields' =>
                                array(
                                    array(
                                        'name' => 'date',
                                        'type' => 'text',
                                        'label' => _('Datum'),
                                        'optional' => true,
                                    ),
                                    array(
                                        'name' => 'author',
                                        'type' => 'text',
                                        'label' => _('Verändert durch'),
                                        'optional' => true,
                                    ),
                                    array(
                                        'name' => 'log',
                                        'type' => 'text',
                                        'widget' => 'textarea',
                                        'label' => _('Beschreibung der Änderung'),
                                        'placeholder' => _('Foto zugeschnitten, Text geändert, etc.'),
                                        'optional' => true,
                                    ),
                                ),
                        ),
                ),
                array(
                    'name' => 'authorComments',
                    'type' => 'text',
                    'widget' => 'textarea',
                    'label' => _('Autorenkommentare'),
                    'description' => _('Kommentare für den Editor (dieser Kommentar wird nicht veröffentlicht)'),
                    'optional' => true,
                ),
                array(
                    'name' => 'contentType',
                    'type' => 'text',
                    'widget' => 'none',
                ),
            ),
            'assets' => array(
                'css' => array(
                    $this->plugin->getPluginURL()."/assets/h5p/h5p.css",
                    $this->plugin->getPluginURL()."/assets/h5p/h5p-confirmation-dialog.css",
                    $this->plugin->getPluginURL()."/assets/h5p/h5p-core-button.css",
                    $this->plugin->getPluginURL()."/assets/h5p/darkroom.css",
                    $this->plugin->getPluginURL()."/assets/h5p/h5p-hub-client.css",
                    $this->plugin->getPluginURL()."/assets/h5p/fonts.css",
                    $this->plugin->getPluginURL()."/assets/h5p/application.css",
                    $this->plugin->getPluginURL()."/assets/h5p/zebra_datepicker.min.css",
                    $this->plugin->getPluginURL()."/assets/h5peditor_studip.css"
                ),
                'js' => array(
                    $this->plugin->getPluginURL()."/assets/h5p/jquery.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5p.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5p-event-dispatcher.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5p-x-api-event.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5p-x-api.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5p-content-type.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5p-confirmation-dialog.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5p-action-bar.js",
                    //"\/wordpress\/wp-content\/plugins\/h5p\/h5p-editor-php-library\/scripts\/h5p-hub-client.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-semantic-structure.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-library-selector.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-form.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-text.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-html.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-number.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-textarea.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-file-uploader.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-file.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-image.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-image-popup.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-av.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-group.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-boolean.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-list.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-list-editor.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-library.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-library-list-cache.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-select.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-selector-hub.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-selector-legacy.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-dimensions.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-coordinates.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-none.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-metadata.js?v=1",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-metadata-author-widget.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-metadata-changelog-widget.js",
                    $this->plugin->getPluginURL()."/assets/h5p/h5peditor-pre-save.js",
                    $this->plugin->getPluginURL()."/vendor/h5p-editor/ckeditor/ckeditor.js"
                )
            ),
            'deleteMessage' => dgettext("lernmoduleplugin","Bist du sicher, dass du diesen Inhalt löschen willst?"),
            'apiVersion' => array(
                'majorVersion' => 1,
                'minorVersion' => 19
            )
        );
        if (!$this->mod['draft']) {
            $settings['metadata'] = array(
                'title' => $this->mod['name'],
                'license' => "U"
            );
            $h5p_json = json_decode(file_get_contents($this->mod->getPath()."/h5p.json"), true);
            if ($h5p_json) {
                if ($h5p_json['license']) {
                    $settings['metadata']['license'] = $h5p_json['license'];
                }
                if ($h5p_json['authors']) {
                    $settings['metadata']['authors'] = (array)$h5p_json['authors'];
                }
                if ($h5p_json['changes']) {
                    $settings['metadata']['changes'] = (array)$h5p_json['changes'];
                }
                if ($h5p_json['yearFrom']) {
                    $settings['metadata']['yearFrom'] = $h5p_json['yearFrom'];
                }
                if ($h5p_json['yearTo']) {
                    $settings['metadata']['yearTo'] = $h5p_json['yearTo'];
                }
                if ($h5p_json['source']) {
                    $settings['metadata']['source'] = $h5p_json['source'];
                }
                if ($h5p_json['licenseExtras']) {
                    $settings['metadata']['licenseExtras'] = $h5p_json['licenseExtras'];
                }
            }
        }
        return $settings;
    }

    public function ajax_action() {
        $cmd = Request::get("cmd");
        if ($cmd === "libraries" && Request::get("machineName")) {
            //deliver the JSON to display the lib in the editor after it has been selected
            $main_library = H5PLib::findVersion(Request::get("machineName"), Request::get("majorVersion"), Request::get("minorVersion"));
            if (!$main_library['allowed']) {
                throw new AccessDeniedException();
            }
            $javascripts = array();
            $css = array();

            $libs = array();
            $lib_ids = array();
            $library = json_decode(file_get_contents($main_library->getPath() . "/library.json"), true);

            $dependencies = (array) $library['editorDependencies'];

            foreach ($dependencies as $dependency) {
                $lib = H5PLib::findVersion($dependency['machineName'], $dependency['majorVersion'], $dependency['minorVersion']);
                if ($lib) {
                    $libs[] = $lib;
                    $lib_ids[] = $lib->getId();
                    foreach ($lib->getSubLibs($lib_ids, true) as $sublib) {
                        if (!in_array($sublib->getId(), $lib_ids)) {
                            $lib_ids[] = $sublib->getId();
                            $libs[] = $sublib;
                        }
                    }
                }
            }

            //Once again the topological ordering of libs:
            $edges = array();
            $ignore = array();
            foreach ($libs as $lib) {
                foreach ($lib->getSubLibs(array(), false) as $sublib) {
                    if ($lib->getId() != $sublib->getId()) {
                        if (!in_array($sublib->getId(), $ignore)) {
                            $ignore[] = $sublib->getId();
                        }
                        $edges[$sublib->getId() . "_" . $lib->getId()] = array(
                            $sublib->getId(),
                            $lib->getId()
                        );
                    }
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
            $libs = $libs_sorted;


            //now fetch the js and css files:
            $language = substr(getUserLanguage($GLOBALS['user']->id), 0, 2);
            $translations = array();
            foreach ($libs as $lib) {
                if ($lib) {
                    foreach ($lib->getFiles("js", true) as $js) {
                        $javascripts[] = H5PLernmodul::getH5pLibURL() . "/" . $js;
                    }
                    foreach ($lib->getFiles("css", true) as $style) {
                        $css[] = H5PLernmodul::getH5pLibURL() . "/" . $style;
                    }
                }

                if (file_exists($lib->getPath() . "/language/" . $language . ".json")) {
                    $translation = json_decode(file_get_contents($lib->getPath() . "/language/" . $language . ".json"), true);
                    if ($translation) {
                        $translations[$lib['name']] = $translation;
                    }
                }
            }


            $output = array(
                'semantics' => json_decode(file_get_contents($main_library->getPath() . "/semantics.json"), true),
                'language' => file_exists($main_library->getPath() . "/language/de.json")
                    ? file_get_contents($main_library->getPath() . "/language/de.json")
                    : json_encode(array()),
                'javascript' => $javascripts,
                'css' => $css,
                'translations' => $translations
            );
            $this->render_json($output);
            return;
        } elseif(count(Request::getArray("libraries")) > 0) {
            $libs = array();
            foreach (Request::getArray("libraries") as $library_name) {
                list($machineName, $version) = explode(" ", $library_name);
                list($majorVersion, $minorVersion) = explode(".", $version);
                $lib = H5PLib::findVersion($machineName, $majorVersion, $minorVersion);
                if ($lib) {
                    $libs[] = $lib;
                }
            }
            foreach ($libs as $lib) {
                $data = $lib->getLibraryData();
                $output[] = array(
                    'uberName' => $lib['name']." " .$lib['major_version'].".".$lib['minor_version'],
                    'name' => $lib['name'],
                    'title' => $data['title'],
                    'majorVersion' => $lib['major_version'],
                    'minorVersion' => $lib['minor_version'],
                    'tutorialUrl' => "",
                    'runnable' => $lib['runnable'] ? 1 : 0,
                    'restricted' => false,
                    'metadataSettings' => null
                );
            }
            $this->render_json($output);
            return;
        } elseif ($cmd === "libraries") {
            //load all libraries for editor at the beginning
            $statement = DBManager::get()->prepare("
                    SELECT lernmodule_h5plibs.*
                    FROM lernmodule_h5plibs
                    WHERE lernmodule_h5plibs.allowed = '1'
                        AND lernmodule_h5plibs.runnable = '1'
                        AND lib_id = (
                            SELECT l2.lib_id
                            FROM lernmodule_h5plibs AS l2
                            WHERE l2.name = lernmodule_h5plibs.name
                            ORDER BY major_version DESC, minor_version DESC
                            LIMIT 1
                        )
                    ORDER BY name ASC
                ");
            $statement->execute();
            $libs = array();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $d) {
                $libs[] = H5PLib::buildExisting($d);
            }
            $output = array();
            foreach ($libs as $lib) {
                $data = $lib->getLibraryData();
                $output[] = array(
                    'name' => $lib['name'],
                    'title' => $data['title'],
                    'majorVersion' => $lib['major_version'],
                    'minorVersion' => $lib['minor_version'],
                    'tutorialUrl' => "",
                    'restricted' => false,
                    'metadataSettings' => null
                );
            }
            $this->render_json($output);
            return;
        } elseif ($cmd === "files") {
            //upload files ...
            $_FILES['file'];
            $mod = H5PLernmodul::find(Request::get("module_id"));
            if (!$mod) {
                throw new Exception(_("Modul existiert nicht. Kann Datei nicht hochladen."));
            }

            $path = $mod->getPath();
            if (!file_exists($path)) {
                mkdir($path);
            }

            if (!file_exists($path."/content")) {
                mkdir($path."/content");
            }
            $path .= "/content";
            if (!file_exists($path."/assets")) {
                mkdir($path."/assets");
            }
            $path .= "/assets/";
            $ending = strpos($_FILES['file']['name'], ".") !== false
                ? substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], ".") + 1)
                : "";
            $filename = strpos($_FILES['file']['name'], ".") !== false
                ? substr($_FILES['file']['name'], 0, strrpos($_FILES['file']['name'], "."))
                : $_FILES['file']['name'];

            $i = "";
            while (file_exists($path.$filename.($i ? " (".$i.")" : "").($ending ? ".".$ending : ""))) {
                $i++;
            }
            $newfilepath = $path.$filename.($i ? " (".$i.")" : "").($ending ? ".".$ending : "");
            move_uploaded_file($_FILES['file']['tmp_name'], $newfilepath);

            $image_size = getimagesize($newfilepath);


            $this->render_json(array(
                'width' => $image_size[0],
                'height' => $image_size[1],
                'mime' => $_FILES['file']['type'],
                'path' => "assets/".$filename.($i ? " (".$i.")" : "").($ending ? ".".$ending : "")
            ));
            return;
        }
        $this->render_text("errr .. ok");
    }


}
