<?php

# V5

require_once("../../../../wp-config.php");
if (!defined('SFCHARSET')) define('SFCHARSET', get_bloginfo('charset'));

sp_forum_ajax_support();

# --------------------------------------------------------------------------------------------------
#
#	Query Variables Passed:
#		'action'	set to 'userPhase', 'forumPhase', 'tagPhase', 'countPhase' and 'cleanPhase'
#		'phase'
#				'userPhase':   Number of batch starting 1 - split using $def->user_batch constant setting.
#				'forumPhase':  Number of forum to process starting 1.
#				'tagPhase':	   Not Used
#				'countPhase':  Not Used
#				'cleanPhase':  Not used
#
#	Notes:
#		Importing users: We can not guarantee that when new users are created they will have the same
#		user ID as in the source source users table so we need to compile a mapping array so that
#		the correct users are always credited for their posts. This is the case fo WP based forums
#
#		If a user being imported has the same login name as the user performing the import (who is
#		a recognised Simple:Press admin) then no new user record is created for that user.
#
#		Importing forums: If the source forum has no concept of groups - before the first forum is
#		imported, a single Simple:Press 'Group' is created named 'Forum Group'. All forums
#		are imported into that Group.
#
# --------------------------------------------------------------------------------------------------

# Load up the query variables --------------------------------------
$task = $_GET['action'];
if(isset($_GET['phase'])) $phase = $_GET['phase'];

# Grab control arrays ----------------------------------------------
	$source	= get_option('spi-dbase');
	$def 	= get_option('spi-def');
	$map 	= get_option('spi-map');

$wpdb->hide_errors();

# Direct the tasks -------------------------------------------------
switch($task) {
	case 'userPhase':
		spi_import_users($phase, $source, $def, $map);
		break;

	case 'forumPhase':
		if($def->parse_bbcode==true) {
			include_once(SPI_DIR.'parser/spimport-parser.php');
		}
		spi_import_forums($phase, $source, $def, $map);
		break;
	case 'tagPhase':
		spi_import_tags($source, $def);
		break;
	case 'countPhase':
		spi_build_postcounts();
		break;
	case 'cleanPhase':
		spi_clean_up();
		break;
}

die();

# End --------------------------------------------------------------

# ==================================================================
# IMPORT PRPARATORY ROUTINES
# ==================================================================

# ------------------------------------------------------------------
# spi_import_users()
# Prepare the next batch of users for import
#	$phase:		Number of the next batch
#	$source:	Array of source db connection settings
# ------------------------------------------------------------------
function spi_import_users($phase, $source, $def, $map) {
	global $current_user;

	if($map['tables']->users==false) return;

	# source table name variables
	$userTable	= "`".$source->dbname.'`.'.$source->dbuserpfix.$map['users']->table;

	# Create the user id mapping array and delete any old one
	$usermap = array();
	$usermap = get_option('spi-usermap');

	# Get limits for this batch
	$limit = " LIMIT ".$def->user_batch;
	if($phase > 1) $limit.= " OFFSET ".(($def->user_batch * ($phase-1))+1);

	# Select this batch of users
	$dbConn = mysqli_connect($source->dbhost, $source->dbuser, $source->dbpass);
	if(empty($map['users']->select)) {
	$sql = "SELECT * FROM $userTable ORDER BY ".$map['users']->ID." $limit";
	} else {
		$sql = spi_select($source, $map['users']->select).$limit;
	}

	$dbResourceUser = mysqli_query($dbConn, $sql);
	while($spiUser = mysqli_fetch_array($dbResourceUser, MYSQLI_ASSOC)) {
		# Is user an administrator
		$admin = false;
if(empty($spiUser['ID'])) $spiUser['ID']=0;
if(empty($current_user->ID)) $current_user->ID = -1;
		if($spiUser['ID']==$current_user->ID) $admin = true;
		spi_create_user($source, $def, $map, $spiUser, $admin, $usermap);
	}

	# Update the user map
	update_option('spi-usermap', $usermap);

	echo '<p>User/Member Data Created for <b>Members '.((($phase - 1) * $def->user_batch)+1).' - '.($phase * $def->user_batch).'</b></p>';
}

