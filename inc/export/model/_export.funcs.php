<?php
/**
 * This file implements export functions.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2018 by Francois Planque - {@link http://fplanque.com/}.
 * Parts of this file are copyright (c)2004-2005 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @package evocore
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * Export collection or user data to XML/ZIP file
 *
 * @param array Params
 */
function export_xml( $params )
{
	global $DB, $app_name, $app_version, $baseurl;

	$params = array_merge( array(
			'blog_ID' => NULL,
			'user_ID' => NULL,
			'options' => array(
					// What should be exported:
					'all'       => false, // All content of the options below
					'user'      => false, // All users
						'pass'    => false, // Passwords of the users
						'avatar'  => false, // Profile pictures of the users
					'cat'       => false, // Categories
					'tag'       => false, // Tags
					'post'      => false, // Posts/Items
						'comment' => false, // Comments
						'file'    => false, // Attachments of the Posts/Items
				),
		), $params );

	$UserCache = & get_UserCache();

	if( $params['blog_ID'] !== NULL )
	{	// Export collection data:
		$BlogCache = & get_BlogCache();
		$export_Blog = & $BlogCache->get_by_ID( $params['blog_ID'] );
		$file_name = $export_Blog->dget( 'name' );
		$xml_locale = $export_Blog->get( 'locale' );
	}
	elseif( $params['user_ID'] !== NULL )
	{	// Export user data:
		$export_User = & $UserCache->get_by_ID( $params['user_ID'] );
		$file_name = $export_User->get( 'login' );
		$xml_locale = $export_User->get( 'locale' );
	}
	else
	{	// Stop wrong request:
		debug_die( 'Wrong export request with unknown collection and user!' );
	}

	// New line
	$nl = "\r\n";

	// What to export
	$export_users = false;
	$export_passwords = false;
	$export_avatars = false;
	$export_categories = false;
	$export_tags = false;
	$export_posts = false;
	$export_comments = false;
	$export_files = false;

	if( !empty( $params['options']['all'] ) )
	{	// Export all contents
		$export_users = true;
		$export_passwords = true;
		$export_avatars = true;
		$export_categories = true;
		$export_tags = true;
		$export_posts = true;
		$export_comments = true;
		$export_files = true;
	}
	else
	{	// Export only selected contents
		if( !empty( $params['options']['user'] ) )
		{
			$export_users = true;
			if( !empty( $params['options']['pass'] ) )
			{
				$export_passwords = true;
			}
			if( !empty( $params['options']['avatar'] ) )
			{
				$export_avatars = true;
			}
		}
		if( !empty( $params['options']['cat'] ) )
		{
			$export_categories = true;
		}
		if( !empty( $params['options']['tag'] ) )
		{
			$export_tags = true;
		}
		if( !empty( $params['options']['post'] ) )
		{
			$export_posts = true;
			if( !empty( $params['options']['comment'] ) )
			{
				$export_comments = true;
			}
			if( !empty( $params['options']['file'] ) )
			{
				$export_files = true;
			}
		}
	}

	if( $export_files || $export_avatars )
	{ // The files will be exported
		global $media_path;
		// Store here the files of all posts and comments
		$files_xml_data = array();
		// Init FileCache
		$FileCache = & get_FileCache();
	}

	$file_name = format_to_output( strtolower( str_replace( ' ', '_', $file_name ) ), 'text' ) .'.b2evolution.'.date( 'Y-m-d' );
	$xml_file_name = $file_name.'.xml';

	$XML = '<?xml version="1.0" encoding="UTF-8" ?>'.$nl;

	$XML .= '<!-- This is an eXtended RSS file generated by b2evolution as an export of your site. -->'.$nl;
	$XML .= '<!-- It contains information about your site\'s posts, pages, comments, categories, and other content. -->'.$nl;
	$XML .= '<!-- You may use this file to transfer that content from one site to another. -->'.$nl;
	$XML .= '<!-- This file is not intended to serve as a complete backup of your site. -->'.$nl.$nl;

	$XML .= '<!-- generator="'.$app_name.'/'.$app_version.'" created="'.date( 'Y-m-d H:i' ).'" -->'.$nl;
	$XML .= '<rss version="2.0"'.$nl.
		'	xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"'.$nl.
		'	xmlns:content="http://purl.org/rss/1.0/modules/content/"'.$nl.
		'	xmlns:wfw="http://wellformedweb.org/CommentAPI/"'.$nl.
		'	xmlns:dc="http://purl.org/dc/elements/1.1/"'.$nl.
		'	xmlns:wp="http://wordpress.org/export/1.2/"'.$nl.
		'	xmlns:evo="http://b2evolution.net/export/1.0/"'.$nl.
	'>'.$nl.$nl;

	$XML .= '<channel>'.$nl;
	if( isset( $export_Blog ) )
	{
		$XML .= '	<title>'.xml_cdata( $export_Blog->get_name() ).'</title>'.$nl;
		$XML .= '	<link>'.xml_cdata( $export_Blog->gen_blogurl() ).'</link>'.$nl;
		$XML .= '	<description>'.xml_cdata( $export_Blog->get( 'longdesc' ) ).'</description>'.$nl;
		$XML .= '	<pubDate>'.date( 'r' ).'</pubDate>'.$nl;
	}
	$XML .= '	<language>'.$xml_locale.'</language>'.$nl;
	$XML .= '	<wp:wxr_version>1.2</wp:wxr_version>'.$nl;
	$XML .= '	<evo:export_version>1.0</evo:export_version>'.$nl;
	$XML .= '	<wp:base_site_url>'.xml_cdata( $baseurl ).'</wp:base_site_url>'.$nl;
	if( isset( $export_Blog ) )
	{
		$XML .= '	<wp:base_blog_url>'.xml_cdata( $export_Blog->gen_blogurl() ).'</wp:base_blog_url>'.$nl;
	}
	$XML .= '	<generator>http://b2evolution.net/</generator>'.$nl.$nl;

	if( $export_users )
	{	// Export users:
		global $UserSettings;

		load_class( 'regional/model/_country.class.php', 'Country' );
		$CountryCache = & get_CountryCache();

		$users_SQL = new SQL();
		$users_SQL->SELECT( 'user_ID' );
		$users_SQL->FROM( 'T_users' );
		if( isset( $export_User ) )
		{	// Export only single User:
			$users_SQL->WHERE( 'user_ID = '.$export_User->ID );
		}
		$users_SQL->ORDER_BY( 'user_ID' );
		$users_IDs = $DB->get_col( $users_SQL->get(), 0, ( isset( $export_User ) ? 'Get user #'.$export_User->ID.' to export' : 'Get all users to export' ) );

		if( count( $users_IDs ) > 0 )
		{
			$users_xml_data = array();
			foreach( $users_IDs as $u => $user_ID )
			{
				$User = & $UserCache->get_by_ID( $user_ID, false, false );
				$Group = $User->get_Group();
				$user_country_code = '';
				if( !empty( $User->ctry_ID ) )
				{	// Get a country code
					$Country = & $CountryCache->get_by_ID( $User->ctry_ID );
					$user_country_code = $Country->get( 'code' );
				}
				$reg_country_code = '';
				if( !empty( $User->reg_ctry_ID ) )
				{	// Get a country code which was detected by IP address during registration
					$RegCountry = & $CountryCache->get_by_ID( $User->reg_ctry_ID );
					$reg_country_code = $RegCountry->get( 'code' );
				}

				if( $User->get( 'gender' ) == 'F' )
				{	// Female
					$gender_name = 'female';
				}
				elseif( $User->get( 'gender' ) == 'M' )
				{	// Male
					$gender_name = 'male';
				}
				else
				{	// No gender
					$gender_name = '';
				}

				$users_xml_data[ $u ] = array(
					'wp:author_id'           => $User->ID,
					'wp:author_login'        => $User->get( 'login' ),
					'wp:author_email'        => $User->get( 'email' ),
					'wp:author_display_name' => xml_cdata( $User->get( 'fullname' ) != '' ? $User->get( 'fullname' ) : $User->get( 'login' ) ),
					'wp:author_first_name'   => xml_cdata( $User->get( 'firstname' ) ),
					'wp:author_last_name'    => xml_cdata( $User->get( 'lastname' ) )
				);
				if( $export_passwords )
				{
					$users_xml_data[ $u ]['evo:author_pass'] = $User->get( 'pass' );
					$users_xml_data[ $u ]['evo:author_salt'] = $User->get( 'salt' );
					$users_xml_data[ $u ]['evo:author_pass_driver'] = $User->get( 'pass_driver' );
				}
				$users_xml_data[ $u ]['evo:author_group'] = xml_cdata( $Group->get_name() );
				$users_xml_data[ $u ]['evo:author_status'] = $User->get( 'status' );
				$users_xml_data[ $u ]['evo:author_nickname'] = xml_cdata( $User->get( 'nickname' ) );
				$users_xml_data[ $u ]['evo:author_url'] = xml_cdata( $User->get( 'url' ) );
				$users_xml_data[ $u ]['evo:author_level'] = $User->get( 'level' );
				$users_xml_data[ $u ]['evo:author_locale'] = $User->get( 'locale' );
				$users_xml_data[ $u ]['evo:author_gender'] = $gender_name;
				$users_xml_data[ $u ]['evo:author_age_min'] = $User->get( 'age_min' );
				$users_xml_data[ $u ]['evo:author_age_max'] = $User->get( 'age_max' );
				$users_xml_data[ $u ]['evo:author_created_from_country'] = $reg_country_code;
				$users_xml_data[ $u ]['evo:author_country'] = $user_country_code;
				$users_xml_data[ $u ]['evo:author_region'] = xml_cdata( $User->get_region_name() );
				$users_xml_data[ $u ]['evo:author_subregion'] = xml_cdata( $User->get_subregion_name() );
				$users_xml_data[ $u ]['evo:author_city'] = xml_cdata( $User->get_city_name( false ) );
				$users_xml_data[ $u ]['evo:author_source'] = $User->get( 'source' );
				$users_xml_data[ $u ]['evo:author_created_ts'] = $User->get( 'datecreated' );
				$users_xml_data[ $u ]['evo:author_lastseen_ts'] = $User->get( 'lastseen_ts' );
				$users_xml_data[ $u ]['evo:author_created_from_ipv4'] = int2ip( $UserSettings->get( 'created_fromIPv4' ) );
				$users_xml_data[ $u ]['evo:author_profileupdate_date'] = $User->get( 'profileupdate_date' );
				$users_xml_data[ $u ]['evo:author_avatar_file_ID'] = $User->get( 'avatar_file_ID' );

				if( $export_avatars )
				{	// Export the avatars:
					export_xml_files( 'user', $users_xml_data[ $u ], $files_xml_data );
				}

				// Clear users cache to keep memory free
				$UserCache->clear();
			}

			$XML .= get_xml_tags( 'wp:author', $users_xml_data );
		}
	}

	if( $export_categories )
	{	// Export categories
		$cats_SQL = new SQL( 'Get categories to XML export' );
		$cats_SQL->SELECT( 'c1.cat_ID, c1.cat_parent_ID, c1.cat_name, c1.cat_urlname, c2.cat_urlname AS parent_urlname, c1.cat_description, c1.cat_order' );
		$cats_SQL->FROM( 'T_categories c1' );
		$cats_SQL->FROM_add( 'LEFT OUTER JOIN T_categories c2 ON c1.cat_parent_ID = c2.cat_ID' );
		$cats_SQL->WHERE( 'c1.cat_blog_ID = '.$DB->quote( $export_Blog->ID ) );
		$cats_SQL->ORDER_BY( 'cat_parent_ID, cat_ID' );
		$cats = $DB->get_results( $cats_SQL->get() );

		if( count( $cats ) > 0 )
		{
			$cats_xml_data = array();
			foreach( $cats as $cat )
			{
				$cats_xml_data[] = array(
					'wp:term_id'           => $cat->cat_ID,
					'wp:category_nicename' => $cat->cat_urlname,
					'wp:category_parent'   => $cat->parent_urlname,
					'wp:cat_name'          => xml_cdata( $cat->cat_name ),
					'wp:cat_description'   => xml_cdata( $cat->cat_description ),
					'wp:cat_order'         => $cat->cat_order,
				);
			}

			$XML .= get_xml_tags( 'wp:category', $cats_xml_data );
		}
	}

	if( $export_tags )
	{ // Export tags
		$tags_SQL = new SQL( 'Get tags to XML export' );
		$tags_SQL->SELECT( 'tag_ID, tag_name' );
		$tags_SQL->FROM( 'T_items__tag' );
		$tags_SQL->ORDER_BY( 'tag_ID' );
		$tags = $DB->get_results( $tags_SQL->get() );

		if( count( $tags ) > 0 )
		{
			$tags_xml_data = array();
			foreach( $tags as $tag )
			{
				$tags_xml_data[] = array(
					'wp:term_id'  => $tag->tag_ID,
					'wp:tag_slug' => $tag->tag_name,
					'wp:tag_name' => xml_cdata( $tag->tag_name )
				);
			}

			$XML .= get_xml_tags( 'wp:tag', $tags_xml_data );
		}
	}

	if( $export_posts )
	{ // Export posts
		$cats_SQL = new SQL( 'Get all categories IDs of current Blog' );
		$cats_SQL->SELECT( 'cat_ID' );
		$cats_SQL->FROM( 'T_categories' );
		$cats_SQL->WHERE( 'cat_blog_ID = '.$DB->quote( $export_Blog->ID ) );
		$cats_IDs = $DB->get_col( $cats_SQL->get() );

		if( count( $cats_IDs ) > 0 )
		{
			$ItemCache = & get_ItemCache();
			$Items = $ItemCache->load_where( 'post_main_cat_ID IN ( '.implode( ', ', $cats_IDs ).' )' );

			if( count( $Items ) > 0 )
			{
				load_class( 'regional/model/_country.class.php', 'Country' );
				$CountryCache = & get_CountryCache();

				// Set status's links between b2evo and WP names
				$post_statuses = array(
					'published'  => 'publish',
					'deprecated' => 'deprecated',
					'protected'  => 'protected',
					'private'    => 'private',
					'redirected' => 'redirected',
					'draft'      => 'draft',
				);

				global $postIDlist;
				$postIDlist = array();
				foreach( $Items as $Item )
				{	// Init this list to correct work of the method $Item->get_Chapters()
					$postIDlist[] = $Item->ID;
				}
				$postIDlist = implode( ', ', $postIDlist );

				$posts_xml_data = array();
				foreach( $Items as $i => $Item )
				{
					$post_categories = array();
					foreach( $Item->get_Chapters() as $Chapter )
					{	// Set category's data
						$post_categories[] = array(
							'domain'   => 'category',
							'nicename' => $Chapter->get( 'urlname' ),
							'value'    => xml_cdata( $Chapter->dget( 'name' ) ) );
					}
					foreach( $Item->get_tags() as $tag_name )
					{	// Set tag's data
						$post_categories[] = array(
							'domain'   => 'post_tag',
							'nicename' => $tag_name,
							'value'    => xml_cdata( $tag_name ) );
					}
					$lastedit_user_login = '';
					if( !empty( $Item->lastedit_user_ID ) && $User = & $UserCache->get_by_ID( $Item->lastedit_user_ID ) )
					{	// Get lastedit user's login
						$lastedit_user_login = $User->login;
					}
					$assigned_user_login = '';
					if( !empty( $Item->assigned_user_ID ) && $User = & $UserCache->get_by_ID( $Item->assigned_user_ID ) )
					{	// Get assigned user's login
						$assigned_user_login = $User->login;
					}
					$item_country_code = '';
					if( !empty( $Item->ctry_ID ) )
					{	// Get a country code for Item
						$Country = & $CountryCache->get_by_ID( $Item->ctry_ID );
						$item_country_code = $Country->get( 'code' );
					}

					$posts_xml_data[$i] = array(
						'title'              => xml_cdata( $Item->dget( 'title' ) ),
						'link'               => xml_cdata( $Item->get_permanent_url() ),
						'pubDate'            => date( 'r', strtotime( $Item->get( 'datemodified' ) ) ),
						'dc:creator'         => $Item->get_creator_User()->get( 'login' ),
						'guid'               => array( 'isPermaLink' => 'false', 'value' => $Item->get_tinyurl() ),
						'description'        => '',
						'content:encoded'    => xml_cdata( $Item->dget( 'content' ) ),
						'excerpt:encoded'    => xml_cdata( $Item->dget( 'excerpt' ) ),
						'wp:post_id'         => $Item->ID,
						'wp:post_date'       => date( 'Y-m-d H:i:s', strtotime( $Item->get( 'datestart' ) ) ),
						'wp:post_date_gmt'   => date( 'Y-m-d H:i:s', strtotime( $Item->get( 'datestart' ) ) - date( 'Z' ) ),
						'evo:post_date_mode' => $Item->dateset == '1' ? 'set' : 'now',
						'wp:comment_status'  => $Item->get( 'comment_status' ),
						'wp:ping_status'     => '',
						'wp:post_name'       => $Item->get( 'urltitle' ),
						'wp:status'          => isset( $post_statuses[ $Item->get( 'status' ) ] ) ? $post_statuses[ $Item->get( 'status' ) ] : 'draft',
						'wp:post_parent'     => '0',
						'wp:menu_order'      => (int) $Item->get( 'order' ),
						'wp:post_type'       => strtolower( $Item->get( 't_type' ) ),
						'wp:post_password'   => '',
						'wp:is_sticky'       => '0',
						'evo:post_lastedit_user' => $lastedit_user_login,
						'evo:post_assigned_user' => $assigned_user_login,
						'evo:post_datedeadline'  => $Item->datedeadline,
						'evo:post_datecreated'   => $Item->datecreated,
						'evo:post_datemodified'  => $Item->datemodified,
						'evo:post_locale'        => $Item->locale,
						'evo:post_excerpt_autogenerated' => $Item->excerpt_autogenerated,
						'evo:post_urltitle'      => $Item->urltitle,
						'evo:post_titletag'      => $Item->titletag,
						'evo:post_url'           => xml_cdata( $Item->url ),
						'evo:post_notifications_status' => $Item->notifications_status,
						'evo:post_renderers'     => $Item->renderers,
						'evo:post_priority'      => $Item->priority,
						'evo:post_featured'      => $Item->featured,
						'evo:post_order'         => $Item->get_order(),
						'evo:post_country'       => $item_country_code,
						'evo:post_region'        => xml_cdata( $Item->get_region() ),
						'evo:post_subregion'     => xml_cdata( $Item->get_subregion() ),
						'evo:post_city'          => xml_cdata( $Item->get_city() ),
						'category'               => $post_categories,
					);

					if( $export_comments )
					{	// Export comments
						$comments_SQL = new SQL();
						$comments_SQL->SELECT( 'comment_id, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_content, comment_status, comment_type, comment_in_reply_to_cmt_ID, comment_author_user_ID, 
							comment_IP_ctry_ID, comment_rating, comment_featured, comment_nofollow, comment_helpful_addvotes, comment_helpful_countvotes, comment_spam_addvotes, comment_spam_countvotes, comment_karma, comment_spam_karma, comment_allow_msgform, comment_notif_status' );
						$comments_SQL->FROM( 'T_comments' );
						$comments_SQL->WHERE( 'comment_item_ID = '.$DB->quote( $Item->ID ) );
						$comments = $DB->get_results( $comments_SQL->get() );

						if( count( $comments ) > 0 )
						{
							$post_comments = array();
							foreach( $comments as $comment )
							{
								$comment_country_code = '';
								if( !empty( $comment->comment_IP_ctry_ID ) )
								{	// Get a country code for Comment
									$Country = & $CountryCache->get_by_ID( $comment->comment_IP_ctry_ID );
									$comment_country_code = $Country->get( 'code' );
								}
								$post_comments[] = array(
										'value' => array(
											'wp:comment_id'           => $comment->comment_id,
											'wp:comment_author'       => xml_cdata( $comment->comment_author ),
											'wp:comment_author_email' => $comment->comment_author_email,
											'wp:comment_author_url'   => $comment->comment_author_url,
											'wp:comment_author_IP'    => $comment->comment_author_IP,
											'wp:comment_date'         => $comment->comment_date,
											'wp:comment_date_gmt'     => date( 'Y-m-d H:i:s', strtotime( $comment->comment_date ) - date( 'Z' ) ),
											'wp:comment_content'      => xml_cdata( $comment->comment_content ),
											'wp:comment_approved'     => $comment->comment_status == 'published' ? '1' : '0',
											'wp:comment_type'         => $comment->comment_type,
											'wp:comment_parent'       => (int)$comment->comment_in_reply_to_cmt_ID,
											'wp:comment_user_id'      => (int)$comment->comment_author_user_ID,
											'evo:comment_status'             => $comment->comment_status,
											'evo:comment_IP_country'         => $comment_country_code,
											'evo:comment_rating'             => $comment->comment_rating,
											'evo:comment_featured'           => $comment->comment_featured,
											'evo:comment_nofollow'           => $comment->comment_nofollow,
											'evo:comment_helpful_addvotes'   => $comment->comment_helpful_addvotes,
											'evo:comment_helpful_countvotes' => $comment->comment_helpful_countvotes,
											'evo:comment_spam_addvotes'      => $comment->comment_spam_addvotes,
											'evo:comment_spam_countvotes'    => $comment->comment_spam_countvotes,
											'evo:comment_karma'              => $comment->comment_karma,
											'evo:comment_spam_karma'         => $comment->comment_spam_karma,
											'evo:comment_allow_msgform'      => $comment->comment_allow_msgform,
											'evo:comment_notif_status'       => $comment->comment_notif_status
										)
									);
							}

							$posts_xml_data[$i]['wp:comment'] = $post_comments;
						}
					}

					if( $export_files )
					{	// Export the files:
						export_xml_files( 'item', $posts_xml_data[ $i ], $files_xml_data );
					}
				}

				$XML .= get_xml_tags( 'item', $posts_xml_data );
			}
		}
	}

	if( ! empty( $files_xml_data ) )
	{ // Append all files tags at the end(after items)
		$XML .= get_xml_tags( 'file', $files_xml_data );
	}

	$XML .= '</channel>'.$nl;
	$XML .= '</rss>';

	if( ( $export_files || $export_avatars ) && count( $files_xml_data ) )
	{ // If files are exported we should download them inside ZIP file
		load_class( '_ext/_zip_archives.php', 'zip_file' );

		$options = array (
			'basedir'  => $media_path,
			'inmemory' => 1,
			'recurse'  => 1,
			// Put files in this subfolder
			'prepend'  => 'b2evolution_export_files',
		);

		// Add the files to ZIP file
		$attached_files = array();
		foreach( $files_xml_data as $file_data )
		{
			$attached_files[] = $file_data['evo:zip_path'].$file_data['evo:file_path'];
		}

		// Create temp XML file
		$xml_file_path = $media_path.$xml_file_name;
		if( ( $xml_file_handle = fopen( $xml_file_path, 'w+' ) ) === false )
		{ // Error on creating
			global $Messages;
			$Messages->add( sprintf( T_( 'No permission to create a temporary file %s' ), '<b>'.$xml_file_path.'</b>' ), 'error' );
			// Exit here!
			return;
		}
		fwrite( $xml_file_handle, $XML );
		fclose( $xml_file_handle );

		// Create ZIP file
		$zipfile = new zip_file( $file_name.'.zip' );
		$zipfile->set_options( $options );
		$zipfile->add_files( $attached_files, array( '_evocache' ) );
		// Add XML file to the root of zip
		$zipfile->options['prepend'] = '';
		$zipfile->add_files( array( $xml_file_name ), array( '_evocache' ) );
		$zipfile->create_archive();

		// Download ZIP file
		$zipfile->download_file();

		// Remove temp XML file
		unlink( $xml_file_path );
	}
	else 
	{ // Download only XML file
		header( 'Content-type: text/plain' );
		header( 'Content-Disposition: attachment; filename="'.$xml_file_name.'"' );
		echo $XML;
	}

	// Stop here to don't print out next content!
	exit(0);
}


/**
 * Export file data
 *
 * @param string Type: 'item', 'user'
 * @param array XML data of the current row (updated by reference)
 * @param array XML data of the attached files (updated by reference)
 */
function export_xml_files( $type, & $row_xml_data, & $files_xml_data )
{
	global $DB;

	switch( $type )
	{
		case 'item':
			$link_field_ID_name = 'link_itm_ID';
			$link_field_ID_value = $row_xml_data['wp:post_id'];
			break;

		case 'user':
			$link_field_ID_name = 'link_usr_ID';
			$link_field_ID_value = $row_xml_data['wp:author_id'];
			break;

		default:
			debug_die( 'Invalid type "'.$type.'" for export files!' );
	}

	// Get all links of the User:
	$links_SQL = new SQL( 'Get the links for export' );
	$links_SQL->SELECT( '*' );
	$links_SQL->FROM( 'T_links' );
	$links_SQL->WHERE( $link_field_ID_name.' = '.$DB->quote( $link_field_ID_value ) );
	$links = $DB->get_results( $links_SQL );

	$link_files_IDs = array();
	if( count( $links ) > 0 )
	{
		$row_xml_data['evo:link'] = array();

		foreach( $links as $link )
		{
			$row_xml_data['evo:link'][] = array(
					'value' => array(
						'evo:link_ID'               => $link->link_ID,
						'evo:link_datecreated'      => $link->link_datecreated,
						'evo:link_datemodified'     => $link->link_datemodified,
						'evo:link_creator_user_ID'  => $link->link_creator_user_ID,
						'evo:link_lastedit_user_ID' => $link->link_lastedit_user_ID,
						'evo:link_itm_ID'           => $link->link_itm_ID,
						'evo:link_cmt_ID'           => $link->link_cmt_ID,
						'evo:link_usr_ID'           => $link->link_usr_ID,
						'evo:link_file_ID'          => $link->link_file_ID,
						'evo:link_position'         => $link->link_position,
						'evo:link_order'            => $link->link_order
					)
				);

			if( ! in_array( $link->link_file_ID, $link_files_IDs ) )
			{
				$link_files_IDs[] = $link->link_file_ID;
			}
		}
	}

	if( count( $link_files_IDs ) )
	{	// Files tags:
		global $media_path;

		$FileCache = & get_FileCache();

		foreach( $link_files_IDs as $file_ID )
		{
			if( isset( $files_xml_data[ $file_ID ] ) ||
					! ( $File = & $FileCache->get_by_ID( $file_ID, false, false ) ) )
			{ // Skip if File is already in array OR when it is invalid
				continue;
			}

			$FileRoot = & $File->get_FileRoot();

			$files_xml_data[ $File->ID ] = array(
					'evo:file_ID'        => $File->ID,
					'evo:file_root_type' => $FileRoot->type,
					'evo:file_root_ID'   => $FileRoot->in_type_ID,
					'evo:file_path'      => $File->get_rdfp_rel_path(),
					'evo:file_title'     => xml_cdata( $File->title ),
					'evo:file_alt'       => xml_cdata( $File->alt ),
					'evo:file_desc'      => xml_cdata( $File->desc ),
					'evo:zip_path'       => preg_replace( '~^'.str_replace( '~', '\~', $media_path ).'~', '', $FileRoot->ads_path ),
				);

			// Clear a cache to keep memory free:
			$FileCache->clear();
		}
	}
}


/**
 * Convert array to XML tags
 *
 * @param string Tag name
 * @param array Data
 * @param array Params
 * @return string XML tags
 */
function get_xml_tags( $tag_name, $data, $params = array() )
{
	$params = array_merge( array(
			'block_before'     => '',
			'block_after'      => "\r\n",
			'tag_start_before' => '	',
			'tag_start_after'  => "\r\n",
			'tag_end_before'   => '	',
			'tag_end_after'    => "\r\n",
			'field_before'     => '		',
			'field_after'      => "\r\n",
			'sub_field_start'  => "\r\n",
			'sub_field_before' => '			',
			'sub_field_after'  => "\r\n",
			'sub_field_end'    => '		',
		), $params );

	$XML = '';

	foreach( $data as $row )
	{
		// Start tag
		$XML .= $params['tag_start_before'];
		$XML .= '<'.$tag_name.'>';
		$XML .= $params['tag_start_after'];

		foreach( $row as $field_name => $field_value )
		{	// Print field
			if( is_array( $field_value ) )
			{	// Field with attributes
				$fields = $field_value;
				if( isset( $field_value['value'] ) )
				{	// Single field
					$fields = array( $field_value );
				}

				foreach( $fields as $field_attrs )
				{
					$attrs = array();
					$value = '';
					foreach( $field_attrs as $attr_name => $attr_value )
					{
						if( $attr_name == 'value' )
						{	// Field value
							if( is_array( $attr_value ) )
							{	// Value as multiple fields
								$value = $params['sub_field_start'];
								foreach( $attr_value as $sub_field_name => $sub_field_value )
								{
									$value .= $params['sub_field_before'];
									$value .= '<'.$sub_field_name.'>'.$sub_field_value.'</'.$sub_field_name.'>';
									$value .= $params['sub_field_after'];
								}
								$value .= $params['sub_field_end'];
							}
							else
							{	// Value as string
								$value = $attr_value;
							}
						}
						else
						{	// Field attribute
							$attrs[] = $attr_name.'="'.$attr_value.'"';
						}
					}
					$XML .= $params['field_before'];
					$XML .= '<'.$field_name.( !empty( $attrs ) ? ' '.implode( ' ', $attrs ) : '' ).'>'.$value.'</'.$field_name.'>';
					$XML .= $params['field_after'];
				}
			}
			else
			{	// Simple field (without attributes)
				$XML .= $params['field_before'];
				$XML .= '<'.$field_name.'>'.$field_value.'</'.$field_name.'>';
				$XML .= $params['field_after'];
			}
		}

		// End tag
		$XML .= $params['tag_end_before'];
		$XML .= '</'.$tag_name.'>';
		$XML .= $params['tag_end_after'];
	}

	if( ! empty( $XML ) )
	{
		$XML = $params['block_before'].$XML.$params['block_after'];
	}

	return $XML;
}


/**
 * Quote a value by CDATA
 *
 * @param string Value
 * @param string Value
 */
function xml_cdata( $value )
{
	global $evo_charset;
	if( $evo_charset != 'UTF-8' )
	{	// We should convert special chars to utf-8 format
		if( function_exists( 'iconv' ) )
		{	// Convert text to UTF-8 encoding
			$value = iconv( $evo_charset, 'UTF-8', $value );
		}
		else
		{	// If NO iconv function - Convert special chars (like german umlauts) to ASCII characters
			$value = replace_special_chars( $value );
		}
	}

	return '<![CDATA['.$value.']]>';
}
?>