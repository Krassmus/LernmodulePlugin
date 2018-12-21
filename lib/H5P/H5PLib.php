<?php

class H5PLib extends SimpleORMap
{
    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_h5plibs';
        parent::configure($config);
    }

    static public function findVersion($name, $major_version, $minor_version)
    {
        return self::findOneBySQL("name = :name AND major_version = :major AND minor_version = :minor", array(
            'name' => $name,
            'major' => $major_version,
            'minor' => $minor_version
        ));
    }

    public function getPath()
    {
        if (Config::get()->LERNMODUL_DATA_PATH) {
            $datafolder = Config::get()->LERNMODUL_DATA_PATH;
        } else {
            $datafolder = realpath(__DIR__."/../../..")."/LernmodulePluginData";
            if (!file_exists($datafolder)) {
                $success = mkdir($datafolder);
                if (!$success) {
                    throw new Exception("Konnte Verzeichnis LernmodulePluginData nicht anlegen.");
                }
            }
        }
        $datafolder .= "/h5plibs";
        if (!file_exists($datafolder)) {
            $success = mkdir($datafolder);
            if (!$success) {
                throw new Exception("Konnte Verzeichnis LernmodulePluginData/h5plibs nicht anlegen.");
            }
        }
        return $datafolder."/".$this['name']."-".$this['major_version'].".".$this['minor_version'];
    }

    public function countUsage()
    {
        $statement = DBManager::get()->prepare("
            SELECT COUNT(*) 
            FROM lernmodule_h5plib_module
            WHERE lib_id = ?
        ");
        $statement->execute(array($this->getId()));
        return $statement->fetch(PDO::FETCH_COLUMN, 0);
    }
}