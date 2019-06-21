<?php

class H5PMigration extends Migration
{
    /**
     * Updates the H5P libraries in the h5plibs-folder to Stud.IP. It adds new libraries at first install
     * or updates them if the patch-number is higher than the already installed library with same major
     * and minor version number. New libraries are allowed/activated by default. Patches libraries don't
     * automatically acticated if they weren't activated before.
     * @throws Exception
     */
    protected function updateH5PLibs()
    {
        foreach (scandir(__DIR__."/../h5plibs") as $file) {
            if ($file[0] !== "." && is_dir(__DIR__."/../h5plibs/".$file)
                    && preg_match("/^(.*)\-(\d+)\.(\d+)$/", $file, $matches)) {

                $lib_data = array(
                    'machineName' => $matches[1],
                    'majorVersion' => $matches[2],
                    'minorVersion' => $matches[3]
                );

                $lib = H5PLib::findVersion($lib_data['machineName'], $lib_data['majorVersion'], $lib_data['minorVersion']);
                $lib_path = __DIR__."/../h5plibs/" . $file;
                $lib_json = json_decode(file_get_contents($lib_path . "/library.json"), true);
                if ($lib_json) {
                    if ($lib && ($lib_json['patchVersion'] > $lib['patch_version'])) {
                        //patch existing lib
                        $this->rcopy($lib_path, $lib->getPath());
                        $lib['patch_version'] = $lib_json['patchVersion'];
                        $lib->store();
                    } else {
                        //create new lib
                        $lib = new H5PLib();
                        $lib['name'] = $lib_data['machineName'];
                        $lib['major_version'] = $lib_data['majorVersion'];
                        $lib['minor_version'] = $lib_data['minorVersion'];
                        $lib['patch_version'] = $lib_json['patchVersion'];
                        $lib['allowed'] = 1;
                        $lib['runnable'] = $lib_json['runnable'] ? 1 : 0;
                        $lib->store();

                        if (file_exists($lib_path) && !file_exists($lib->getPath())) {
                            $this->rcopy($lib_path, $lib->getPath());
                        }
                    }
                }

            }
        }
    }

    protected function rcopy($src, $dst) {
        if (file_exists($dst)) {
            $this->rrmdir($dst);
        }
        if (is_dir($src)) {
            mkdir($dst);
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file !== "." && $file !== "..") {
                    $this->rcopy($src. "/" .$file, $dst . "/" .$file);
                }
            }
        } elseif (file_exists($src)) {
            copy($src, $dst);
        }
    }

    protected function rrmdir($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file !== "." && $file !== "..") {
                    $this->rrmdir($dir . "/" . $file);
                }
            }
            rmdir($dir);
        } elseif (file_exists($dir)) {
            unlink($dir);
        }
    }
}