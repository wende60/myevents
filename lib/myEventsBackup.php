<?php

    /**
     * Addon MyEvents
     * @author  kgde@wendenburg.de
     * @package redaxo 5
     * @version $Id: myEventsBackup.php, v 2.0.0
     */
    class myEventsBackup {

        private $dbConfig       =  array();
        private $user           =  false;
        private $pass           =  false;
        private $host           =  false;
        private $name           =  false;
        private $myeventsPath   =  false;
        private $list           =  array();
        private $tables         =  array();

        public $error           =  array();
        public $message         =  array();

        function __construct() {
            $this->dbConfig     =  rex::getProperty('db');
            $this->user         =  $this->dbConfig[1]['login'];
            $this->pass         =  $this->dbConfig[1]['password'];
            $this->host         =  $this->dbConfig[1]['host'];
            $this->name         =  $this->dbConfig[1]['name'];
            $this->myeventsPath =  rex_path::addonData('myevents');
            $this->tables       =  array(
                'dates' =>      rex_addon::get('myevents')->getProperty('table_dates'),
                'content' =>    rex_addon::get('myevents')->getProperty('table_content'),
            );
        }

        public function createBackup() {
            if ($this->checkBackupDirectory() && $this->checkMysqlDump()) {
                foreach($this->tables as $table) {
                    /*
                        echo rex_addon::get('myevents')->getProperty('mysqldump') .
                        " --host=" . $this->host . " --user=" . $this->user .
                        " --password=" . $this->pass . " " . $this->name . " " . $table .
                        " | gzip > " . $bakfile;
                    */

                    $filename   =  $table . "_" . date("Y-m-d-H-i-s") . ".sql.gz";
                    $bakfile    =  $this->myeventsPath . "mysqldump/" . $filename;
                    $command    =  rex_addon::get('myevents')->getProperty('mysqldump') .
                                    " --host=" . $this->host . " --user=" . $this->user .
                                    " --password=" . $this->pass . " " . $this->name . " " . $table .
                                    " | gzip > " . $bakfile;
                    shell_exec($command);
                    chmod ($bakfile , 0777);
                    array_push($this->message, $filename . ' angelegt');
                }
                return true;
            } else {
                return false;
            }
        }

        private function checkMysqlDump() {
            $result =  shell_exec(rex_addon::get('myevents')->getProperty('mysqldump') . ' --version');
            if ($result === '') {
                array_push($this->error,
                    "Kein mysqldump gefunden!
                    Bei Entwicklungsumgebungen wie XAMPP oder MAMP den Pfad in der boot.php eintragen"
                );
                return false;
            } else {
                return true;
            }
        }

        private function checkBackupDirectory() {

            if (!is_dir($this->myeventsPath)) {
                $myEventData =  mkdir($this->myeventsPath, 0777);
                if (!$myEventData || !is_writeable($this->myeventsPath)) {
                    array_push($this->error, 'Konnte das Verzeichnis ' . $this->myeventsPath . ' nicht anlegen!');
                    return false;
                }
            }
            $myEventsSqldummp =  mkdir($this->myeventsPath . 'mysqldump', 0777);
            return true;
        }

        public function listBackups() {
            if (is_dir($this->myeventsPath . 'mysqldump') && $handle = opendir($this->myeventsPath . 'mysqldump')) {
                while (false !== ($entry = readdir($handle))) {
                    if (substr($entry,0,1) !== '.') {
                        array_push($this->list, $entry);
                    }
                }
                closedir($handle);
            }
            return count($this->list)? $this->list : false;
        }

        public function deleteBackup() {

            if (is_dir($this->myeventsPath . 'mysqldump') && $handle = opendir($this->myeventsPath . 'mysqldump')) {
                while (false !== ($entry = readdir($handle))) {
                    unlink($this->myeventsPath . 'mysqldump/' . $entry);
                }
                closedir($handle);
            }

            if (rmdir($this->myeventsPath . 'mysqldump')) {
                array_push($this->message, 'Alle Backups gelöscht!');
                return true;
            } else {
                array_push($this->error, 'Das Verzeichnis ' . $this->myeventsPath . 'mysqldump konnte nicht gelöscht werden!');
                return false;
            }
        }
    };