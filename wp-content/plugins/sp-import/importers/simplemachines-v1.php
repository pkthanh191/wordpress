<?php
/*
NAME	: Simple Machines V1 Importer
FILE	: simplemachines-v1.php
ICON	: simplemachines-v1.png
INFO	: Imports Users, Groups, Forums, Topics and Posts
TAG		: Simple Machines (version 1)
*/

# ------------------------------------------------------------------
#
#	Simple Machines Version 1 to SP Import Definition File
#
# ------------------------------------------------------------------

# ------------------------------------------------------------------
# spi_collect_db_settings()
# The form to collect the source data settings
# ------------------------------------------------------------------
function spi_collect_db_settings() {

	# Source DB Settings
	# ------------------------------------------------------------------

	$source = get_option('spi-dbase');
	if(empty($source)) {

		# Source DB Connection Settings

		$source->dbname			= '';
		$source->dbhost			= '';
		$source->dbuser			= '';
		$source->dbpass			= '';
		$source->dbbasepfix		= '';
		$source->dbuserpfix		= '';
		$source->dbpassoption	= 1;
		$source->utf8encode	= 0;
	}

	# Misc definitions
	# ------------------------------------------------------------------

	$def = get_option('spi-def');

		$def->user_batch	= 100;
		$def->required		= 'dbname,dbuser,dbpass,dbhost,dbbasepfix,dbpassoption';
		$def->wp_tags		= false;
		$def->parse_bbcode	= true;

	update_option('spi-def', $def);

	# Table/Data mapping
	# ------------------------------------------------------------------

	$map = get_option('spi-map');

		# USERS Table Mappings

		$map['tables']->users				= true;
			$map['users']->table			= 'members';
			$map['users']->select			= '';
			$map['users']->where			= '';
			$map['users']->ID				= 'ID_MEMBER';
			$map['users']->user_login		= 'memberName';
			$map['users']->user_pass		= 'passwd';
			$map['users']->user_nicename	= 'realName';
			$map['users']->user_email		= 'emailAddress';
			$map['users']->user_url			= 'websiteUrl';
			$map['users']->user_registered	= '#timestamp:dateRegistered';
			$map['users']->display_name		= 'realName';

		# SFGROUPS Table Mappings

		$map['tables']->sfgroups			= true;
			$map['sfgroups']->table			= 'categories';
			$map['sfgroups']->select		= '';
			$map['sfgroups']->where			= '';
			$map['sfgroups']->group_id		= 'ID_CAT';
			$map['sfgroups']->group_name	= 'name';
			$map['sfgroups']->group_desc	= '';
			$map['sfgroups']->group_seq		= 'catOrder';

		# SFFORUMS Table Mappings

		$map['tables']->sfforums			= true;
			$map['sfforums']->table			= 'boards';
			$map['sfforums']->select		= '';
			$map['sfforums']->where			= '';
			$map['sfforums']->forum_id		= 'ID_BOARD';
			$map['sfforums']->forum_name 	= 'name';
			$map['sfforums']->group_id 		= 'ID_CAT';
			$map['sfforums']->forum_seq 	= 'boardOrder';
			$map['sfforums']->forum_desc 	= 'description';
			$map['sfforums']->forum_slug 	= 'name';

		# SFTOPICS Table Mappings

		$map['tables']->sftopics			= true;
			$map['sftopics']->table			= 'topics';
			$map['sftopics']->select		= '';
			$map['sftopics']->topic_id		= 'ID_TOPIC';
			$map['sftopics']->topic_name	= '#query:SELECT subject FROM <%dbname%>.<%dbbasepfix%>messages WHERE ID_TOPIC = <%topic_id%> ORDER BY ID_MSG LIMIT 1';
			$map['sftopics']->topic_date	= '';
			$map['sftopics']->topic_status	= 'locked';
			$map['sftopics']->forum_id		= 'ID_BOARD';
			$map['sftopics']->user_id		= 'ID_MEMBER_STARTED';
			$map['sftopics']->topic_pinned	= 'isSticky';
			$map['sftopics']->topic_opened	= 'numViews';
			$map['sftopics']->topic_slug	= '#query:SELECT subject FROM <%dbname%>.<%dbbasepfix%>messages WHERE ID_TOPIC = <%topic_id%> ORDER BY ID_MSG LIMIT 1';
			$map['sftopics']->post_id		= 'ID_LAST_MSG';

		# SFPOSTS Table Mappings

		$map['tables']->sfposts				= true;
			$map['sfposts']->table			= 'messages';
			$map['sfposts']->select			= '';
			$map['sfposts']->post_id		= 'ID_MSG';
			$map['sfposts']->topic_id		= 'ID_TOPIC';
			$map['sfposts']->forum_id		= 'ID_BOARD';
			$map['sfposts']->post_content 	= 'body';
			$map['sfposts']->post_date		= '#timestamp:posterTime';
			$map['sfposts']->user_id		= 'ID_MEMBER';
			$map['sfposts']->post_index		= '#default:0';
			$map['sfposts']->poster_ip		= 'posterIP';

		# USERMETA Table Mappings

		$map['tables']->usermeta			= false;

	update_option('spi-map', $map);

	# ------------------------------------------------------------------
	# Form: WP Forum
	# ------------------------------------------------------------------

	$required = explode(',', $def->required);
	$bullet = '<img src="'.SPI_URL.'importers/logos/bullet.png" alr="" />';
	$icon = '<td>'.$bullet.'</td>';
	$noicon = '<td></td>';

	?>
	<div id="spiForm" class="spiMainPanel">
	<h3>Source Forum Data: Simple Machines V1</h3>
	<form name="smf2spimport" method="post" action="<?php echo(admin_url()); ?>admin.php?page=sp-import/admin/spimport-setup.php&sys=simplemachines">

		<input type="hidden" id="s2" name="s2" value="s2" />
		<input type="hidden" id="selectsource" name="selectsource" value="simplemachines-v1.php"

		<p><b><?php echo($bullet); ?>&nbsp;&nbsp;Required Data</b></p>

		<table id="sourcedata" class="form-table">

			<tr valign="top">
				<th scope="row"><label for="dbname">SMF Database Name:</label></th>
				<?php if(in_array('dbname', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbname" name="dbname" value="<?php echo($source->dbname); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbuser">SMF Database User Name:</label></th>
				<?php if(in_array('dbuser', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbuser" name="dbuser" value="<?php echo($source->dbuser); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbpass">SMF Database Password:</label></th>
				<?php if(in_array('dbpass', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbpass" name="dbpass" value="<?php echo($source->dbpass); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbhost">SMF Database Host:</label></th>
				<?php if(in_array('dbhost', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbhost" name="dbhost" value="<?php echo($source->dbhost); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbbasepfix">SMF Table Prefix:</label></th>
				<?php if(in_array('dbbasepfix', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbbasepfix" name="dbbasepfix" value="<?php echo($source->dbbasepfix); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbpassoption">Password Options:</label></th>
				<?php if(in_array('dbpassoption', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td>

					<input type="radio" name="dbpassoption" id="copy" value="copy" />
					<label for="copy">&nbsp;&nbsp;&nbsp;<b>Copy source passwords from user records</b></label><br />

					<input type="radio" name="dbpassoption" id="create" value="create" />
					<label for="create">&nbsp;&nbsp;&nbsp;<b>Let WordPress create random passwords</b></label><br />

					<input type="radio" name="dbpassoption" id="ulogin" value="ulogin" />
					<label for="ulogin">&nbsp;&nbsp;&nbsp;<b>Create passwords from login name</b></label><br />

				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="utf8encode">UTF-8 encode results</label></th>
				<td>

					<input type="checkbox" name="utf8encode" id="utf8encode" />

				</td>
			</tr>

		</table><br />

		<div style="clear: both"></div>

		<input type="submit" class="button-primary" id="sbutton" name="goimport" value="Perform Import" />

	</form>
	</div>
	<?php
}

?>