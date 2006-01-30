#
# Table structure for table `tagmemo`
#

CREATE TABLE `tagmemo` (
`tagmemo_id` smallint(5) unsigned NOT NULL auto_increment,
`uid` smallint(5) unsigned NOT NULL,
`title` varchar(120) NOT NULL,
`content` text NOT NULL,
`public` enum('0','1') NOT NULL,
`timestamp` int(10) unsigned NOT NULL,
PRIMARY KEY (`tagmemo_id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `tagmemo_tag`
#

CREATE TABLE `tagmemo_tag` (
`tag_id` smallint(5) unsigned NOT NULL auto_increment,
#`tagmemo_id` smallint(5) unsigned NOT NULL,
`tag` varchar(20) NOT NULL,
PRIMARY KEY (`tag_id`)
) TYPE=MyISAM;


#
# Table structure for table `tagmemo_rel`
#

CREATE TABLE `tagmemo_rel` (
`tag_id` smallint(5) unsigned NOT NULL,
`tagmemo_id` smallint(5) unsigned NOT NULL,
PRIMARY KEY (`tag_id`,`tagmemo_id`)
) TYPE=MyISAM;
