<?php
$loginUserType = $_SESSION['aib']['user_data']['user_type'];
$requestedUrl = explode('/', $_SERVER['REQUEST_URI']);
$requestedFileName = end($requestedUrl);
$menuArray = [];

if ($loginUserType == 'R') {
	$menuArray = [
		'Dashboard' => [
			//'Home'    => 'index.php',
			'My_Account' => 'update_profile.php',
			'Content_Removal_Requests' => 'content_removal.php',
			'Reported_Content' => 'content_report.php',
			'Reported_Comment' => 'comment_report.php',
			'Reported_Public_Connections' => 'report_listing.php',
			'Contact_Requests' => 'contact_request.php'
		],
		'My_Archive' => [
			'Upload/Manage_Records' => 'manage_my_archive.php',
			'Create_Fields' => 'create_fields.php',
			'Manage_Fields' => 'manage_fields.php',
			'Create_Template' => 'create_forms.php',
			'Manage_Templates' => 'manage_forms.php',
		],
		'Client_Administrator' => [
			'Add_Client_Administrator' => 'add_administrator.php',
			'Manage_Client_Administrator' => 'manage_clients_administrator.php'
		],
		'Assistants' => [
			'Add_New_Assistant' => 'add_assistant.php',
			'Manage_Assistants' => 'manage_assistant.php'
		],
		'Public_User' => [
//                'Add_Client_Administrator'=>'add_administrator.php',
			'Manage_Public_User' => 'manage_public_user.php'
		],
		'Revenue' => [
			//'Display_Ads' =>'display_ads.php',
			'Reprint_Requests' => 'reprint_request.php',
		//'Manage_Advertisements'=>'manage_advertisements.php'
		],
		'Manage_Content' => [
			'Terms_and_Conditions' => 'terms_and_condition.php',
			'Privacy_and_Cookies' => 'privacy_and_cookies.php',
			'DMCA' => 'dmca.php',
			'DMCA_Counter_Notice' => 'dmca_counter_notice.php',
		],
		'Trouble_Ticket_Requests' => [
			'Society_Trouble_Request' => 'society_trouble_request.php',
			'Regular_Trouble_Request' => 'trouble_ticket_request.php'
		],
		'Sign_out' => 'admin.php?mode=logout',
	];
} elseif ($loginUserType == 'A') {
	$menuArray = [
		'Dashboard' => [
			//'Home'    => 'index.php',
			'My_Account' => 'update_profile.php',
			'Manage Homepage Template' => 'manage_archive_group_data.php?archive_id=' . $_SESSION['aib']['user_data']['user_top_folder'],
			'Content_Removal_Requests' => 'content_removal.php',
			'Contact_Requests' => 'contact_request.php'
		],
		'My_Archive' => [
			'Upload/Manage_Records' => 'manage_my_archive.php',
			'Create_Fields' => 'create_fields.php',
			'Manage_Fields' => 'manage_fields.php',
			'Create_Template' => 'create_forms.php',
			'Manage_Templates' => 'manage_forms.php',
			'Shared_With_Me' => 'shared_with_me.php',
			'Shared_With_Others' => 'shared_with_others.php',
			'My_Connections' => 'my_connections.php'
		],
		'Manage_Scrapbook' => [
			'Add_Scrapbook' => 'add_scrapbook.php',
			'My_Scrapbook' => 'manage_scrapbook.php',
		//'Shared_With_Me'=>'scrapbook_shared_with_me.php'
		],
		'Assistants' => [
			'Add_New_Assistant' => 'add_assistant.php',
			'Manage_Assistants' => 'manage_assistant.php'
		],
		'Revenue' => [
			//'Display_Ads' =>'display_ads.php',
			'Reprint_Requests' => 'reprint_request.php',
		//'Manage_Advertisements'=>'manage_advertisements.php'
		],
		'Create_Trouble_Ticket' => 'trouble_ticket.php',
		'Sign_out' => 'admin.php?mode=logout'
	];
} elseif ($loginUserType == 'U') {
	$menuArray = [
		'Dashboard' => [
			//'Home'    => 'index.php',
			//'My_Account'=>'update_profile.php',
			'My_Profile' => 'public_user_details.php',
			'Manage Homepage Template' => 'manage_user_archive_data.php?user_id=' . $_SESSION['aib']['user_data']['user_top_folder'],
		],
		'My_Archive' => [
			'Upload/Manage_Records' => 'manage_my_archive.php',
			'Shared_With_Me' => 'shared_with_me.php',
			'Shared_With_Others' => 'shared_with_others.php',
		//'Manage_Notifier'=> 'manage_notifier.php'
		],
		'Manage_Scrapbook' => [
			'Add_Scrapbook' => 'add_scrapbook.php',
			'My_Scrapbook' => 'manage_scrapbook.php',
			'Shared_With_Me' => 'scrapbook_shared_with_me.php'
		],
		'Public_Connections' => [
			'My_Public_Connections' => 'user_share_link.php',
			'Public_Connections_with_me' => 'user_share_link_with_me.php',
		],
		//'Terms and Conditions'=>'terms_and_condition.php',
		'Sign_out' => 'admin.php?mode=logout'
	];
} elseif ($loginUserType == 'S') {
	$menuArray = [
		'My_Account' => 'assistant_profile.php',
		'My_Archive' => [
			'My_Data_Entry' => 'assistant_index.php'
		],
		'Create_Trouble_Ticket' => 'trouble_ticket.php',
		'Sign_out' => 'admin.php?mode=logout'
	];
}

