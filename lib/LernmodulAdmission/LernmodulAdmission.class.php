<?php

require_once 'lib/classes/admission/AdmissionRule.class.php';
require_once __DIR__."/../Lernmodul.php";
require_once __DIR__."/../LernmodulAttempt.php";

class LernmodulAdmission extends AdmissionRule
{
    public $module_id = null;
    public $seminar_id = null;

    public function __construct($ruleId='', $courseSetId = '')
    {
        parent::__construct($ruleId, $courseSetId);
        $this->default_message = dgettext('lernmoduleplugin','Sie befinden sich nicht innerhalb des Anmeldezeitraums.');
        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('timedadmissions');
        }
    }

    public function delete() {
        parent::delete();
        // Delete rule data.
        $stmt = DBManager::get()->prepare("
            DELETE FROM `lernmodule_admissionrules`
            WHERE `rule_id` = ?
        ");
        $stmt->execute(array($this->id));
    }

    public static function getDescription() {
        return dgettext("lernmoduleplugin","Anmelderegeln dieses Typs legen ein Lernmodul fest, das erfolgreich besucht sein muss, um zu der Veranstaltung zugelassen zu werden.");
    }

    public static function getName() {
        return dgettext("lernmoduleplugin","Lernmodul als Voraussetzung");
    }

    public function getTemplate() {
        $factory = new Flexi_TemplateFactory(__DIR__.'/../../views');
        // Open specific template for this rule and insert base template.
        $template = $factory->open('admission/configure');
        $template->set_attribute("search", new SQLSearch("
            SELECT CONCAT(seminare.Seminar_id, '-', lernmodule_module.module_id), CONCAT(seminare.name, ': ', lernmodule_module.name)
            FROM lernmodule_module
                INNER JOIN lernmodule_courses ON (lernmodule_courses.module_id = lernmodule_module.module_id)
                INNER JOIN seminare ON (lernmodule_courses.seminar_id = seminare.Seminar_id)
            WHERE CONCAT(seminare.name, ': ', lernmodule_module.name) LIKE :input
            ", dgettext("lernmoduleplugin","Lernmodul")));
        $template->set_attribute('rule', $this);
        return $template->render();
    }

    public function load() {
        // Load data.
        $stmt = DBManager::get()->prepare("
            SELECT *
            FROM `lernmodule_admissionrules`
            WHERE `rule_id` = ? LIMIT 1
        ");
        $stmt->execute(array($this->id));
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->module_id = $current['module_id'];
            $this->seminar_id = $current['seminar_id'];
        }
    }

    public function ruleApplies($user_id, $course_id) {
        $errors = array();

        if (!LernmodulAttempt::findOneBySQL("successful = '1' AND module_id = ? AND user_id = ?", array($this->module_id, $user_id))) {
            $errors[] = sprintf(dgettext("lernmoduleplugin","Sie haben das Lernmodul '%s' noch nicht absolviert"), Lernmodul::find($this->module_id)->name);
        }
        return $errors;
    }

    public function setAllData($data) {
        parent::setAllData($data);
        list($this->seminar_id, $this->module_id) = explode("-", $data['seminar_id-module_id']);
        return $this;
    }

    public function store() {
        $stmt = DBManager::get()->prepare("
            INSERT INTO `lernmodule_admissionrules`
            SET `rule_id` = :rule_id,
                `seminar_id` = :seminar_id,
                `module_id` = :module_id
            ON DUPLICATE KEY UPDATE `module_id` = :module_id
        ");
        $stmt->execute(array(
            'rule_id' => $this->id,
            'module_id' => $this->module_id,
            'seminar_id' => $this->seminar_id
        ));
    }

    public function toString()
    {
        return sprintf(
            dgettext("lernmoduleplugin","Um zu der Veranstaltung zugelassen zu werden, müssen Sie an dem Lernmodul '%s' erfolgreich teilgenommen haben."),
            Lernmodul::find($this->module_id)->name
        );
    }

    public function validate($data)
    {
        $errors = parent::validate($data);
        if (!$data['seminar_id-module_id']) {
            $errors[] = dgettext("lernmoduleplugin","Bitte geben Sie das Modul ein.");
        }
        return $errors;
    }

}
