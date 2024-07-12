<?php

class VuejsLernmodul extends Lernmodul implements CustomLernmodul
{
    static public function detect($path)
    {
        throw new Exception('Not implemented');
    }

    /**
     * Import the data from the exported zip file, whose contents
     * (precondition) have been extracted to $this->getPath().
     */
    public function afterInstall()
    {
        // 1. Import all of the files from the zip, except for task_definition.json
        //    and files_metadata.json, as Files/FileRefs using Stud.IP's SORM.
        // Load the metadata for the files.
        // TODO: Consider using JSON Schema validation, as in the Courseware,
        //  to validate the contents of files_metadata.json.
        $files_metadata_path = $this->getPath() . '/files_metadata.json';
        $files_metadata_contents = file_get_contents($files_metadata_path);
        if (!$files_metadata_contents) {
            throw new Exception("Die Datei '$files_metadata_path' wurde nicht gefunden.");
        }
        $files_metadata = json_decode($files_metadata_contents, true);

        // Import all the files, applying the metadata that has been supplied.
        $filenames = array_diff(scandir($this->getPath()), ['.', '..', 'task_definition.json', 'files_metadata.json']);
        $uploaded_files = ['name' => [], 'error' => [], 'type' => [], 'size' => [], 'tmp_name' => []];
        foreach ($filenames as $filename) {
            $metadata = $files_metadata[$filename];
            $file_path = $this->getPath() . '/' . $filename;
            $uploaded_files['name'][] = $metadata['name'];
            $uploaded_files['error'][] = 0;
            // TODO Check if mime_content_type is OK to use here.
            $uploaded_files['type'][] = mime_content_type($file_path);
            // FileManager will find the size for us.
            $uploaded_files['size'][] = null;
            $uploaded_files['tmp_name'][] = $file_path;
            // TODO Import file description and terms of use as well?
        }
        $folder = $this->getWysiwygFolder();
        $validatedFiles = FileManager::handleFileUpload(
            $uploaded_files, $folder, $GLOBALS['user']->id
        );

        // 2. Make a map from old file_ids to new file_ids.
        $file_ids_map = [];
        assert(count($filenames) === count($validatedFiles['files']));
        // The order of $validatedFiles is the same as the order of $uploaded_files,
        // so we can match files up by index.
        // However, we must first re-index $filenames numerically to make the indices
        // match, because the use of array_diff above removes entries from $filenames
        // without reindexing, making it a sparse array.
        $filenames = array_values($filenames);
        // Go through the two lists of files and put every old_id => new_id entry
        // into the map.
        for ($i = 0; $i < count($filenames); $i++) {
            $old_id = $filenames[$i];
            $new_id = $validatedFiles['files'][$i]->id;
            $file_ids_map[$old_id] = $new_id;
        }

        // 3. Replace the old file_id keys with the new ones everywhere in task_definition.json.
        $task_definition_path = $this->getPath() . '/task_definition.json';
        $task_definition_contents = file_get_contents($task_definition_path);
        if (!$task_definition_contents) {
            throw new Exception("Die Datei '$task_definition_path' wurde nicht gefunden.");
        }
        $task_definition = json_decode($task_definition_contents, true);
        self::traverseArray($task_definition, function(&$array, $key, $value) use ($file_ids_map) {
            if ($key === 'file_id') {
                // We made our lives easy and simply saved the file by its ID under 'file_id' :)
                $old_id = $value;
                $new_id = $file_ids_map[$old_id];
                $array['file_id'] = $new_id;
            } else if (self::is_wysiwyg_html($array, $key, $value)) {
                // The WYSIWYG Editor makes our lives more complicated.
                // We need to reach inside the WYSIWYG HTML and rewrite URLs,
                // fixing up the 'file_id' parameter where appropriate.
                // Fortunately, we can do this in a simple way: Just grep for each
                // old ID and replace it with the respective new ID wherever it occurs
                // in the WYSIWYG-HTML. I think there is little risk that we
                // will mess anything up by doing this, because file IDs are
                // 32-character-long hexadecimal strings that are unlikely to
                // appear here by mistake outside of the files' URLs.
                $replaced_html = $value;
                foreach($file_ids_map as $old_id => $new_id) {
                    $replaced_html = str_replace($old_id, $new_id, $replaced_html);
                }
                $array[$key] = $replaced_html;
            }
        });

        // 4. Save the new task_definition with the correct file IDs.
        $this['customdata'] = json_encode($task_definition);
        $this->store();
    }

