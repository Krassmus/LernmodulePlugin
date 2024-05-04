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
        // 1. Store all of the files in the zip, except for task_definition.json,
        //    as Files/FileRefs using Stud.IP's SORM.
        // TODO preserve 'description' and terms of use metadata.
        $filenames = array_diff(scandir($this->getPath()), ['.', '..', 'task_definition.json']);
        $uploaded_files = ['name' => [], 'error' => [], 'type' => [], 'size' => [], 'tmp_name' => []];
        foreach ($filenames as $filename) {
            $file_path = $this->getPath() . '/' . $filename;
            // TODO This sets the filename to the file's ID on the system where the
            //  lernmodul was exported, which is definitely not what we want.
            //  We should probably preserve the original filename in export and import.
            $uploaded_files['name'][] = $filename;
            $uploaded_files['error'][] = 0;
            // TODO Check if mime_content_type is OK to use here.
            $uploaded_files['type'][] = mime_content_type($file_path);
            // FileManager will find the size for us.
            $uploaded_files['size'][] = null;
            $uploaded_files['tmp_name'][] = $file_path;
        }
        $folder = $this->getWysiwygFolder();
        $validatedFiles = FileManager::handleFileUpload(
            $uploaded_files, $folder, $GLOBALS['user']->id
        );

        // 2. Make a map from old file_ids to new file_ids.
        $file_ids_map = [];
        assert(count($filenames) == count($validatedFiles['files']));
        // The order of $validatedFiles is the same as the order of $uploaded_files,
        // so we can match files up by index.
        // However, we must first re-index $filenames numerically to make the indices
        // match, because the use of array_diff above removes entries from $filenames,
        // making it a sparse array.
        $filenames = array_values($filenames);
        // Go through the two lists of files and put every old_id => new_id entry
        // into the map.
        for ($i = 0; $i < count($filenames); $i++) {
            $old_id = $filenames[$i];
            $new_id = $validatedFiles['files'][$i]->id;
            $file_ids_map[$old_id] = $new_id;
        }

        // 3. Replace the old file_id keys with the new ones everywhere in task_definition.json.
        $task_definition_contents = file_get_contents($this->getPath() . '/task_definition.json');
        $task_definition = json_decode($task_definition_contents, true);
        self::traverseArray($task_definition, function(&$array, $key, $value) use ($file_ids_map) {
            if ($key == 'file_id') {
                $old_id = $value;
                $new_id = $file_ids_map[$old_id];
                $array['file_id'] = $new_id;
            }
        });

        // 4. Set $this['customdata'] to the contents of task_definition.json.
        $this['customdata'] = json_encode($task_definition);
        $this->store();

        // TODO Import the Infotext and image for the Lernmodul.
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

        /**
         * Get all images, videos, etc. and add them to the zip
         * By convention, they are all saved by ID under keys named "file_id".
         * */
        $files = [];
        self::traverseArray(
            $task_definition,
            function($array, $key, $value) use (&$files) {
                if ($key == 'file_id') {
                    $file_ref = FileRef::find($value);
                    if (is_null($file_ref)) {
                        throw new Exception("Es wurde kein File-Ref mit der ID {$value} gefunden.");
                    }
                    $path = $file_ref->file->getPath();
                    if (is_null($path)) {
                        throw new Exception("getPath() hat für das File-Ref mit der ID {$value} null geliefert.");
                    }
                    $files[$value] = $path;
                }
            }
        );
        // Add all the files we found to the zip, named according to their file_id.
        // TODO Export filename, description and terms of use metadata as well.
        foreach ($files as $id => $path) {
            $zip->addFile($path, $id);
        }

        // TODO: Export Infotext and image for the Lernmodul.

        $zip->close();
        return $zip_file_path;
    }

}
