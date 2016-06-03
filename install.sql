DROP TABLE IF EXISTS `%TABLE_PREFIX%myevents_dates`;
DROP TABLE IF EXISTS `%TABLE_PREFIX%myevents_content`;

CREATE TABLE `%TABLE_PREFIX%myevents_dates` (
    `id` int(11) NOT NULL auto_increment,
    `startdate` Date NOT NULL,   
    `enddate` Date NOT NULL, 
    `dates` text NOT NULL,
    `online` varchar(1) NOT NULL default 1,
    `dpltime` varchar(1) NOT NULL default 1,
    `adddates` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `%TABLE_PREFIX%myevents_content` (
    `cid` int(11) NOT NULL auto_increment,
    `event_id` int(11) NOT NULL,
    `clang` int(2) NOT NULL,
    `title` varchar(255) NOT NULL,
    `content` text,
    `local` varchar(255) NOT NULL,
    `addcontent` varchar(255) NOT NULL,
    PRIMARY KEY  (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
