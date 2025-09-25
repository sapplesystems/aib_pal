<?php

/*
$dbConn = mysql_connect (DBHOST, DBUSER, DBPASS) or die ('MySQL connect failed. ' . mysql_error());
$GLOBALS["dbConn"] = $dbConn;
mysql_select_db(DATABASE) or die('Cannot select database. ' . mysql_error());

function dbQuery($sql)
{
	$result = mysql_query($sql) or die(mysql_error());
	
	return $result;
}

function dbAffectedRows()
{
	$dbConn = $GLOBALS["dbConn"];
	
	return mysql_affected_rows($dbConn);
}

function dbFetchArray($result, $resultType = MYSQL_NUM) {
	return mysql_fetch_array($result, $resultType);
}

function dbFetchAssoc($result)
{
	return mysql_fetch_assoc($result);
}

function dbFetchRow($result) 
{
	return mysql_fetch_row($result);
}

function dbFreeResult($result)
{
	return mysql_free_result($result);
}

function dbNumRows($result)
{
	return mysql_num_rows($result);
}

function dbSelect($dbName)
{
	return mysql_select_db($dbName);
}

function dbInsertId()
{
	return mysql_insert_id();
}
*/
?>
