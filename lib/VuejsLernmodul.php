<?php

class VuejsLernmodul extends Lernmodul implements CustomLernmodul
{
    static public function detect($path)
    {
        throw new Exception('Not implemented');
    }

    public function afterInstall()
    {
        // TODO Import the data from the exported zip file, whose contents
        //  (precondition) have been extracted to $this->getPath():
        // 1. Store all of the files, except for task_definition.json, as
        //    Files/FileRefs using Stud.IP's SORM.
        // 2. Find-and-replace every "file_id" key in task_definition.json using
        //    the new file_id created in step 1.
        // 3. Set $this['customdata'] to the contents of task_definition.json.
        $task_definition_contents = file_get_contents($this->getPath() . '/task_definition.json');
        $task_definition = json_decode($task_definition_contents, true);
        $this['customdata'] = json_encode($task_definition);
        $this->store();
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

    public function getExportFile() {
        // Create a temporary zip file
        $temp_prefix = $GLOBALS['TMP_PATH'] . '/' . md5(uniqid());
        $zip_filename = "{$temp_prefix}.studip-lernmodule.zip";
        $zip = \Studip\ZipArchive::create($zip_filename);

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
        function getAllFiles($array, &$files) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    getAllFiles($value, $files);
                    continue;
                }
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
        }
        $files = [];
        getAllFiles($task_definition, $files);
        // Add all the files we found to the zip
        foreach ($files as $id => $path) {
            $zip->addFile($path, $id);
        }

        $zip->close();
        return $zip_filename;
    }

}