    /**
     * Based on code in wysiwyg.php, find the user's Wysiwyg Uploads folder.
     * @return FolderType The user's WYSIWYG Uploads folder
     * @throws Exception if the wysiwyg folder is not present and cannot be created
     */
    private function getWysiwygFolder(): FolderType
    {
        //try to find an already existing WYSIWYG folder inside the
        //user's personal file area:
        $user = User::findCurrent();
        $wysiwyg_folder = Folder::findOneBySql(
            "range_id = :user_id
                AND folder_type = 'PublicFolder'
                AND name = :wysiwyg_name ",
            [
                'user_id' => $user->id,
                'wysiwyg_name' => 'Wysiwyg Uploads',
            ]
        );

        if (!$wysiwyg_folder) {
            //get the top folder of the user's personal file area and its FolderType:
            $top_folder = Folder::findTopFolder($user->id)->getTypedFolder();

            $wysiwyg_folder = new PublicFolder(Folder::build([
                'user_id' => $user->id,
                'name' => 'Wysiwyg Uploads',
                'description' => 'Vom WYSIWYG Editor hochgeladene Dateien',
            ]));

            if (!$top_folder->createSubfolder($wysiwyg_folder)) {
                throw new Exception(_('WYSIWYG-Ordner für hochgeladene Dateien konnte nicht erstellt werden!'));
            }
        } else {
            $wysiwyg_folder = $wysiwyg_folder->getTypedFolder();
        }
        return $wysiwyg_folder;
    }

    public function getEditTemplate()
    {
        $actions = new ActionsWidget();
        $actions->addLink(
            dgettext("lernmoduleplugin", "Im Editor bearbeiten"),
            URLHelper::getURL("plugins.php/lernmoduleplugin/vuejseditor/edit/" . $this->getId()),
            Icon::create("edit", "clickable")
        );
        Sidebar::Get()->addWidget($actions);
    }

    public function getViewerTemplate($attempt, $game_attendance = null)
    {
        $actions = new ActionsWidget();
        $actions->addLink(
            dgettext("lernmoduleplugin", "Im Editor bearbeiten"),
            URLHelper::getURL("plugins.php/lernmoduleplugin/vuejseditor/edit/" . $this->getId()),
            Icon::create("edit", "clickable")
        );
        Sidebar::Get()->addWidget($actions);

        $templatefactory = new Flexi_TemplateFactory(__DIR__ . "/../views");
        $template = $templatefactory->open("vuejs/view.php");
        $template->set_attribute("module", $this);
        $template->set_attribute("attempt", $attempt);
        $template->set_attribute(
            "coursemodule",
            LernmodulCourse::findOneBySQL(
                "module_id = ? AND seminar_id = ?",
                array($this->getId(), Context::get()->id)
            )
        );
        $template->set_attribute("game_attendance", $game_attendance);
        return $template;
    }

    public function getEvaluationTemplate($course_id)
    {
        $attempts = LernmodulAttempt::findbyCourseAndModule(Context::get()->id, $this->getId());
        $pointclasses = array();
        foreach ($attempts as $attempt) {
            if ($attempt['customdata']) {
                $data = $attempt['customdata']->getArrayCopy();
                foreach ((array)$data['points'] as $pointclass => $value) {
                    if (!in_array($pointclass, $pointclasses)) {
                        $pointclasses[] = $pointclass;
                    }
                }
            }
        }

        $templatefactory = new Flexi_TemplateFactory(__DIR__ . "/../views");
        $template = $templatefactory->open("html/evaluation.php");
        $template->set_attribute("module", $this);
        $template->set_attribute("course_id", $course_id);
        $template->set_attribute("pointclasses", $pointclasses);
        $template->set_attribute("attempts", $attempts);
        return $template;
    }

    public function evaluateAttempt($attempt)
    {
        $output = array();
        if ($attempt['customdata']['points']) {
            $output = $attempt['customdata']['points']->getArrayCopy();
        }
        if ($attempt['customdata']['attributes']) {
            foreach ($attempt['customdata']['attributes'] as $name => $value) {
                if (!isset($output[$name])) {
                    $output[$name] = $value;
                }
            }
        }
        return $output;
    }

