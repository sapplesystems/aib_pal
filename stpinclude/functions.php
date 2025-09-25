<?php
/*
	Check if a session user id exist or not. If not set redirect
	to login page. If the user session id exist and there's found
	$_GET['logout'] in the query string logout the user
*/
function checkUser()
{
	// if the session id is not set, redirect to login page
	if (!isset($_SESSION['stp_user_id'])) {
		header('Location: ' . HOST_ROOT_PATH . 'admin/login.php');
		exit;
	}
	
	// the user want to logout
	if (isset($_GET['logout'])) {
		doLogout();
	}
}

/*
	
*/
function doLogin()
{
	// if we found an error save the error message in this variable
	$errorMessage = '';
	
	$userName = $_POST['txtUserName'];
	$password = $_POST['txtPassword'];
	
	// first, make sure the username & password are not empty
	if ($userName == '') {
		$errorMessage = 'You must enter your username';
	} else if ($password == '') {
		$errorMessage = 'You must enter the password';
	} else {
		// check the database and see if the username and password combo do match
		$sql = "SELECT user_id
		        FROM tbl_user 
				WHERE user_name = '$userName' and user_password='".base64_encode($password)."'";
		$result = dbQuery($sql);
	
		if (dbNumRows($result) == 1) {
			$row = dbFetchAssoc($result);
			$_SESSION['stp_user_id'] = $row['user_id'];
			
			// log the time when the user last login
			$sql = "UPDATE tbl_user 
			        SET user_last_login = NOW() 
					WHERE user_id = '{$row['user_id']}'";
			dbQuery($sql);

			// now that the user is verified we move on to the next page
            // if the user had been in the admin pages before we move to
			// the last page visited
			if (isset($_SESSION['login_return_url'])) {
				header('Location: ' . $_SESSION['login_return_url']);
				exit;
			} else {
				header('Location: index.php');
				exit;
			}
		} else {
			$errorMessage = 'Wrong username or password';
		}		
			
	}
	
	return $errorMessage;
}

/*
	Logout a user
*/
function doLogout()
{
	if (isset($_SESSION['stp_user_id'])) {
		unset($_SESSION['stp_user_id']);
		session_unregister('stp_user_id');
	}
		
	header('Location: login.php');
	exit;
}


function uploadFile($PATH,$FILENAME,$FILEBOX)
	{
		$file=$PATH.$FILENAME;
		$uploaded="TRUE";
		global $_FILES;
		if (! @file_exists($file))
		{
			if ( isset($_FILES[$FILEBOX] ) )
			{
				//copy($_FILES[$FILEBOX]['tmp_name'], $PATH.$FILENAME);
			
				if (is_uploaded_file($_FILES[$FILEBOX]['tmp_name']))
				{
				 $_FILES[$FILEBOX]['tmp_name'];
						move_uploaded_file($_FILES[$FILEBOX]['tmp_name'], $file);
					//$_FILES[$FILEBOX]['tmp_name'], $PATH.$FILENAME);
					
				}else{
					$uploaded="FALSE";
				}
			}
		} //end of if @fileexists
		return $uploaded;
	}
		 
function updatePage($data)
{
    if($data['allow_more_newspaper']=='')
    {
        $data['allow_more_newspaper']='N';
    }
	if($data['publogo']!='')
	{
		$upadteQuery="update pagecontent set pub_title='".$data['pub_title']."',city='".$data['city']."',state='".$data['state']."',welcome_text='".$data['welcome_text']."',publogo='".$data['publogo']."',client_text='".$data['client_text']."',publication_website='".$data['publication_website']."',page_width='".$data['page_width']."',copyright_owner='".$data['copyright_owner']."',yearbook_archive='".$data['yearbook_archive']."',yearbook_cover='".$data['yearBookCover']."',survey='".$data['survey']."',allow_ads='".$data['allow_ads']."',allow_more_newspaper='".$data['allow_more_newspaper']."' WHERE id='".$data['pageContentId']."'";		
		
		$result = dbQuery($upadteQuery);
		
		
	}
	else
	{
		$upadteQuery="update pagecontent set pub_title='".$data['pub_title']."',city='".$data['city']."',state='".$data['state']."',welcome_text='".$data['welcome_text']."',client_text='".$data['client_text']."',publication_website='".$data['publication_website']."',page_width='".$data['page_width']."',copyright_owner='".$data['copyright_owner']."',yearbook_archive='".$data['yearbook_archive']."',yearbook_cover='".$data['yearBookCover']."',survey='".$data['survey']."',allow_ads='".$data['allow_ads']."',allow_more_newspaper='".$data['allow_more_newspaper']."' WHERE id='".$data['pageContentId']."'";
		
		$result = dbQuery($upadteQuery);
	}
	
	if($result)
		{
		return 1;
		}
		else
		{
		return 0;
		}
}


function addPage($data)
{
    if($data['allow_more_newspaper']=='')
    {
        $data['allow_more_newspaper']='N';
    }
	$addQuery="insert into newspaper_master(newspaper_name,sort_name)values('".$data['newspaper_name']."','".$data['sort_name']."')";	
	$result = dbQuery($addQuery);		
	$inserted_id=mysql_insert_id();
				
	$addPageQuery="insert into pagecontent(newspaperId,pub_title,city,state,welcome_text,publogo,publication_website,client_text,page_width,yearbook_archive,yearbook_cover,survey,copyright_owner,allow_ads,allow_more_newspaper)values('".$inserted_id."','".$data['pub_title']."','".$data['city']."','".$data['state']."','".$data['welcome_text']."','".$data['publogo']."','".$data['publication_website']."','".$data['client_text']."','".$data['page_width']."','".$data['yearbook_archive']."','".$data['yearBookCover']."','".$data['survey']."','".$data['copyright_owner']."','".$data['allow_ads']."','".$data['allow_more_newspaper']."')";
		
		$result = dbQuery($addPageQuery);		
	
	if($result)
		{
		//create a new directory in Main folder fro this news paper
		$path=ROOT_PATH."Main/";
		$dirName=$data['sort_name'];
		mkdir("$path/$dirName",0777,true);	
		
		$addCat="insert into categories(category,parent_num)values('".$data['sort_name']."',0)";
		$resultCat = dbQuery($addCat);	
		
		return 1;
		}
		else
		{
		return 0;
		}

}


