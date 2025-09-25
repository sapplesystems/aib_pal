<style>
    .modal{background-color: transparent;padding:0px; z-index:1050;}
    .modal-content{width:100%;outline-width:0px;box-shadow: 0 5px 15px rgba(0,0,0,.5);}
    .close {float: right;font-size: 21px;color: #000;}
    .modal-content, #caption {-webkit-animation-name: none;-webkit-animation-duration: 0s;animation-name: none;animation-duration: 0s; }
    .close:hover, .close:focus {color: #000;}

</style>

<?php
$jsArray = [
        'bootstrap.min.js',
        'jquery-form-validate.min.js',        
        'jquery.dataTables.min.js',
        'adminlte.min.js',
	'form_validation.js',
        'jquery.inputmask.bundle.js'
    ];
$reportingRessionArray = ['Pornographic','Hate speech','Extremism','Violence','Racist','Bullying','Abuse','Child Abuse',' ','Copyright Infringement','Other'];
?>
<div class="footer">
    <div class="admin-loading-image"><img src="<?php echo IMAGE_PATH . 'loading.gif'; ?>" alt="Loading...." /></div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 col-sm-2 centerText"><img height="40" src="<?php echo IMAGE_PATH . 'logo.png'; ?>" alt="" /></div>
            <div class="col-md-8 text-center footerText col-sm-10">Copyright &copy; <?php echo date('Y'); ?>. All rights reserved. "ArchiveInABox" and box device is a registered trademark of SmallTownPapers, Inc. <br> <span> <a href="../terms_condition.php" class="term-of-use foot_ancher" target="_blank">Terms of Use </a> | <a href="../privacy_cookies.php" class="privacy-cookies foot_ancher" target="_blank"> Privacy & cookies </a> | <a href="../dmca.php" class="dmca foot_ancher" target="_blank">DMCA</a> | <a href="../dmca_counter_notice.php" class="privacy-cookies foot_ancher" target="_blank"> DMCA counter notice </a> | <a href="javascript:void(0);" class="report-content foot_ancher">Report Content</a> | <a href="javascript:void(0);" class="request-removal">Request Content Removal</a> | <a href="javascript:void(0);" class="front-contact-us foot_ancher">Contact Us</a> <!--| <br /> <a href="javascript:void(0);" class="start-trouble-ticket foot_ancher">Start Trouble Ticket</a>--></span></div>
            <div class="col-md-2 col-sm-12 centerText">
                <ul class="socialIcons">
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'fb.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'twitter.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'pinterest.png'; ?>"></a></li>
                    <li><a href="#"><img src="<?php echo IMAGE_PATH . 'linkedIn.png'; ?>"></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="term_condition_of_services" role="dialog" data-backdrop="static"> 
    <div class="modal-dialog widthFullModal">
        <div class="modal-content">
            <div class="modal-header form_header">
                <h4 class="list_title"><span id="popup_heading">Terms of service </span> </h4>
                <button type="button" onclick="PrintElem('get_term_cond_data');" class="btn btn-primary pull-right marginTop10">Print</button>
            </div>
                <div class="modal-body" id="movefolderformdivData"> 
				<div class="clearfix"></div>
                <div class="footerOverflow overflowTerms">
				<p id="term_cond_data_value"> </p>
			   </div>
			    <div  class="form-horizontal"> 
                    <div class="form-group">
                        <label class="col-xs-3 control-label"></label>
                        <div class="col-xs-7">
                          <button type="button" class="btn btn-info borderRadiusNone" id="agree_term_condition"  user-id="<?php echo $_SESSION['aib']['user_data']['user_id'];  ?>">Yes I agree</button>
			 <button type="button" class="btn btn-info borderRadiusNone" id="not_agree_term_condition">
                             <a href="admin.php?mode=logout" class="acolor">No I do not agree</a></button>
                             
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="request_removal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="">Request Content Removal <span style="text-align:right;float: right;padding-right: 31px;color:green">.</span></h4>
            </div>
            <div class="modal-body">
			  <div id="RequestContentRemovalFormId">
				<p style="font-size:20px;"><b>General Content</b></p>
				<p>If you are requesting removal of general content, please fill out the form below and state your reason for requesting the removal. 
                                    <!--Final decisions are made by the individual account owner, so any follow up should first be directed to them either by resubmitting the form or using the Contact form.-->
				<br/><br/>To report inappropriate content or copyright concerns, <a href="javascript:OpenReportForm();" style="color:red;">click here.</a></p>
				<br/><br/>
			
				<p style="font-size:20px;"><b>Newspapers</b></p>
                <!--<p>Not all articles can be screened due to the nature of the subject matter and for legal reasons. Generally, articles about felony charges filed, felony convictions, legal notices and public notices cannot be screened. The article to be screened cannot be about another person -- only the person mentioned in the article can request screening.</p>-->
                <h5>Newspapers only print Public Information</h5>
                <div class="box_public_info">
                    <h5>First Amendment to the Constitution of the United States</h5>
                    <p>Congress shall make no law respecting an establishment of religion, or prohibiting the free exercise thereof; or abridging the freedom of speech, or of the press; or the right of the people peaceably to assemble, and to petition the Government for a redress of grievances.</p>
                </div>
                    <p>Newspapers produce and deliver news and information which they feel is important -- most often from public information such as arrest reports, traffic infractions, accidents, indictments, lawsuits, jury verdicts, property records, legal notices, and many other sources of information which is freely available to anyone at any time. Newspaper publishers do not have access to any information which is not also available to the public at large. All information published in a newspaper is "public information."</p>
                    <p>The use of your name in an article does not mean you own it. Newspapers deliver news on all sorts of public matters which, in addition to information found in the public registers, may include statements you make to a reporter or official, your photograph, background and other information provided by you or others, rebuttals, opinions, and other information such as court-ordered legal notices, and historical and statistical data.</p>
<!--                    <p>After you submit the form, we will review your request. We do not judge! We must follow the law regarding censorship of the press and only look at your request from that perspective. If we are legally able to screen the article, we will make every effort to do so.</p>
                    <p>We will contact you to let you know if the article can be screened and the amount of the service fee.</p>-->
                    <p>"The Press of the United States of America" is constitutionally protected from interference, including by the government. If you are an attorney, law enforcement officer, officer of the court, or other investigator, by law you must reveal that to us.</p>
                <div class="removeLink"> 
                    <form class="form-horizontal" name="content_removal_form" id="content_removal_form" method="POST">
                        <input type="hidden" name="request_type" id="request_type" value="RD">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">First Name :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="first_name" id="first_name" placeholder="">
                            </div>
                        </div> 
						<input type="text" id="field_check_req" name="field_check_req" value="" style="display:none">
						<input type="text" name="timestamp_value" value="<?php echo time();?>" style="display:none">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Last Name :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="last_name" id="last_name" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Email :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="email" id="email" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Phone Number :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="" maxlength="14">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone">Paste link to the article(s) you request to have removed :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="article_link" id="article_link" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-4 control-label">Comments :</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
                            </div>
                        </div>
                        
                    </form>
                </div>
				</div>
				
				<div id="RequestContentRemovalListDivId" style="display: none">
                    
                    <h3>Request Content Removal : Verify Request</h3>
                    <p style="text-align:center;color:#ff0000;">When you submit your request, we will record your IP address as:  
                        <?php 
                               $user_ip_address = getenv('HTTP_CLIENT_IP')?: getenv('HTTP_X_FORWARDED_FOR')?: getenv('HTTP_X_FORWARDED')?: getenv('HTTP_FORWARDED_FOR')?: getenv('HTTP_FORWARDED')?: getenv('REMOTE_ADDR');
                               echo $user_ip_address;    
                         ?>
                    </p>
                    <button type="button" class="btn btn-info pull-right" id="edit_content_removal_request">Edit</button>
                    <div class="clearfix"></div>
                    <div class="requestRemove">
                        <div class="row">
                            <div class="col-md-2"></div>
                            <label class="col-md-3 control-label">First Name :  </label>
                            <div class="col-md-6 top-padding rcrFname"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-2"></div>
                            <label class="col-md-3 control-label">Last Name :  </label>
                            <div class="col-md-6 top-padding rcrLname"></div>
                        </div>   

                        <div class="row">
                            <div class="col-md-2"></div>
                            <label class="col-md-3 control-label">Email :  </label>
                            <div class="col-md-6 top-padding rcrEmail"></div>
                        </div>    

                        <div class="row">
                            <div class="col-md-2"></div>
                            <label class="col-md-3 control-label">Phone :  </label>
                            <div class="col-md-6 top-padding rcrPhone"></div>
                        </div>  

                        <div class="row">
                            <div class="col-md-2"></div>
                            <label class="col-md-3 control-label">Link To remove :  </label>
                            <div class="col-md-6 top-padding rcrLinkRemove"></div>
                        </div>      

                        <div class="row">
                            <div class="col-md-2"></div>
                            <label class="col-md-3 control-label">Comment :  </label>
                            <div class="col-md-6 top-padding rcrComment"></div>
                        </div>   
                        
                    </div>     
                    
                </div>
				 
				
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info" id="continue_content_removal">Continue</button>
				<button type="button" class="btn btn-info" id="submit_content_removal_request" style="display: none">Submit Request</button>
                <input type="hidden" name="rootFolder" id="rootFolder" value="<?php echo $_REQUEST['folder_id'];?>">
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="report_content" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="">Report Content <span style="text-align:right;float: right;padding-right: 31px;color:green">.</span></h4>
            </div>
            <div class="modal-body"> 
			<p style="font-size:20px;"><b>Report Content</b></p>
			<p>This is where you can report inappropriate content you find on ArchiveInABox.
				<br/><br/>If you are looking for the place to Request Content Removal, <a href="javascript:OpenContentRemovalForm();" style="color:red;">click here.</a></p>
				<br/> 
                <div class="removeLink"> 
                    <form class="form-horizontal" name="report_content_form" id="report_content_form" method="POST">
                        <input type="hidden" name="request_type" id="request_type" value="RC">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">First Name : <span>*</span></label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="first_name"   placeholder="">
                            </div>
                        </div> 
			<input type="text" id="field_report_content" name="field_report_content" value="" style="display:none">
			<input type="text" name="timestamp_value" value="<?php echo time();?>" style="display:none">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Last Name :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="last_name"   placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Email : <span>*</span></label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="email"  placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Phone Number : </label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" id="phone_number_report" name="phone_number" placeholder="" maxlength="14">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone">Link to the content you wish to report: <span>*</span></label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="article_link"  placeholder="">
                            </div>
                        </div>
						<div class="form-group">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone">Reason for reporting this content: <span>*</span></label>
                            <div class="col-sm-7">
                                <select class="form-control" name="reporting_reason" id="reporting_reason">
									<option value="0">--Select--</option>
									<?php foreach($reportingRessionArray as $reportValue){?>
									<option value="<?php echo $reportValue;?>"><?php echo $reportValue;?></option>
									<?php }?>
								</select>
                            </div>
                        </div>
						<div class="form-group marginBottomNone">
                            <label for="" class="col-sm-4 control-label topPaddMarginNone"> </label>
                            <div class="col-sm-7">
                                <span class="reportProvide">Please provide details in the comments section. Be specific.</span>
                            </div>
                        </div> 
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-4 control-label">Comments : </label>
                            <div class="col-sm-7">
                                <textarea class="form-control"  name="comments" rows="3"></textarea>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info" id="continue_report_content">Continue</button>
                <input type="hidden" name="rootFolder" id="rootFolder" value="<?php echo $_REQUEST['folder_id'];?>">
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="front_contact_us" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="">Contact Us <span style="text-align:right;float: right;padding-right: 31px;color:green">.</span></h4>
            </div>
            <div class="modal-body"> 
			
                <div class="removeLink"> 
                    <form class="form-horizontal" name="front_contact_us_form" id="front_contact_us_form" method="POST">
                        <input type="hidden" name="request_type" id="front_contact_us_request_type" value="CS">
                        <input type="hidden" name="search_type" id="search_type" value="C">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Name : <span>*</span></label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="contact_us_name" id="contact_us_name" placeholder="">
                            </div>
                        </div> 
			<input type="text" id="front_contact_us_content" name="front_contact_us_content" value="" style="display:none">
			<input type="text" name="timestamp_value" value="<?php echo time();?>" style="display:none">
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Organization Name :</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="organization_name" id="organization_name"  placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Email : <span>*</span></label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="front_contact_us_email"  id="front_contact_us_email" placeholder="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-4 control-label">Phone Number : </label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control" id="front_contact_us_phone" name="front_contact_us_phone" placeholder="" maxlength="14">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-4 control-label">Comments : </label>
                            <div class="col-sm-7">
                                <textarea class="form-control"  name="comments" rows="3"></textarea>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info" id="front_contact_us_submit">Submit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="start_trouble_ticket_popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="">Start Trouble Ticket <span style="text-align:right;float: right;padding-right: 31px;color:green">.</span></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" name="start_trouble_ticket_form" id="start_trouble_ticket_form" method="POST">
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Name : <span>*</span></label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" name="name" id="name" placeholder="Enter your name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Organization : <span>*</span></label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" name="organization" id="organization" placeholder="Enter your organization">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Email : <span>*</span></label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" name="trouble_email" id="trouble_email" placeholder="Enter your email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Phone : </label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter your Phone" maxlength="14">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">User Type : <span>*</span></label>
                        <div class="col-sm-7">
                            <select name="user_type" id="user_type" class="form-control">
                                <option value="">Select user type</option>
                                <option value="Administrator">Administrator</option>
                                <option value="Public User">Public User</option>
                                <option value="Assistant">Assistant</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Type Of Trouble : <span>*</span></label>
                        <div class="col-sm-7">
                            <select name="type_of_trouble" id="type_of_trouble" class="form-control">
                                <option value="">Select type of trouble</option>
                                <option value="Need Login Instructions">Need Login Instructions</option>
                                <option value="Forgot Password">Forgot Password</option>
                                <option value="Did Not Receive Validation">Did Not Receive Validation</option>
                                <option value="Can't Upload File">Can't Upload File</option>
                                <option value="Can't Log In">Can't Log In</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Your Computer : <span>*</span></label>
                        <div class="col-sm-7">
                            <select name="your_computer" id="your_computer" class="form-control">
                                <option value=""> Select your computer </option>
                                <option value="PC">PC</option>
                                <option value="Macintosh">Macintosh</option>
                                <option value="iPad">iPad</option>
                                <option value="Android Tablet">Android Tablet</option>
                                <option value="iPhone">iPhone</option>
                                <option value="Android Phone">Android Phone</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Browser Type : <span>*</span></label>
                        <div class="col-sm-7">
                            <select name="browser_type" id="browser_type" class="form-control">
                                <option value="">Select you browser</option>
                                <option value="Chrome">Chrome</option>
                                <option value="Firefox">Firefox</option>
                                <option value="Internet Explorer">Internet Explorer</option>
                                <option value="Safari">Safari</option>
                                <option value="Edge">Edge</option>
                                <option value="Opera">Opera</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-4 control-label">Internet Connection : <span>*</span></label>
                        <div class="col-sm-7">
                            <select name="internet_connection" id="internet_connection" class="form-control">
                                <option value="">Select your internet connection</option>
                                <option value="Broadband/Cable">Broadband/Cable</option>
                                <option value="Cellular">Cellular</option>
                                <option value="Dialup Explorer">Dialup Explorer</option>
                                <option value="DSL">DSL</option>
                                <option value="Office Wireless">Office Wireless</option>
                                <option value="WiFi">WiFi</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-4 control-label">Your Message : </label>
                        <div class="col-sm-7">
                            <textarea class="form-control"  name="your_message" id="your_message" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-4 control-label"></label>
                        <div class="col-sm-7">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-info" id="start_trouble_ticket_form_submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php foreach ($jsArray as $key => $fileName) { ?>
    <script src="<?php echo JS_PATH . $fileName; ?>"></script>
<?php } ?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23911814-1"></script>
<?php
include_once COMMON_TEMPLATE_PATH . 'popup.php';
?>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'UA-23911814-1');
</script>
<script type="text/javascript">
    $(document).ready(function(){
        $('.sidebar-menu li').each(function(){
            if($(this).hasClass('menu-open')){
                $(this).children('ul').show();
            }
        });
        
        jQuery.validator.addMethod("validEmailid", function (value, element) {
            return this.optional(element) || /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/i.test(value);
        }, "Please Enter Valid EmailId");    
        $.validator.addMethod("LinkCorrect",function (value, element, regexp) {
            var check = false;
            return this.optional(element) || regexp.test(value);
        },"Please check your input.");
        $.validator.addMethod("valueNotEquals", function(value, element, arg){
            return arg !== value;
        }, "Value must not equal arg."); 
        var phones = [{ "mask": "(###) ###-####"}, { "mask": "(###) ###-##############"}];
        $('#phone_number').inputmask({ 
            mask: phones, 
            greedy: false, 
            definitions: { '#': { validator: "[0-9]", cardinality: 1}} 
        });  
        $('#phone_number_report').inputmask({ 
            mask: phones, 
            greedy: false, 
            definitions: { '#': { validator: "[0-9]", cardinality: 1}} 
        });
        $('#front_contact_us_phone').inputmask({ 
            mask: phones, 
            greedy: false, 
            definitions: { '#': { validator: "[0-9]", cardinality: 1}} 
        });
        $('#phone').inputmask({
            mask: phones,
            greedy: false,
            definitions: { '#': { validator: "[0-9]", cardinality: 1}}
        });
        
        $("#content_removal_form").validate({
            rules: {
                first_name:{
                    required: true
                },
                last_name:{
                    required: true
                },
                email:{
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                },
                phone_number:{
                    required: true
                    
                },
                article_link:{
                    required: true,
		    LinkCorrect : /^(?:http(?:s)?:\/\/)?(?:[^\.]+\.)?archiveinabox\.com/
                },
                comments:{
                    required: true
                }
            },
            messages: { 
                first_name:{
                    required: "First name is required."
                },
                last_name:{
                    required: "Last name is required."
                },
                email:{
                    required: "Email is required.",
                    email: "Please enter valid email Id."
                },
                phone_number:{
                    required: "Phone No. is required."
                },
                article_link:{
                    required: "Article link is required.",
		    LinkCorrect: "Please enter  archiveinabox.com url only "
                },
                comments:{
                    required: "Comments is required."
                }
            }
        });
        
        $("#front_contact_us_form").validate({
            rules: {
                contact_us_name:{
                    required: true
                }, 
		
                front_contact_us_email:{
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                }
		
            },
            messages: { 
                contact_us_name:{
                    required: "Name is required."
                },
                front_contact_us_email:{
                    required: "Email is required.",
                    email: "Please enter valid email Id."
                }
            }
        });
        
        $("#report_content_form").validate({
            rules: {
                first_name:{
                    required: true
                }, 
				last_name:{
                    required: true
                },
                email:{
                    required: {
                        depends:function(){
                            $(this).val($.trim($(this).val()));
                            return true;
                        }
                    },
                    email: true
                }, 
                article_link:{
                    required: true,
		LinkCorrect : /^(?:http(?:s)?:\/\/)?(?:[^\.]+\.)?archiveinabox\.com/
                },
		reporting_reason:{
                    valueNotEquals: '0'
                } 
            },
            messages: { 
                first_name:{
                    required: "Name is required."
                },
				last_name:{
                    required: "Last name is required."
                },
                email:{
                    required: "Email is required.",
                    email: "Please enter valid email Id."
                }, 
                article_link:{
                    required: "Article link is required.",
		    LinkCorrect: "Please enter  archiveinabox.com url only "
                },
		reporting_reason:{
                    valueNotEquals: "Reason for reporting reason is required."
                } 
            }
        });
    });
    $(document).on('click', '.resume-session', function () {
        var user_id = $(this).attr('resume-user-id');
        if (user_id) {
            $('.admin-loading-image').show();
            $.ajax({
                type: 'POST',
                url: 'services_admin_api.php',
                data: {mode: 'resume_user_session', user_id: user_id},
                success: function (response) {
                    var record = JSON.parse(response);
                    if (record.status == 'success') {
                        window.location.href = 'manage_my_archive.php';
                    }
                    $('.admin-loading-image').hide();
                }
            });
        }
    });
    
    $(document).on('click','.front-contact-us',function(){
        $("#front_contact_us").modal('show');
    });
    
    $(document).on('click','.request-removal', function(){
        $('#request_removal').modal('show');
    });
    
    $(document).on('click','.report-content', function(){
        $('#report_content').modal('show');
    });
    
    $(document).on('click', '#front_contact_us_submit', function(){ 
        if($("#front_contact_us_form").valid() && $('#front_contact_us_content').val() =='' ){
            $('.admin-loading-image').show();
            var formData = $('#front_contact_us_form').serialize();
            $.ajax({
               url: "services_admin_api.php",
               type: "post",
               data: {mode: 'submit_request',formData: formData},
               success: function (response) {
                   var result = JSON.parse(response);
                  $('.admin-loading-image').hide();
                   if(result.status == 'success'){
                       $('#front_contact_us_form')[0].reset();
                       $('#front_contact_us').modal('hide');
                       showPopupMessage('success', result.message);
                   }else{
                       showPopupMessage('error', result.message + ' (Error Code: 1145)');
                   }
               },
               error: function () {
                   showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1146)');
               }
            });	
        }
    });
    
    function OpenReportForm(){
        $('#request_removal').modal('hide');
        setTimeout( function(){ 
            $('#report_content').modal('show');
        }  , 500 );	
    } 
    function OpenContentRemovalForm(){
        $('#report_content').modal('hide');
        setTimeout( function(){ 
            $('#request_removal').modal('show');
        }  , 500 );	
    }
    
    $(document).on('click', '#continue_content_removal', function(){  
        if($("#content_removal_form").valid() && $('#field_check_req').val() =='' ){
            $('#RequestContentRemovalFormId').hide();
            $('#RequestContentRemovalListDivId').show();
            $('#submit_content_removal_request').show();
            $('#continue_content_removal').hide();
            
            $('.rcrFname').html($('#first_name').val()); 
            $('.rcrLname').html($('#last_name').val());
            $('.rcrEmail').html($('#email').val());
            $('.rcrPhone').html($('#phone_number').val());
            $('.rcrLinkRemove').html($('#article_link').val());
            $('.rcrComment').html($('#comments').val());
        }    
    
    });
    
    
    $(document).on('click', '#front_contact_us_submit', function(){ 
        if($("#front_contact_us_form").valid() && $('#front_contact_us_content').val() =='' ){
            $('.admin-loading-image').show();
            var formData = $('#front_contact_us_form').serialize();
            $.ajax({
              url: "services_admin_api.php",
              type: "post",
              data: {mode: 'submit_request',formData: formData},
              success: function (response) {
                  var result = JSON.parse(response);
                 $('.admin-loading-image').hide();
                  if(result.status == 'success'){
                      $('#front_contact_us_form')[0].reset();
                      $('#front_contact_us').modal('hide');
                      showPopupMessage('success', result.message);
                  }else{
                      showPopupMessage('error', result.message + ' (Error Code: 1147)');
                  }
              },
              error: function () {
                  showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1148)');
              }
            });	
        }
   });
   
   $(document).on('click', '#submit_content_removal_request', function(){ 
          $('.admin-loading-image').show();
            var formData = $('#content_removal_form').serialize();
            $.ajax({
                url: "services_admin_api.php",
                type: "post",
                data: {mode: 'submit_request',formData: formData},
                success: function (response) {
                    var result = JSON.parse(response);
                   $('.admin-loading-image').hide();
                    if(result.status == 'success'){
                        $('#content_removal_form')[0].reset();
                        $('#request_removal').modal('hide');
                        showPopupMessage('success', result.message);
                    }else{
                        showPopupMessage('error', result.message + ' (Error Code: 1149)');
                    }
                },
                error: function () {
                    showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1150)');
                }
            });	 
    }); 
    $(document).on('click', '#edit_content_removal_request', function(){  
        $('#RequestContentRemovalFormId').show();
        $('#RequestContentRemovalListDivId').hide();
        $('#submit_content_removal_request').hide();
        $('#continue_content_removal').show();
    });
    function showPopupMessage(type, message) {
        $('.jquery-popup-overwrite').show();
        if (type == 'error') {
            $('#error_message').html(message);
            $('.errorMessage').show();
        } else if (type == 'success') {
            $('#success_message').html(message);
            $('.successMessage').show();
        } else {

        }
        //(type == 'error') ? $('.errorMessage').show() : $('.successMessage').show();
    }
    $(document).on('click', '.dismiss-popup', function () {
        $('.errorMessage').hide();
        $('.successMessage').hide();
        $('.jquery-popup-overwrite').hide();
    });
    
    $(document).on('click', '#continue_report_content', function(){ 
    if($("#report_content_form").valid() && $('#field_report_content').val() =='' ){
        $('.admin-loading-image').show();
        //var item_id='<?php // echo ($p_folder_id != '' && $p_folder_id != 0) ? $p_folder_id : 1 ;?>';
        var formData = $('#report_content_form').serialize();
        $.ajax({
            url: "services_admin_api.php",
            type: "post",
            data: {mode: 'submit_request',formData: formData},
            success: function (response) {
                var result = JSON.parse(response);
                $('.admin-loading-image').hide();
                if(result.status == 'success'){
                    $('#report_content_form')[0].reset();
                    $('#report_content').modal('hide');
                    showPopupMessage('success', result.message);
                }else{
                    showPopupMessage('error', result.message + ' (Error Code: 1151)');
                }
            },
            error: function () {
                showPopupMessage('error','Something went wrong, Please try again. (Error Code: 1152)');
            }
        });	
    }
});
</script>
</body>
</html>