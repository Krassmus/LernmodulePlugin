<?php

class HtmlLernmodul extends Lernmodul implements CustomLernmodul
{
    static public function detect($path)
    {
        return true;
    }

    public function afterInstall()
    {
        if (!$this['customdata']['start_file'] || !file_exists($this->getPath()."/".$this['customdata']['start_file'])) {
            if (file_exists($this->getPath()."/index.html")) {
                $this['customdata']['start_file'] = "index.html";
            } else {
                $files = $this->scanForFiletypes(array("html", "htm"));
                $this['customdata']['start_file'] = $files[0];
            }
        }
    }

    public function getEditTemplate()
    {
        $templatefactory = new Flexi_TemplateFactory(__DIR__."/../views");
        $template = $templatefactory->open("edittemplates/html.php");
        $template->set_attribute("module", $this);
        return $template;
    }

    public function getStartURL()
    {
        return $this->getURL()."/".($this['customdata']['start_file'] ?: "index.html");
    }
}