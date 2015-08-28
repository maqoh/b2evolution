<?php
/**
 * This is the template that displays the item block in list
 *
 * This file is not meant to be called directly.
 * It is meant to be called by an include in the main.page.php template (or other templates)
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/gnu-gpl-license}
 * @copyright (c)2003-2015 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evoskins
 * @subpackage manual
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

// Default params:
$params = array_merge( array(
		'post_navigation' => 'same_category', // Always navigate through category in this skin
		'before_title'    => '<h3>',
		'after_title'     => '</h3>',
		'Item'            => NULL
	), $params );

global $Item;
if( ! empty( $params['Item'] ) )
{ // Get Item from params:
	$Item = $params['Item'];
}

?>
<li><?php
		$item_edit_link = $Item->get_edit_link( array(
				'class' => 'roundbutton roundbutton_text',
			) );
		$Item->title( array(
				'before'          => $params['before_title'],
				'after'           => $params['after_title'].$item_edit_link.'<div class="clear"></div>',
				//'after'      => ' <span class="red">'.( $Item->get('order') > 0 ? $Item->get('order') : 'NULL').'</span>'.$params['after_title'].$item_edit_link.'<div class="clear"></div>',
				'post_navigation' => $params['post_navigation'],
				'link_class'      => 'link',
			) );
		// this will create a <section>
			// ---------------------- POST CONTENT INCLUDED HERE ----------------------
			skin_include( '_item_content.inc.php', $params );
			// Note: You can customize the default item content by copying the generic
			// /skins/_item_content.inc.php file into the current skin folder.
			// -------------------------- END OF POST CONTENT -------------------------
		// this will end a </section>
?></li>
