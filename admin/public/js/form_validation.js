//$(document).ready(function(){
  
    
    //#################     Assistant Form   #####################
    // $('#addAssistantButton').click(function(){
         
    //        if($('#assistantForm').valid()){
    //            $.ajax({
    //                 type: 'POST',
    //                 url: 'services_admin_api.php',
    //                 data: {data:$('#assistantForm').serialize(),mode:'assistant_add'},
    //                 success: function () {
    //                   showPopupMessage('success','form was submitted');
    //                   location.reload();
    //                 }
    //               }); 
    //        }
    // });
    //  $("#assistantForm").validate({
    //     rules: {
            
    //         archive_name: {
    //             required: 0
    //         }, 
    //         login_data: {
    //             required: true 
    //         }, 
    //         asst_name: {
    //             required: true 
    //         },
            
    //         asst_paswd: {
    //             required: true 
    //         },
    //         asst_cnfrm_paswd: {
    //             required: true,
    //             equalTo : "#asst_paswd"             
    //         }           
    //     },
    //     messages: {
            
    //         archive_name: {
    //             required: "Please enter Assistant"  
    //         },          
    //         login_data: {
    //             required: "Please enter Login" 
    //         },
    //         asst_name: {
    //             required: "Please enter Name" 
    //         },
    //         asst_paswd: {
    //             required: "Please enter Password" 
    //         },
    //         asst_cnfrm_paswd: {
    //             required: "Please enter Password",
    //             equalTo: "Your Password does not Match"             
    //         }           
    //     },
    //     submitHandler: function(form) {
    //        assistantFormSubmit();
    //     }
    //}); 


   //#################     Update Profile   #####################

   // $('#UpdateProfileButton').click(function(){
         
   //       $('#profileForm').valid();
   //  });

   //   $("#profileForm").validate({
   //      rules: {
            
   //          archive_name: {
   //              required: true
   //          }, 
   //          login_data: {
   //              required: true 
   //          }, 
   //          profile_name: {
   //              required: true 
   //          },
            
   //          profile_paswd: {
   //              required: true 
   //          },
   //          profile_cnfrm_paswd: {
   //              required: true,
   //              equalTo : "#profile_paswd"             
   //          }           
   //      },
   //      messages: {
            
   //          archive_name: {
   //              required: "Please enter Archive"  
   //          },          
   //          login_data: {
   //              required: "Please enter Login" 
   //          },
   //          profile_name: {
   //              required: "Please enter Name" 
   //          },
   //          profile_paswd: {
   //              required: "Please enter Password" 
   //          },
   //          profile_cnfrm_paswd: {
   //              required: "Please enter Password",
   //              equalTo: "Your Password does not Match"             
   //          }           
   //      },
        
   //  }); 
     
   //####################     Create Forms Validation  ##################  
    $('#addformsButton').click(function(){
            $('#addforms').valid();
    });
     $("#addforms").validate({
        rules: {
            
            field_owner_name: {
                required: true
            }, 
            field_name: {
                required: true 
            }, 
            field_type_name: {
                required: true 
            },
            field_format_detail: {
                required: true 
            },
            field_display_width_name: {
                required: true 
            }               
        },
        messages: {
            
            field_owner_name: {
                required: "Please Enter Field Owner Name"  
            },          
            field_name: {
                required: "Please Enter Field Name" 
            },
            field_type_name: {
                required: "Please Enter Field Type Name" 
            },
            field_format_detail: {
                required: "Please Enter Field Formet Detail" 
            },
            field_display_width_name: {
                required: "Please Enter Field Display Width Name" 
            }                 
        },
        // submitHandler: function(form) {
        //    addforms();
        // }
    }); 
     

    
    //####################     Archive Form  ##################
    $('#addArchiveFormBtn').click(function(){
            $('#add_archiveData').valid();
    });
     $("#add_archiveData").validate({
        rules: {
            
            arch_grp_name: {
                required: true
            }, 
            arch_code: {
                required: true 
            }, 
            arch_grp_titl: {
                required: true 
            }           
        },
        messages: {
            
            arch_grp_name: {
                required: "Please enter Assistant"  
            },          
            arch_code: {
                required: "Please enter Archive Code" 
            },
            arch_grp_titl: {
                required: "Please enter Archive Group Title" 
            }       
        },
        submitHandler: function(form) {
           myArchiveForm();
        }
    }); 
     
//####################     My Account Form  ##################
    
    $('#myAccountFormButton').click(function(){
            $('#my_accountForm').valid();
    });
    $("#my_accountForm").validate({
        rules: {
            
            full_name: {
                required: true
            }, 
            new_pswd: {
                required: true 
            },
            new_cnfrm_paswd: {
                required: true,
                equalTo : "#new_pswd"               
            }               
        },
        messages: {
            
            full_name: {
                required: "Please enter Full Name"  
            },          
            new_pswd: {
                required: "Please enter Password" 
            },
            new_cnfrm_paswd: {
                required: "Please enter Password",
                equalTo: "Your Password does not Match" 
            }       
        },
        submitHandler: function(form) {
           myAccountForm();
        }
    });
    
    
    $('#undomyForm').click(function(){
            $('#my_accountForm')[0].reset();
    });
     

    $('#clearArchiveForm').click(function(){
            $('#add_archiveData')[0].reset();
    });
       
//});
 
$(document).on('click', '.clearAdminForm', function () {
	var formId = $(this).parents('form:first').attr('id');  
		 $('#'+formId)[0].reset();
});
function myAccountForm(){
    
     $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {data:$('#my_accountForm').serialize(),mode:'add_my_account'},
            success: function () {
              showPopupMessage('success','form was submitted');
              location.reload();
            }
    }); 
}

function myArchiveForm(){
    
     $.ajax({
            type: 'POST',
            url: 'services_admin_api.php',
            data: {data:$('#add_archiveData').serialize(),mode:'add_archive_form_data'},
            success: function () { 
			  showPopupMessage('success','form was submitted');
              location.reload();
            }
    }); 
}