# ------------------------------------------------------------------
# spi_import_forums()
# Prepare the next forum for import
#	$phase:		Number of the next forum record
#	$source:	Array of source db connection settings
# ------------------------------------------------------------------
function spi_import_forums($phase, $source, $def, $map) {
	# source table name variables
	if($map['tables']->sfgroups) {
		$groupTable = "`".$source->dbname.'`.'.$source->dbbasepfix.$map['sfgroups']->table;
	}
	$forumTable = "`".$source->dbname.'`.'.$source->dbbasepfix.$map['sfforums']->table;
	$topicTable = "`".$source->dbname.'`.'.$source->dbbasepfix.$map['sftopics']->table;
	$postTable 	= "`".$source->dbname.'`.'.$source->dbbasepfix.$map['sfposts']->table;

	# Get the user id mapping array
	$usermap = array();
	$usermap = get_option('spi-usermap');

	# Create some Stats variables
	$topicCount = 0;
	$postCount  = 0;

	# Grab the forum record to be processed
	$limit = " LIMIT 1";
	if($phase > 1) $limit.= " OFFSET ".($phase-1);

	# Groups
	# --------------------------
	# Do we need to create a holding Group or does the source support them?
	if($phase == 1) {
		if($map['tables']->sfgroups == false) {
			if(spi_create_group('', $source, $def, $map) == false) {
				spi_clean_up();
				die();
			}
		} else {
			# Select the groups
			$dbConn = mysqli_connect($source->dbhost, $source->dbuser, $source->dbpass);
			if(empty($map['sfgroups']->select)) {
				$sql = "SELECT * FROM $groupTable ORDER BY ".$map['sfgroups']->group_id;
			} else {
				$sql = spi_select($source, $map['sfgroups']->select);
			}

			$dbResourceGroup = mysqli_query($dbConn, $sql);
			while($dbGroup = mysqli_fetch_array($dbResourceGroup, MYSQLI_ASSOC)) {
				if(spi_create_group($dbGroup, $source, $def, $map) == false) {
					spi_clean_up();
					die();
				}
			}
		}

		# weed out any topics with no valid forum id.
		spi_check_invalid_topics($source, $map, $topicTable);
	}

	# Forums
	# --------------------------
	# Select this forum
	$dbConn = mysqli_connect($source->dbhost, $source->dbuser, $source->dbpass);
	if(empty($map['sfforums']->select)) {
		$sql = "SELECT * FROM $forumTable ORDER BY ".$map['sfforums']->forum_id.$limit;
	} else {
		$sql = spi_select($source, $map['sfforums']->select).$limit;
	}
	$dbResourceForum = mysqli_query($dbConn, $sql);

	$dbForum = mysqli_fetch_array($dbResourceForum, MYSQLI_ASSOC);

	# Create the forum (pass $phase as a stand in for forum_seq if needed
	if(spi_create_forum($source, $def, $map, $dbForum, $dbForum[$map['sfforums']->forum_id], $phase) == false) {
		die();
	}

	# Topics
	# --------------------------
	# Get topics in forum and loop
	$dbConn = mysqli_connect($source->dbhost, $source->dbuser, $source->dbpass);
	if(empty($map['sftopics']->select)) {
		$sql = "SELECT * FROM $topicTable
				WHERE ".$map['sftopics']->forum_id." = ".$dbForum[$map['sfforums']->forum_id]." ORDER BY ".$map['sftopics']->topic_id.";";
	} else {
		$sql = spi_select($source, $map['sftopics']->select, $dbForum[$map['sfforums']->forum_id]);
	}
	$dbResourceTopic = mysqli_query($dbConn, $sql);
	while($dbTopic = mysqli_fetch_array($dbResourceTopic, MYSQLI_ASSOC)) {
		# Create the topic - return the slug
		$tslug = spi_create_topic($source, $def, $map, $dbTopic, $dbForum[$map['sfforums']->forum_id], $dbTopic[$map['sftopics']->topic_id], $usermap);
		if($tslug != false) {
			$topicCount++;

			# Posts
			# --------------------------
			# Get posts in topic and loop
			$dbConn = mysqli_connect($source->dbhost, $source->dbuser, $source->dbpass);
			if(empty($map['sfposts']->select)) {
				$sql = "SELECT * FROM $postTable
						WHERE ".$map['sfposts']->topic_id." = ".$dbTopic[$map['sftopics']->topic_id]." ORDER BY ".$map['sfposts']->post_id.";";
			} else {
				$sql = spi_select($source, $map['sfposts']->select, $dbForum[$map['sfforums']->forum_id], $dbTopic[$map['sftopics']->topic_id]);
			}
			$dbResourcePost = mysqli_query($dbConn, $sql);

			while($dbPost = mysqli_fetch_array($dbResourcePost, MYSQLI_ASSOC)) {
				# Create the post
				spi_create_post($source, $def, $map, $dbPost, $dbForum[$map['sfforums']->forum_id], $dbTopic[$map['sftopics']->topic_id], $dbPost[$map['sfposts']->post_id], $usermap);
				$postCount++;
			}
			# Build Topic/Post Index
			sp_build_post_index($dbTopic[$map['sftopics']->topic_id]);
		}
	}

	# Build Forum/Topic Index
	sp_build_forum_index($dbForum[$map['sfforums']->forum_id]);

	echo '<p>Forum Created: <b>'.$dbForum[$map['sfforums']->forum_name].'</b> (Topics: '.$topicCount.' Posts: '.$postCount.')</p>';
}