    private static function traverseArray(&$array, $callback) {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                self::traverseArray($value, $callback);
                continue;
            }
            $callback($array, $key, $value);
        }
    }

    /**
     * Produce a zip file containing the contents of this Lernmodul, ready
     * to be imported onto another Stud.IP installation.
     * @return string The path of the zip file, somewhere inside of $GLOBALS['TMP_PATH']
     * @throws Exception if an error occurs while exporting a file used in the Lernmodul
     */
    public function getExportFile() {
        // Create a temporary zip file
        $temp_prefix = $GLOBALS['TMP_PATH'] . '/' . md5(uniqid());
        $zip_file_path = "{$temp_prefix}.studip-lernmodule.zip";
        $zip = \Studip\ZipArchive::create($zip_file_path);

        // Get the JSON of the lernmodule and add it to the zip
        $task_definition = json_decode($this['customdata'], true);
        $task_definition_encoded = json_encode($task_definition);
        $task_definition_filename = "{$temp_prefix}.task_definition.json";
        file_put_contents($task_definition_filename, $task_definition_encoded);
        $zip->addFile($task_definition_filename, 'task_definition.json');

        // Find the IDs of all files (images, videos, etc.) used in the Lernmodul
        // that should be exported.
        $file_ids = [];
        self::traverseArray(
            $task_definition,
            function($array, $key, $value) use (&$file_ids) {
                if ($key === 'file_id') {
                    // To make things easy on ourselves, we try to save all
                    // references to embedded files under keys named 'file_id'.
                    $file_ids[] = $value;
                } else if (self::is_wysiwyg_html($array, $key, $value)) {
                    // The WYSIWYG editor makes things tricky, because it produces
                    // a big blob of HTML that can have images embedded in it.
                    // We should export any files in the Lernmodul that were
                    // uploaded using the WYSIWYG editor.
                    $wysiwyg_file_ids = self::extract_file_ids_from_wysiwyg_html($value);
                    array_push($file_ids, ...$wysiwyg_file_ids);
                }
            }
        );

        // Now that we have a list of file IDs, use SORM to get the paths to the
        // files on disk, as well as the files' metadata that should be exported.
        // Map from file_id => file path
        $file_paths_by_id = [];
        // Map from file_id => ['name' => name, 'description' => description]
        $files_metadata = [];
        foreach ($file_ids as $file_id) {
            if (!$file_id) {
                continue; // Prevent an empty or undefined file_id from causing the export to fail.
            }
            $file_ref = FileRef::find($file_id);
            if (is_null($file_ref)) {
                throw new Exception("Es wurde kein File-Ref mit der ID '$file_id' gefunden.");
            }
            if (!$file_ref->getFileType()->isDownloadable($GLOBALS['user']->id)) {
                throw new Exception("Die Datei mit der ID '$file_id' ist für den eingeloggen Nutzer nicht verfügbar.");
            }
            $path = $file_ref->file->getPath();
            if (is_null($path)) {
                throw new Exception("getPath() hat für das File-Ref mit der ID '{$file_id}' null geliefert.");
            }
            $file_paths_by_id[$file_id] = $path;
            $files_metadata[$file_id] = [
                'name' => $file_ref->name,
                'description' => $file_ref->description,
                // TODO Export terms of use metadata as well?
            ];
        }

        // Add all the files we found to the zip, named according to their file_id.
        foreach ($file_paths_by_id as $id => $path) {
            $zip->addFile($path, $id);
        }
        // Add the metadata of all files to the zip as well.
        $files_metadata_encoded = json_encode($files_metadata);
        $files_metadata_filename = "{$temp_prefix}.files_metadata.json";
        file_put_contents($files_metadata_filename, $files_metadata_encoded);
        $zip->addFile($files_metadata_filename, 'files_metadata.json');

        $zip->close();
        return $zip_file_path;
    }

    /**
     * @param array $array
     * @param string $key a key in the array
     * @return bool true iff the value stored under key should be treated as
     * wysiwyg html during import/export
     */
    private static function is_wysiwyg_html($array, $key, $value): bool {
        if (str_ends_with($key, '_wysiwyg')) {
            // All future wysiwyg html should be stored under a key ending with _wysiwyg, please!
            return true;
        } else {
            // Certain existing task types save WYSIWYG-generated HTML under
            // the key 'template'. Rather than change the data schema, which
            // would force us to write a lot of migration boilerplate and refactor
            // a lot of Vue code, we just special-case them here.
            return $key === 'template' &&
                in_array($array['task_type'], ['DragTheWords', 'MarkTheWords', 'FillInTheBlanks']);
        }
    }

    /**
     * Use a regex to extract, as best we can, file_ids of images embedded in
     * the given rich text HTML string created in the Stud.IP WYSIWYG editor.
     * They are embedded using tags like <img src='...'>.
     * The 'src' attribute of each <img> tag should look something like this:
     * http://localhost/54/sendfile.php?type=0&file_id=31c8022aed2992c052a4d471cb0eb58e&file_name=banan%C3%A1baby.jpeg
     * @param string $html
     * @return string[] Array of file IDs.
     */
    private static function extract_file_ids_from_wysiwyg_html(string $html) {
        // I recommend using https://regex101.com/ to interpret and debug this
        // complex regex if it gives you any trouble. -Ann
        $regex = preg_quote($GLOBALS['ABSOLUTE_URI_STUDIP'], '/') . 'sendfile\.php\?(?:\w+=[\w\+\.]+&amp;)*file_id=([a-f0-9]{32})';
        $matches = [];
        $result = preg_match_all("/$regex/", $html, $matches);
        if ($result === false) {
            throw new Exception('preg_match_all hat fehlgeschlagen. Regex: ' . $regex);
        }
        return $matches[1];
    }

}