if ($loginUserType == 'A' && $_SESSION['aib']['user_data']['user_prop']['type'] == 'primary') {
	$primaryUser = array('Owner_Account' => 'manage_archive_registration.php?archive_id=' . $_SESSION['aib']['user_data']['user_top_folder']);
	$menuArray['Dashboard'] = array_slice($menuArray['Dashboard'], 0, 2, true) + $primaryUser + array_slice($menuArray['Dashboard'], 2, count($menuArray['Dashboard']) - 1, true);
	$superA = array('Client_Administrator' => array('Add_Client_Administrator' => 'add_administrator.php', 'Manage_Client_Administrator' => 'manage_clients_administrator.php'));
	$menuArray = array_slice($menuArray, 0, 2, true) + $superA + array_slice($menuArray, 2, count($menuArray) - 1, true);
}

function make_list($arr, $menuClass, $count) {
	$requestedUrl = explode('/', $_SERVER['REQUEST_URI']);
	$requestedFileName = end($requestedUrl);
	if (strpos($requestedFileName, '?') !== false) {
		$fileName = explode('?', $requestedFileName);
		$requestedFileName = $fileName[0];
	}
	$return = '<ul class="' . $menuClass . '">';
	if ($count == 0) {
		$return = '<ul class="' . $menuClass . '" data-widget="tree">';
	}
	foreach ($arr as $key => $item) {
		if (is_array($item)) {
			if (($requestedFileName == 'contact_request.php' || $requestedFileName == 'report_listing.php' || $requestedFileName == 'content_removal.php' || $requestedFileName == 'update_profile.php' || $requestedFileName == 'public_user_details.php' || $requestedFileName == 'index.php' || $requestedFileName == 'manage_archive_registration.php' || $requestedFileName == 'manage_archive_group_data.php' || $requestedFileName == 'manage_user_archive_data.php' || $requestedFileName == 'content_report.php' || $requestedFileName == 'comment_report.php') && $key == 'Dashboard') {
				$return .= '<li class="treeview  menu-open"><a href="#"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . make_list($item, 'treeview-menu', 1) . '</li>';
			} elseif (($requestedFileName == 'add_assistant.php' || $requestedFileName == 'manage_assistant.php') && $key == 'Assistants') {
				$return .= '<li class="treeview  menu-open"><a href="#"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . make_list($item, 'treeview-menu', 1) . '</li>';
			} elseif (($requestedFileName == 'add_scrapbook.php' || $requestedFileName == 'manage_scrapbook.php' || $requestedFileName == 'manage_scrapbook_entries.php' || $requestedFileName == 'scrapbook_shared_with_me.php') && $key == 'Manage_Scrapbook') {
				$return .= '<li class="treeview  menu-open"><a href="#"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . make_list($item, 'treeview-menu', 1) . '</li>';
			} elseif (($requestedFileName == 'manage_my_archive.php' || $requestedFileName == 'create_fields.php' || $requestedFileName == 'manage_fields.php' || $requestedFileName == 'create_forms.php' || $requestedFileName == 'manage_forms.php' || $requestedFileName == 'manage_advertisements.php' || $requestedFileName == 'manage_user_archive.php' || $requestedFileName == 'shared_with_me.php' || $requestedFileName == 'shared_with_others.php' || $requestedFileName == 'manage_notifier.php' || $requestedFileName == 'assistant_index.php' || $requestedFileName == 'my_connections.php') && $key == 'My_Archive') {
				$return .= '<li class="treeview  menu-open"><a href="#"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . make_list($item, 'treeview-menu', 1) . '</li>';
			} elseif (($requestedFileName == 'reprint_request.php' || $requestedFileName == 'display_ads.php') && $key == 'Revenue') {
				$return .= '<li class="treeview  menu-open"><a href="#"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . make_list($item, 'treeview-menu', 1) . '</li>';
			} elseif (($requestedFileName == 'add_client.php' || $requestedFileName == 'add_administrator.php' || $requestedFileName == 'manage_clients.php' || $requestedFileName == 'manage_clients_administrator.php' || $requestedFileName == 'add_archive.php' || $requestedFileName == 'manage_archive.php') && $key == 'Client_Administrator') {
				$return .= '<li class="treeview  menu-open"><a href="#"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . make_list($item, 'treeview-menu', 1) . '</li>';
			} elseif (($requestedFileName == 'manage_public_user.php') && $key == 'Public_User') {
				$return .= '<li class="treeview  menu-open"><a href="#"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . make_list($item, 'treeview-menu', 1) . '</li>';
			} elseif (($requestedFileName == 'terms_and_condition.php' || $requestedFileName == 'privacy_and_cookies.php' || $requestedFileName == 'dmca.php' || $requestedFileName == 'dmca_counter_notice.php') && $key == 'Manage_Content') {
				$return .= '<li class="treeview  menu-open"><a href="#"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . make_list($item, 'treeview-menu', 1) . '</li>';
			} elseif (($requestedFileName == 'user_share_link.php' || $requestedFileName == 'user_share_link_with_me.php') && $key == 'Public_Connections') {
				$return .= '<li class="treeview  menu-open"><a href="#"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . make_list($item, 'treeview-menu', 1) . '</li>';
			} elseif (($requestedFileName == 'society_trouble_request.php' || $requestedFileName == 'trouble_ticket_request.php') && $key == 'Trouble_Ticket_Requests') {
				$return .= '<li class="treeview  menu-open"><a href="#"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . make_list($item, 'treeview-menu', 1) . '</li>';
			} else {
				$return .= '<li class="treeview"><a href="#"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . make_list($item, 'treeview-menu', 1) . '</li>';
			}
		} else {
			if ($key == 'Dashboard' && $requestedFileName == 'index.php') {
				$return .= '<li class="menu-open"><a href="' . $item . '"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span></a></li>';
			} elseif ($key == 'Logout' && $requestedFileName == 'admin.php') {
				$return .= '<li class="menu-open"><a href="' . $item . '"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span></a></li>';
			} elseif ($key == 'Create_Trouble_Ticket' && $requestedFileName == 'trouble_ticket.php') {
				$return .= '<li class="menu-open"><a href="' . $item . '"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span></a></li>';
			} else {
				$return .= '<li><a href="' . $item . '"><i class="fa fa-circle-o"></i><span>' . str_replace('_', ' ', $key) . '</span></a></li>';
			}
		}
	}
	$return .= '</ul>';
	return $return;
}
?>
<aside class="main-sidebar">
    <section class="sidebar">
		<?php echo make_list($menuArray, 'sidebar-menu', 0); ?>
    </section>
</aside>