# ------------------------------------------------------------------
# spi_import_tags()
# Prepare the tag records for import
#	$source:	Array of source db connection settings
# ------------------------------------------------------------------
function spi_import_tags($source, $def) {
	if($def->wp_tags == false) return;

	if(!defined('SPTAGS')) return;

	# source table name variables
	$termTable = "`".$source->dbname.'`.'.$source->dbbasepfix.'terms';
	$termTaxonomyTable = "`".$source->dbname.'`.'.$source->dbbasepfix.'term_taxonomy';
	$termRelationshipsTable = "`".$source->dbname.'`.'.$source->dbbasepfix.'term_relationships';

	# Select this tag data
	$dbConn = mysqli_connect($source->dbhost, $source->dbuser, $source->dbpass);
	$sql = "SELECT name as tag_name, object_id as topic_id
			FROM $termTaxonomyTable
			JOIN $termTable ON $termTaxonomyTable.term_id = $termTable.term_id
			JOIN $termRelationshipsTable ON $termTaxonomyTable.term_taxonomy_id = $termRelationshipsTable.term_taxonomy_id
			WHERE (taxonomy = 'bb_topic_tag' OR taxonomy = 'topic-tag') ORDER BY object_id";

	$dbResourceTag = mysqli_query($dbConn, $sql);
	while($bbTag = mysqli_fetch_array($dbResourceTag, MYSQLI_ASSOC)) {
		# Create the tag
		spi_create_tag($bbTag);
	}

	echo '<p><b>Topic Tags</b> Created</p>';
}


# ==================================================================
# SP DATA CREATION ROUTINES
# ==================================================================

