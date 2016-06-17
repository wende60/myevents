<?php

    /**
     * Addon MyEvents
     * @author  kgde@wendenburg.de
     * @package redaxo 5
     * @version $Id: boot.php, v 2.1.0
     */
    $this->setProperty('table_dates', rex::getTablePrefix() . $this->getProperty('package') . "_dates");
    $this->setProperty('table_content', rex::getTablePrefix() . $this->getProperty('package') . "_content");

    $this->setProperty('month_names', array(
        $this->i18n('jan'),
        $this->i18n('feb'),
        $this->i18n('mar'),
        $this->i18n('apr'),
        $this->i18n('may'),
        $this->i18n('jun'),
        $this->i18n('jul'),
        $this->i18n('aug'),
        $this->i18n('sep'),
        $this->i18n('oct'),
        $this->i18n('nov'),
        $this->i18n('dec')
    ));

    if (rex::isBackend() && rex::getUser()) {
        rex_view::addCssFile($this->getAssetsUrl('myevents.css'));
        rex_view::addJsFile($this->getAssetsUrl('myevents.js'));
    }

    # calling system within local environments may not find the environment mysql files
    # using MAMP on max OSX
    # $this->setProperty('mysqldump', '/Applications/MAMP/Library/bin/mysqldump');
    # default
    $this->setProperty('mysqldump', 'mysqldump');

