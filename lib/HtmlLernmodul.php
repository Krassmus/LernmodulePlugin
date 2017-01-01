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

    public function getViewerTemplate($attempt)
    {
        $actions = new ActionsWidget();
        $actions->addLink(
            _("Vollbild"),
            "#",
            Icon::create("play", "clickable"),
            array('onClick' => "STUDIP.Lernmodule.requestFullscreen(); return false;")
        );
        Sidebar::Get()->addWidget($actions);

        $myorigin = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        $myorigin .= '://'.$_SERVER['SERVER_NAME'];

        $templatefactory = new Flexi_TemplateFactory(__DIR__."/../views");
        $template = $templatefactory->open("html/view.php");
        $template->set_attribute("module", $this);
        $template->set_attribute("attempt", $attempt);
        $template->set_attribute("myorigin", $myorigin);
        return $template;
    }

    public function getStartURL($secret = null)
    {
        return $this->getURL()."/".($this['customdata']['start_file'] ?: "index.html").($secret ? "?vanillalm_secret=".urlencode($secret): "");
    }
}