# ------------------------------------------------------------------
# spi_create_user()
# Create the user/member records
#	$spiUser:	Indexed array of source user record
#	$admin:		True if a WP Admin
#	$usermap:	ID Mapping array (passed by reference)
# ------------------------------------------------------------------
function spi_create_user($source, $def, $map, $spiUser, $admin, &$usermap) {
	# 1 - Check the WP Users table to see if login name is already a user
	# 2 - Create the new user record
	# 3 - Place in mapping array using new user ID
	# 4 - Add admin data if required
	# 5 - Create SP Members data
	# 6 - Update any source usermeta data appropriate to sfmembers

	global $wpdb;

	# 1 - Check is already exists
	$sql = "SELECT ID FROM ".$wpdb->prefix."users WHERE user_login='".$spiUser[$map['users']->user_login]."'";
	$wpUser = $wpdb->get_var($sql);
	if(empty($wpUser)) {
		# 2 - Create new user record

		$user_login			= esc_sql(spi_data($source, $spiUser, $map['users']->user_login));
		$user_nicename		= sp_filter_name_save(spi_data($source, $spiUser, $map['users']->user_nicename));
		$user_email			= sp_filter_email_save(spi_data($source, $spiUser, $map['users']->user_email));
		$user_url			= sp_filter_url_save(spi_data($source, $spiUser, $map['users']->user_url));
		$user_registered	= spi_data($source, $spiUser, $map['users']->user_registered);
		$display_name		= sp_filter_name_save(spi_data($source, $spiUser, $map['users']->display_name));

		# Sort password
		switch($source->dbpassoption) {
			case 'copy':
				$user_pass	= spi_data($source, $spiUser, $map['users']->user_pass);
				break;

			case 'create':
				$user_pass = wp_generate_password();
				break;

			case 'ulogin':
				$user_pass = wp_hash_password(esc_attr($user_login));
				break;
		}

		if(empty($display_name)) {
			$display_name = $user_nicename;
		}

		if($source->utf8encode==true) {
			$user_login = utf8_encode($user_login);
			$user_nicename = utf8_encode($user_nicename);
			$user_email = utf8_encode($user_email);
			$user_url = utf8_encode($user_url);
			$display_name = utf8_encode($display_name);
		}
		$sql =	"INSERT INTO ".$wpdb->prefix."users (user_login, user_pass, user_nicename, user_email, user_url, user_registered, display_name)
				 VALUES ('$user_login', '$user_pass', '$user_nicename', '$user_email', '$user_url', '$user_registered', '$display_name');";

		$success = $wpdb->query($sql);
		if($wpdb->last_error) {
			$spi_errors 	= array();
			$spi_errors 	= get_option('spi_errors');
			$spi_errors[] = 'Creation of User: <b>'.esc_sql($user_login).'</b> Failed';
			update_option('spi_errors', $spi_errors);
			return false;
		}

		$wpUser = $wpdb->insert_id;
		$newuser = true;
	} else {
		$newuser = false;
	}

	if($wpUser) {
		# 3 - add to user map
		$usermap[$spiUser[$map['users']->ID]] = $wpUser;

		# 4 - Is user an admin?
		if($admin) {
			# First check for create new capabilities record if needed
			$caps = get_user_meta(1, $wpdb->prefix.'capabilities', true);
			if(!array_key_exists('SPF Manage Admins', $caps)) {
				$caps = array(
					"administrator"				=>"1",
					"SPF Manage Configuration"	=>"1",
					"SPF Manage Options"		=>"1",
					"SPF Manage Forums"			=>"1",
					"SPF Manage User Groups"	=>"1",
					"SPF Manage Permissions"	=>"1",
					"SPF Manage Components"		=>"1",
					"SPF Manage Admins"			=>"1",
					"SPF Manage Users"			=>"1",
					"SPF Manage Profiles"		=>"1",
					"SPF Manage Toolbox"		=>"1",
					"SPF Manage Plugins"		=>"1",
					"SPF Manage Themes"			=>"1"
				);
				update_user_meta($wpUser, $wpdb->prefix.'capabilities', $caps);
				update_user_meta($wpuser, $wpdb->prefix.'user_level', 10);

				# Now the SP Admin Record
				$opts = array(
					"sfnotify"			=>0,
					"sfstatusmsgtext"	=>"",
					"colors"=>array(
						"submitbg"		=>"21759b",
						"submitbgt"		=>"eaf2fa",
						"bbarbg"		=>"f1f1f1",
						"bbarbgt"		=>"21759b",
						"formbg"		=>"ffffff",
						"formbgt"		=>"333333",
						"panelhead"		=>"dfdfdf",
						"panelheadt"	=>"21759b",
						"panelbg"		=>"ffffff",
						"panelbgt"		=>"333333",
						"tabhead"		=>"f1f1f1",
						"tabheadt"		=>"333333",
						"tabrowmain"	=>"ffffff",
						"tabrowmaint"	=>"333333",
						"tabrowsub"		=>"ffffff",
						"tabrowsubt"	=>"333333",
						"panelsubbg"	=>"dfdfdf",
						"panelsubbgt"	=>"333333",
						"formtabhead"	=>"f1f1f1",
						"formtabheadt"	=>"333333"
					)
				);
				sp_update_member_item($wpUser, 'admin_options', $opts);
				sp_update_member_item($wpUser, 'admin', 1);
			}
		} else {
			if($newuser) {
				# Mark as subscriber
				$caps = array(
					"subscriber"				=>"1"
				);
				update_user_meta($wpUser, $wpdb->prefix.'capabilities', $caps);
			}
		}
		# 5 - Create sfmember record
		if($newuser) {
			sp_create_member_data($wpUser);
		}

		# 6 - Can we bring any usermeta data in?
		if($map['tables']->usermeta == true) {
			foreach($map['usermeta'] as $key=>$item) {
				$data = spi_data($source, array($key), $item, 0, 0, 0, $wpUser);
				if($data) {
				 	add_user_meta($wpUser, $key, $data, true);
				}
			}
		}
	}
}