function updateSearchCode($data)
{
	$upadteQuery="update pagecontent set search_code='".addslashes(htmlentities($data['search_code']))."' WHERE id='".$data['pageid']."'";
	$result = dbQuery($upadteQuery);
	if($result)
	{
		return 1;
	}
	else
	{
		return 0;
	}

}



function changePassword()
{	
	$oldPassword=$_POST['txtOldPassword']; 
	
	$newPassword=base64_encode($_POST['txtNewPassword1']); 
	$confirmPassword=$_post['confirmPassword']; 

	$checkQuery="SELECT user_password FROM tbl_user where user_id=".$_SESSION['stp_user_id'];
	
	$result=dbQuery($checkQuery);
	$record=mysql_fetch_assoc($result);

	$record_passwords=base64_decode($record['user_password']);
	
	if(strcmp($record_passwords,$oldPassword)==0)
	{
		$insertQuery="update tbl_user set user_password='$newPassword' where user_id='".$_SESSION['stp_user_id']."'";
		
		$insresult=dbQuery($insertQuery);
		if($insresult)
		{
			return "success";			
		}
	}
	else
	{
		return "fail";		
	}
	
}



/**************************
	Paging Functions
***************************/

function getPagingQuery($sql, $itemPerPage = 10)
{
	if (isset($_GET['pageno']) && (int)$_GET['pageno'] > 0) {
		$pageno = (int)$_GET['pageno'];
	} else {
		$pageno = 1;
	}
	
	// start fetching from this row number
	$offset = ($pageno - 1) * $itemPerPage;
	
	return $sql . " LIMIT $offset, $itemPerPage";
}

/*
	Get the links to navigate between one result page to another.
	Supply a value for $strGet if the page url already contain some
	GET values for example if the original page url is like this :
	
	http://www.phpwebcommerce.com/plaincart/index.php?c=12
	
	use "c=12" as the value for $strGet. But if the url is like this :
	
	http://www.phpwebcommerce.com/plaincart/index.php
	
	then there's no need to set a value for $strGet
	
	
*/
function getPagingLink($totalPageCount, $itemPerPage = 10, $strGet = '')
{
	//$result        = dbQuery($sql);
	$pagingLink    = '';
	//$totalResults  = dbNumRows($result);
	$totalResults  = $totalPageCount;
	$totalPages    = ceil($totalResults / $itemPerPage);
	
	// how many link pages to show
	$numLinks      = 10;

		
	// create the paging links only if we have more than one page of results
	if ($totalPages > 1) {
	
		$self = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] ;
		
		/*if(isset($_GET['page'])||$_GET['page']!='')
		{
		$self = HOST_ROOT_PATH.$_GET['target']."/".$_GET['page']."/";
		}
		else
		{
		$self = HOST_ROOT_PATH.$_GET['target']."/";
		}*/
		

		if (isset($_GET['pageno']) && (int)$_GET['pageno'] > 0) {
			$pageNumber = (int)$_GET['pageno'];
		} else {
			$pageNumber = 1;
		}
		
		// print 'previous' link only if we're not
		// on page one
		if ($pageNumber > 1) {
			$pageno = $pageNumber - 1;
			if ($pageno > 1) {
				$prev = " <a href=\"$self?&pageno=$pageno&$strGet/\">[Prev]</a> ";
			} else {
				$prev = " <a href=\"$self?&$strGet\">[Prev]</a> ";
			}	
				
			$first = " <a href=\"$self?&$strGet\">[First]</a> ";
		} else {
			$prev  = ''; // we're on page one, don't show 'previous' link
			$first = ''; // nor 'first page' link
		}
	
		// print 'next' link only if we're not
		// on the last page
		if ($pageNumber < $totalPages) {
			$pageno = $pageNumber + 1;
			$next = " <a href=\"$self?&pageno=$pageno&$strGet\">[Next]</a> ";
			$last = " <a href=\"$self?&pageno=$totalPages&$strGet\">[Last]</a> ";
		} else {
			$next = ''; // we're on the last page, don't show 'next' link
			$last = ''; // nor 'last page' link
		}

		$start = $pageNumber - ($pageNumber % $numLinks) + 1;
		$end   = $start + $numLinks - 1;		
		
		$end   = min($totalPages, $end);
		
		$pagingLink = array();
		for($pageno = $start; $pageno <= $end; $pageno++)	{
			if ($pageno == $pageNumber) {
				$pagingLink[] = " $pageno ";   // no need to create a link to current page
			} else {
				if ($pageno == 1) {
					$pagingLink[] = " <a href=\"$self?&$strGet\">$pageno</a> ";
				} else {	
					$pagingLink[] = " <a href=\"$self?&pageno=$pageno&$strGet\">$pageno</a> ";
				}	
			}
	
		}
		
		$pagingLink = implode(' | ', $pagingLink);
		
		// return the page navigation link
		$pagingLink = $first . $prev . $pagingLink . $next . $last;
	}
	
	return $pagingLink;
}

?>