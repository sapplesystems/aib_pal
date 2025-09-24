//
// aib.js
//

// Copyright (c) 2002-2004, Ross Smith.  All rights reserved.
// Licensed under the BSD or LGPL License. See license.txt for details.

var _Bin2HexTable = [
    '0', '1', '2', '3', '4', '5', '6', '7',
    '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'
];

var _Hex2BinTable = [
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
     0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 0, 0, 0, 0, 0, // 0-9
     0,10,11,12,13,14,15, 0, 0, 0, 0, 0, 0, 0, 0, 0, // A-F
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
     0,10,11,12,13,14,15, 0, 0, 0, 0, 0, 0, 0, 0, 0, // a-f
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
     0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
];

function bin2hex(str) {
    var len = str.length;
    var rv = '';
    var i = 0;
    var c;
    
    while (len-- > 0) {
        c = str.charCodeAt(i++);

        rv += _Bin2HexTable[(c & 0xf0) >> 4];
        rv += _Bin2HexTable[(c & 0x0f)];
    }

    return rv;
}

function hex2bin(str) {
    var len = str.length;
    var rv = '';
    var i = 0;

    var c1;
    var c2;

    while (len > 1) {
        h1 = str.charAt(i++);
        c1 = h1.charCodeAt(0);
        h2 = str.charAt(i++);
        c2 = h2.charCodeAt(0);
        
        rv += String.fromCharCode((_Hex2BinTable[c1] << 4) + _Hex2BinTable[c2]);
        len -= 2;
    }

    return rv;
}

function set_cookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function get_cookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }

    return "";
}

function check_cookie(CookieName) {
	var TestValue = get_cookie(CookieName);
	if (TestValue != "")
	{
		return(TestValue);
	}

	return(null);
}

// Send request to server using POST format.  Returns data received
// or FALSE if the required page ID cookie isn't available.
// ----------------------------------------------------------------
function aib_ajax_request(URL,FieldSet,SuccessFunc,FailFunc)
{
	var LocalFieldSet;
	var LocalPageID;

	// Get page ID from cookies

	LocalPageID = check_cookie('aib_page_id');
	if (LocalPageID == null)
	{
		return(false);
	}

	// Create a copy of the field set, add the page ID

	LocalFieldSet = FieldSet;
	LocalFieldSet['s'] = LocalPageID;

	// Ajax request

	$.ajax({
		type: "POST",
		url: URL,
		data: LocalFieldSet,
		success: SuccessFunc,
		error: FailFunc,
		dataType: "json"
		});

	return(true);
}

// Given the ID of a table, append a row
// -------------------------------------
function aib_add_row_to_table(TableName,RowHTML)
{
	var TableObject;

	TableObject = $('#' + TableName);
	if (TableObject == undefined)
	{
		return(-1);
	}

	$('#' + TableName + ' > tbody').append(RowHTML);
	return(0);
}


