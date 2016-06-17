<?php

    /**
     * Addon MyEvents
     * @author  kgde@wendenburg.de
     * @package redaxo 5
     * @version $Id: uninstall.php, v 2.1.0
     */

    $error  = "";

    // do whatever...

    if($error !== '') {
        $this->setProperty('installmsg', $error);
        $this->setProperty('install', true);
    } else {
        $this->setProperty('install', false);
    }

?>