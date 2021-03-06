<?php
/**
*
* @package Icy Phoenix
* @version $Id$
* @copyright (c) 2008 Icy Phoenix
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

// Tell the Security Scanner that reachable code in this file is not a security issue

define('IN_ICYPHOENIX', true);

if (!empty($setmodules))
{
	$filename = basename(__FILE__);
	$module['1000_Configuration']['192_Google_BOT'] = $filename;
	return;
}

// Load default Header
if (!defined('IP_ROOT_PATH')) define('IP_ROOT_PATH', './../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require('pagestart.' . PHP_EXT);

if (isset($_POST['clear']))
{
	$sql = "DELETE FROM " . GOOGLE_BOT_DETECTOR_TABLE;
	$db->sql_query($sql);

	$message = $lang['Detector_Cleared'] . '<br /><br />' . sprintf($lang['Click_Return_Detector'], '<a href="' . append_sid('admin_google_bot_detector.' . PHP_EXT) . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_admin_index'], '<a href="' . append_sid('index.' . PHP_EXT . '?pane=right') . '">', '</a>');

	message_die(GENERAL_MESSAGE, $message);
}

$start = request_var('start', 0);
$start = ($start < 0) ? 0 : $start;

$template->set_filenames(array('body' => ADM_TPL . 'google_bot_detector_body.tpl'));

$sql = "SELECT * FROM " . GOOGLE_BOT_DETECTOR_TABLE ." ORDER BY detect_time DESC";
$result = $db->sql_query($sql);
$total_row = $db->sql_numrows($result);

if (isset($total_row))
{
	$pagination = generate_pagination(append_sid('admin_google_bot_detector.' . PHP_EXT), $total_row, $config['posts_per_page'], $start) . '&nbsp;';
}

$db->sql_freeresult($result);

$sql .= " LIMIT " . $start . ", " . $config['posts_per_page'];
$result = $db->sql_query($sql);

if ($row = $db->sql_fetchrow($result))
{
	$i = $start;
	do
	{
		$i++;
		$template->assign_block_vars('detector', array(
			'ID' => $i,
			'TIME' => sprintf(create_date($config['default_dateformat'], $row['detect_time'], $config['board_timezone'])),
			'URL' => $row['detect_url'],
			)
		);
	}
	while ($row = $db->sql_fetchrow($result));

	$template->assign_block_vars('page', array(
		'PAGINATION' => $pagination,
		'PAGE_NUMBER' => sprintf($lang['Page_of'], (floor($start / $config['posts_per_page']) + 1), ceil($total_row / $config['posts_per_page']))
	));
}
else
{
	$template->assign_block_vars('nobot', array(
		'L_EXPLAIN' => $lang['Detector_No_Bot'],
		)
	);
}

$template->assign_vars(array(
	'S_ACTION' => append_sid('admin_google_bot_detector.' . PHP_EXT),

	'L_YES' => $lang['Yes'],
	'L_NO' => $lang['No'],

	'L_CLEAR' => $lang['Detector_Clear'],
	'L_DETECTOR_TITLE' => $lang['Detector'],
	'L_DETECTOR_EXPLAIN' => $lang['Detector_Explain'] . sprintf($lang['All_times'], $lang['tz'][str_replace('.0', '', sprintf('%.1f', number_format($config['board_timezone'], 1)))]),
	'L_DETECTOR_ID' => $lang['Detector_ID'],
	'L_DETECTOR_TIME' => $lang['Detector_Time'],
	'L_DETECTOR_URL' => $lang['Detector_Url'],
	)
);

$template->pparse('body');

include(IP_ROOT_PATH . ADM . '/page_footer_admin.' . PHP_EXT);

?>