# ------------------------------------------------------------------
# spi_create_group()
# Create the holding Group
# ------------------------------------------------------------------
function spi_create_group($dbGroup, $source, $def, $map) {
	global $wpdb;

	# Do we need groups or just a single holding group?
	if($map['tables']->sfgroups == true) {
		# Map and assign the group data
		$group_id 	= spi_data($source, $dbGroup, $map['sfgroups']->group_id);
		$group_name = sp_filter_title_save(spi_data($source, $dbGroup, $map['sfgroups']->group_name));
		$group_desc = sp_filter_text_save(spi_data($source, $dbGroup, $map['sfgroups']->group_desc));
		$group_seq 	= spi_data($source, $dbGroup, $map['sfgroups']->group_seq);
	} else {
		# SP Needs a Group - create a holding record named 'Forum Group'
		$group_id 	= 1;
		$group_name	= 'Forum Group';
		$group_desc	= 'Imported Forums';
		$group_seq	= 1;
	}

	if($source->utf8encode==true) {
		$group_name = utf8_encode($group_name);
		$group_desc = utf8_encode($group_desc);
	}

	$sql =	"INSERT INTO ".SFGROUPS." (group_id, group_name, group_desc, group_seq)
			 VALUES ($group_id, '$group_name', '$group_desc', $group_seq);";

    $success = $wpdb->query($sql);
	if($wpdb->last_error) {
    	$spi_errors 	= array();
    	$spi_errors 	= get_option('spi_errors');
    	$spi_errors[] 	= 'Creation of <b>'.esc_attr($group_name).'</b> Group failed';
    	update_option('spi_errors', $spi_errors);
    	return false;
    }

	# Finally create the default User Group/Permission records
	$wpdb->query("INSERT INTO ".SFDEFPERMISSIONS." (group_id, usergroup_id, permission_role)
				  VALUES(".$group_id.", 1, 3);");
	$wpdb->query("INSERT INTO ".SFDEFPERMISSIONS." (group_id, usergroup_id, permission_role)
				  VALUES(".$group_id.", 2, 4);");
	$wpdb->query("INSERT INTO ".SFDEFPERMISSIONS." (group_id, usergroup_id, permission_role)
				  VALUES(".$group_id.", 3, 6);");

	echo '<p>Forum Group <b>'.esc_attr($group_name).'</b> Created</p>';
	return true;
}

# ------------------------------------------------------------------
# spi_create_forum()
# Create an imported forum
#	$dbForum:	Indexed array of source forum record
# ------------------------------------------------------------------
function spi_create_forum($source, $def, $map, $dbForum, $fid, $seq) {
	global $wpdb;

	# Map and assign the forum data
	$forum_id 	= spi_data($source, $dbForum, $map['sfforums']->forum_id, $fid);
	$forum_name = sp_filter_title_save(spi_data($source, $dbForum, $map['sfforums']->forum_name, $fid));
	$forum_seq 	= spi_data($source, $dbForum, $map['sfforums']->forum_seq, $fid);
	$forum_desc = sp_filter_text_save(spi_data($source, $dbForum, $map['sfforums']->forum_desc, $fid));
	$group_id 	= spi_data($source, $dbForum, $map['sfforums']->group_id, $fid);

	if($forum_seq == 0) $forum_seq = $seq;

	if($source->utf8encode==true) {
		$forum_name = utf8_encode($forum_name);
		$forum_desc = utf8_encode($forum_desc);
	}

	$forum_slug = sp_create_slug($forum_name, true, SFFORUMS, 'forum_slug');

	if(defined('SPTAGS') && $def->wp_tags == true) {
		$sql = 	"INSERT INTO ".SFFORUMS." (forum_id, forum_name, forum_slug, forum_desc, group_id, forum_seq, use_tags)
				 VALUES ($forum_id, '$forum_name', '$forum_slug', '$forum_desc', $group_id, $forum_seq, 1);";
	} else {
		$sql = 	"INSERT INTO ".SFFORUMS." (forum_id, forum_name, forum_slug, forum_desc, group_id, forum_seq)
				 VALUES ($forum_id, '$forum_name', '$forum_slug', '$forum_desc', $group_id, $forum_seq);";
	}

    $success = $wpdb->query($sql);
	if($wpdb->last_error) {
		$spi_errors 	= array();
		$spi_errors 	= get_option('spi_errors');
		$spi_errors[] 	= 'Creation of Forum: <b>'.esc_attr($forum_name).'</b> Failed';
		update_option('spi_errors', $spi_errors);
		return false;
    }

	# Create the default User Group/Permission records
	$wpdb->query("INSERT INTO ".SFPERMISSIONS." (forum_id, usergroup_id, permission_role)
				  VALUES($forum_id, 1, 3);");
	$wpdb->query("INSERT INTO ".SFPERMISSIONS." (forum_id, usergroup_id, permission_role)
				  VALUES($forum_id, 2, 4);");
	$wpdb->query("INSERT INTO ".SFPERMISSIONS." (forum_id, usergroup_id, permission_role)
				  VALUES($forum_id, 3, 6);");

	return true;
}

# ------------------------------------------------------------------
# spi_create_topic()
# Create an imported topic
#	$dbTopic:	Indexed array of source topic record
#	$usermap:	Mapping array of source and target user Ids
# ------------------------------------------------------------------
function spi_create_topic($source, $def, $map, $dbTopic, $fid, $tid, $usermap) {
	global $wpdb;

	# Map and assign the topic data
	$topic_id		= spi_data($source, $dbTopic, $map['sftopics']->topic_id, $fid, $tid);
	$topic_name		= sp_filter_title_save(spi_data($source, $dbTopic, $map['sftopics']->topic_name, $fid, $tid));
	$topic_date		= spi_data($source, $dbTopic, $map['sftopics']->topic_date, $fid, $tid);
	$topic_status	= spi_data($source, $dbTopic, $map['sftopics']->topic_status, $fid, $tid);
	$forum_id		= spi_data($source, $dbTopic, $map['sftopics']->forum_id, $fid, $tid);
	$user_id		= $usermap[$dbTopic[$map['sftopics']->user_id]];
	$topic_pinned	= spi_data($source, $dbTopic, $map['sftopics']->topic_pinned, $fid, $tid);
	$topic_opened	= spi_data($source, $dbTopic, $map['sftopics']->topic_opened, $fid, $tid);
	$post_id		= spi_data($source, $dbTopic, $map['sftopics']->post_id, $fid, $tid);

	if($source->utf8encode==true) {
		$topic_name = utf8_encode($topic_name);
	}

	$topic_slug	= sp_create_slug($topic_name, true, SFTOPICS, 'topic_slug');

	if(empty($user_id)) $user_id = 'NULL';
	if($topic_pinned > 1) $topic_pinned = 1;

	$sql = 	"INSERT INTO ".SFTOPICS." (topic_id, topic_name, topic_date, topic_status, forum_id, user_id, topic_pinned, post_id, topic_slug)
			 VALUES ($topic_id, '$topic_name', '$topic_date', $topic_status, $forum_id, $user_id, $topic_pinned, $post_id, '$topic_slug');";

    $success = $wpdb->query($sql);
	if($wpdb->last_error) {
		$spi_errors 	= array();
		$spi_errors 	= get_option('spi_errors');
		$spi_errors[] = 'Creation of Topic: <b>'.esc_sql($topic_name).'</b> Failed';
		update_option('spi_errors', $spi_errors);
		return false;
    } else {
    	return $topic_slug;
    }
}

# ------------------------------------------------------------------
# spi_create_post()
# Create an imported post
#	$dbPost:	Indexed array of source post record
#	$usermap:	Mapping array of source and target user Ids
# ------------------------------------------------------------------
function spi_create_post($source, $def, $map, $dbPost, $fid, $tid, $pid, $usermap)
{
	global $wpdb;

	$post_id		= spi_data($source, $dbPost, $map['sfposts']->post_id, $fid, $tid, $pid);
	$post_date		= spi_data($source, $dbPost, $map['sfposts']->post_date, $fid, $tid, $pid);
	$topic_id		= spi_data($source, $dbPost, $map['sfposts']->topic_id, $fid, $tid, $pid);
	$forum_id		= spi_data($source, $dbPost, $map['sfposts']->forum_id, $fid, $tid, $pid);
	$user_id		= $usermap[$dbPost[$map['sfposts']->user_id]];
	$post_index		= spi_data($source, $dbPost, $map['sfposts']->post_index, $fid, $tid, $pid);
	$poster_ip		= spi_data($source, $dbPost, $map['sfposts']->poster_ip, $fid, $tid, $pid);

	if(isset($map['sfposts']->guest_name) ? $guest_name = spi_data($source, $dbPost, $map['sfposts']->guest_name, $fid, $tid, $pid) : $guest_name='');
	if(isset($map['sfposts']->guest_email) ? $guest_email = spi_data($source, $dbPost, $map['sfposts']->guest_email, $fid, $tid, $pid) : $guest_email='');

	$post_content = spi_data($source, $dbPost, $map['sfposts']->post_content, $fid, $tid, $pid);
	$post_content = stripslashes($post_content);
	if($source->utf8encode==true) {
		$post_content=utf8_encode($post_content);
		$guest_name=utf8_encode($guest_name);
		$guest_email=utf8_encode($guest_email);
	}

	if($def->parse_bbcode==true) {
		$post_content = spi_parse_bbcode($post_content);
	}
	$post_content 	= sp_filter_content_save($post_content, 'new');

	if(empty($user_id)) $user_id = 'NULL';

	$sql = 	"INSERT INTO ".SFPOSTS." (post_id, post_content, post_date, topic_id, forum_id, user_id, post_index, poster_ip, guest_name, guest_email)
			 VALUES ($post_id, '$post_content', '$post_date', $topic_id, $forum_id, $user_id, $post_index, '$poster_ip', '$guest_name', '$guest_email');";

    $success = $wpdb->query($sql);
	if($wpdb->last_error)
    {
		$spi_errors 	= array();
		$spi_errors 	= get_option('spi_errors');
		$spi_errors[] 	= 'Creation of Post: <b>'.$post_id.'</b> Failed';
		update_option('spi_errors', $spi_errors);
		die();
    }
}

# ------------------------------------------------------------------
# spi_create_tag()
# Create an imported tag
#	$bbTag:	Indexed array of source tag record
# ------------------------------------------------------------------
function spi_create_tag($bbTag)
{
	$tag = array();
	$tag[] = $bbTag['tag_name'];

	include_once(SPTLIBDIR.'sp-tags-database.php');
	sp_tags_new_tags($bbTag['topic_id'], $tag);
}

# ------------------------------------------------------------------
# spi_build_postcounts()
# Set the users post counts
# ------------------------------------------------------------------
function spi_build_postcounts()
{
	global $wpdb;

	# Get user list
	$users = $wpdb->get_col("SELECT ID FROM ".$wpdb->prefix."users");
	if($users)
	{
		foreach($users as $user)
		{
			$postcount = $wpdb->get_var("SELECT COUNT(post_id) FROM ".$wpdb->prefix."sfposts WHERE user_id=".$user);
			if(empty($postcount)) $postcount = 0;
			sp_update_member_item($user, 'posts', $postcount);
		}
    	echo '<p>Members <b>Post Counts</b> Updated</p>';
	}
}

# ------------------------------------------------------------------
# spi_check_invalid_topics()
# Check for topics with forum id of zero
#	$source:		Array of source db connection settings
#	$topicTable:	resolved table name
# ------------------------------------------------------------------
function spi_check_invalid_topics($source, $map, $topicTable)
{
	$spi_errors 	= array();
	$spi_errors 	= get_option('spi_errors');
//==>
return;
//==>
	# Get topics in forum with bad id and loop
	$dbConn = mysqli_connect($source->dbhost, $source->dbuser, $source->dbpass);
	$sql = "SELECT ".$map['sftopics']->topic_id." FROM $topicTable
			WHERE ".$map['sftopics']->forum_id." = 0 ORDER BY ".$map['sftopics']->topic_id.";";

	$dbResourceTopic = mysqli_query($dbConn, $sql);

	while($dbTopic = mysqli_fetch_array($dbResourceTopic, MYSQLI_ASSOC))
	{
		$spi_errors[] = 'Topic ID: <b>'.$dbTopic[$map['sftopics']->topic_id].'</b> has no valid Forum ID. The Topic and any Posts have NOT been imported';
	}
	update_option('spi_errors', $spi_errors);
}

# ------------------------------------------------------------------
# spi_clean_up()
# Final Housekeeping
# ------------------------------------------------------------------
function spi_clean_up()
{
	global $wpdb;

	$wpdb->flush;

	# reset the auths cache
	sp_reset_auths();

	# Dsiplay success counts
	echo '<p><b>IMPORTED:<br />';

	$users = get_option('spi-usermap');
	$count = count($users);
	if(empty($count)) $count = 0;
	echo '&nbsp;&nbsp;&nbsp;'.$count.' Users<br />';

	$count = $wpdb->get_var("SELECT COUNT(group_id) FROM ".$wpdb->prefix."sfgroups");
	if(empty($count)) $count = 0;
	echo '&nbsp;&nbsp;&nbsp;'.$count.' Groups<br />';

	$count = $wpdb->get_var("SELECT COUNT(forum_id) FROM ".$wpdb->prefix."sfforums");
	if(empty($count)) $count = 0;
	echo '&nbsp;&nbsp;&nbsp;'.$count.' Forums<br />';

	$count = $wpdb->get_var("SELECT COUNT(topic_id) FROM ".$wpdb->prefix."sftopics");
	if(empty($count)) $count = 0;
	echo '&nbsp;&nbsp;&nbsp;'.$count.' Topics<br />';

	$count = $wpdb->get_var("SELECT COUNT(post_id) FROM ".$wpdb->prefix."sfposts");
	if(empty($count)) $count = 0;
	echo '&nbsp;&nbsp;&nbsp;'.$count.' Posts<br />';

	if($def->wp_tags == true) {
		$count = $wpdb->get_var("SELECT COUNT(tag_id) FROM ".$wpdb->prefix."sftags");
		if(empty($count)) $count = 0;
		echo '&nbsp;&nbsp;&nbsp;'.$count.' Tags<br />';
	}

	echo '</b></p>';

	# Display any errors found
	$spi_errors 	= array();
	$spi_errors 	= get_option('spi_errors');
	if(!empty($spi_errors))
	{
		echo '<p><b>IMPORT FAILURES:</b><br />';
		foreach($spi_errors as $fail)
		{
			echo '&nbsp;&nbsp;&nbsp;'.$fail.'<br />';
		}
		echo '</p>';
	}

	# Remove user map array & erros
	delete_option('spi-usermap');
	delete_option('spi_errors');
}

# ------------------------------------------------------------------
# spi_select($source, $sql)
# Creates sql select query from def table select
# ------------------------------------------------------------------
function spi_select($source, $sql, $forum_id=0, $topic_id=0) {
	$sql = str_replace('<%dbname%>', '`'.$source->dbname.'`', $sql);
	$sql = str_replace('<%dbuserpfix%>', $source->dbuserpfix, $sql);
	$sql = str_replace('<%dbbasepfix%>', $source->dbbasepfix, $sql);
	$sql = str_replace('<%forum_id%>', $forum_id, $sql);
	$sql = str_replace('<%topic_id%>', $topic_id, $sql);
	return $sql;
}

# ------------------------------------------------------------------
# spi_data($source, )
# Check and fill data
# ------------------------------------------------------------------
function spi_data($source, $row, $column, $forum_id=0, $topic_id=0, $post_id=0, $user_id=0) {
	$data = null;

	# is there data for the column in the row
	if(array_key_exists($column, $row)) {
		$data = $row[$column];
	} else {
		# any special instructions?
		$special = explode(':', $column);
		switch($special[0]) {

			case '#default':
				$data = $special[1];
				break;

			case '#inherit':
				$lookup = explode('%', $special[1]);
				if($lookup[0]=='sfforums' && $lookup[1]=='forum_id') $data=$forum_id;
				if($lookup[0]=='sftopics' && $lookup[1]=='topic_id') $data=$topic_id;
				if($lookup[0]=='sfposts' && $lookup[1]=='post_id') $data=$post_id;
				break;

			case '#timestamp':
				$ts = $row[$special[1]];
				$data = date("Y-m-d H:i:s", intval($ts));
				break;

			case '#query':
				$sql = $special[1];
				$sql = str_replace('<%dbname%>', '`'.$source->dbname.'`', $sql);
				$sql = str_replace('<%dbuserpfix%>', $source->dbuserpfix, $sql);
				$sql = str_replace('<%dbbasepfix%>', $source->dbbasepfix, $sql);
				$sql = str_replace('<%forum_id%>', $forum_id, $sql);
				$sql = str_replace('<%topic_id%>', $topic_id, $sql);
				$sql = str_replace('<%post_id%>', $post_id, $sql);
				$sql = str_replace('<%user_id%>', $user_id, $sql);
				$dbConn = mysqli_connect($source->dbhost, $source->dbuser, $source->dbpass, $source->dbname);
				if(!$dbConn) echo 'NO CONNECTION';
				$dbResourceQuery = mysqli_query($dbConn, $sql);
				$dbData = mysqli_fetch_array($dbResourceQuery, MYSQLI_NUM);
				$data = $dbData[0];
				mysqli_close($dbConn);
				break;
		}
	}
	return $data;
}

?>