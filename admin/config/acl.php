<?php
$aclArray = [
    'R'=>[
        'redirect'=>'manage_my_archive.php',
        'file_access'=>[
            'update_profile.php'               => '1',
            'content_removal.php'              => '1',
            'content_report.php'               => '1',
            'comment_report.php'               => '1',
            'report_listing.php'               => '1',
            'contact_request.php'              => '1',
            'manage_my_archive.php'            => '1',
            'create_fields.php'                => '1',
            'manage_fields.php'                => '1',
            'create_forms.php'                 => '1',
            'manage_forms.php'                 => '1',
            'add_administrator.php'            => '1',
            'manage_clients_administrator.php' => '1',
            'add_assistant.php'                => '1',
            'manage_assistant.php'             => '1',
            'manage_public_user.php'           => '1',
            'reprint_request.php'              => '1',
            'terms_and_condition.php'          => '1',
            'manage_archive_group_data.php'    => '1',
            'admin.php'                        => '1' ,
            'services_admin_api.php'           => '1',
            'add_record.php'                   => '1',
            'import_record.php'                => '1',
            'import_map_fields.php'            => '1',
            'record_modify.php'                => '1',
            'download-pdf.php'                 => '1',
            'privacy_and_cookies.php'          => '1',
            'dmca.php'                         => '1',
            'dmca_counter_notice.php'          => '1',
            'trouble_ticket_request.php'       => '1',
            'society_trouble_request.php'      => '1',
            'register_society.php'             => '1',
            'create_society.php'               => '1',
            'manage_claimed_user.php'          => '1',
            'view_claimed_user.php'            => '1',
            'claimed_popup_message.php'        => '1',
            'add_security_questions.php'       => '1',
	    'help_message.php'			=> '1',
	    'add_advertisement.php'	       => '1',
		'manage_archive_registration.php'	=> '1',
			  //Fix start for Issue ID 2140 on 23-Feb-2023
			'download_report.php' => '1',
            //Fix end for Issue ID 2140 on 23-Feb-2023

            //Fix start for Issue ID 2180 on 30-March-2023
            'upload_user_guide.php' => '1',
            //Fix end for Issue ID 2180 on 30-March-2023
//Fix start for Issue ID 2147 on 28-Apr-2023
            'homepage_settings.php' => '1',
            //Fix end for Issue ID 2147 on 28-Apr-2023
        ]
    ],
    'A'=>[
        'redirect'=>'manage_my_archive.php',
        'file_access' =>[
            'update_profile.php'              => '1',
            'manage_archive_group_data.php'   => '1',
            'manage_archive_registration.php' => '1',
            'content_removal.php'             => '1',
            'contact_request.php'             => '1',
            'manage_my_archive.php'           => '1',
            'create_fields.php'               => '1',
            'manage_fields.php'               => '1',
            'create_forms.php'                => '1',
            'manage_forms.php'                => '1',
            'add_administrator.php'           => '1',
            'manage_clients_administrator.php'=> '1',
            'add_assistant.php'               => '1',
            'manage_assistant.php'            => '1',
            'reprint_request.php'             => '1',
            'admin.php'                       => '1',
            'services_admin_api.php'          => '1',
            'add_record.php'                  => '1',
            'import_record.php'               => '1',
            'import_map_fields.php'           => '1',
            'record_modify.php'               => '1',
            'download-pdf.php'                => '1',
            'trouble_ticket.php'              => '1',
            'shared_with_me.php'              => '1',
            'shared_with_others.php'          => '1',
	    'add_scrapbook.php'               => '1',
	    'manage_scrapbook.php'            => '1',
	    'manage_scrapbook_entries.php'    => '1',
	    'my_connections.php'              => '1',
	    'add_advertisement.php'	       => '1',
			 //Fix start for Issue ID 2180 on 30-March-2023
            'download_user_guide.php' => '1',
            //Fix end for Issue ID 2180 on 30-March-2023
        ]
    ],
    'U'=>[
        'redirect'=>'manage_my_archive.php',
        'file_access' =>[
            'public_user_details.php'        => '1',
            'manage_user_archive_data.php'   => '1',
            'manage_my_archive.php'          => '1',
            'shared_with_me.php'             => '1',
            'shared_with_others.php'         => '1',
            'add_scrapbook.php'              => '1',
            'manage_scrapbook.php'           => '1',
            'scrapbook_shared_with_me.php'   => '1',
            'user_share_link.php'            => '1',
            'user_share_link_with_me.php'    => '1',
            'admin.php'                      => '1',
            'services_admin_api.php'         => '1',
            'add_record.php'                 => '1',
            'record_modify.php'              => '1',
            'manage_scrapbook_entries.php'   => '1',
            'download-pdf.php'                 => '1',
			//Fix start for Issue ID 2180 on 30-March-2023
            'download_user_guide.php' => '1',
            //Fix end for Issue ID 2180 on 30-March-2023
        ]
    ],
    'S'=>[
        'redirect'=>'assistant_index.php',
        'file_access' =>[
            'assistant_profile.php'         =>'1',
            'assistant_index.php'           => '1',
            'admin.php'                     =>'1',
            'services_admin_api.php'        => '1',
            'assistant_uncomplete_data.php' => '1',
            'trouble_ticket.php'            => '1',
			 //Fix start for Issue ID 2180 on 30-March-2023
            'download_user_guide.php' => '1',
            //Fix end for Issue ID 2180 on 30-March-2023
        ]
    ]
];
$requestedFile = $_SERVER['SCRIPT_NAME'];
$fileName = end(explode('/',$requestedFile));
$userType = $_SESSION['aib']['user_data']['user_type'];
if(!array_key_exists($fileName,$aclArray[$userType]['file_access']) && $fileName != 'login.php' && $fileName != 'services_admin_api.php'){
    header('location: '.$aclArray[$userType]['redirect']); 
    exit;
}
