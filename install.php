<?php

    /**
     * Addon MyEvents
     * @author  kgde@wendenburg.de
     * @package redaxo 5
     * @version $Id: install.php, v 2.0.0
     */

    $error  = "";

    // do whatever...

    if($error !== '') {
        $this->setProperty('installmsg', $error);
        $this->setProperty('install', false);
    } else {
        $this->setProperty('install', true);
    }
?>