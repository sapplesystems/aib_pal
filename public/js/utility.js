function popitup2() {
    newwindow2 = window.open('', 'linkpopup', 'height=500,width=800,titlebar=0');
    var tmp = newwindow2.document;
    tmp.write('<html><head><title>Sharing This Page</title>');
    tmp.write('</head><body>');
    tmp.write('<center><h2>Sharing This Page</h2></center>');
    tmp.write('<br>');
    tmp.write('<p><font face="arial,helvetica,universe">');
    tmp.write('<b>You are welcomed and encouraged to share the great historical information and photos on this site,');
    tmp.write(' but you may do so only on the following basis:</b>');
    tmp.write('<ul>');
    tmp.write('<li>Share only the complete URL address of the page you want to share</li>');
    tmp.write('<li>Sharing screenshots is allowable ONLY IF the screenshot is linked directly to the full page from which it was clipped</li>');
    tmp.write('<li>Shared pages and clips may not be used where a fee is charged to third-party users</li>');
    tmp.write('</ul><br>');
    tmp.write('Please see our <a href="http://www.smalltownpapers.com/terms.php" title="Terms of Use">Terms of Use</a> and <a href="/copyrightnotice.php" title="Copyright Notice">Copyright Notice</a> to be sure you are using our content ');
    tmp.write('in a permitted manner.  If unsure, please write to us.</font></p><br>');
    tmp.write('<p>Copy this link to share: <input size="60" name="dummy" type="text" value="http://spm.stparchive.com/Archive/SPM/SPM01142016P001.php"></p>');
    tmp.write('<br><br>');
    tmp.write('<p><a href="javascript:self.close()">CLOSE THIS WINDOW</a></p>');
    tmp.write('</body></html>');
    tmp.close();
    return false;
}

function show_error_popup(){
    newwindow4 = window.open('', 'errorpopup', 'height=400,width=500,titlebar=0');
    var tmp = newwindow4.document;
    tmp.write('<html><head><title>Using This Page</title>');
    tmp.write('</head><body>');
    tmp.write('<center><h2>Sharing This Page</h2></center>');
    tmp.write('<center><p>If you would like to share this image, copy and paste the URL address ');
    tmp.write('or use the "Share" link on the page<br><br>For information on obtaining high-resolution ');
    tmp.write('copies of scans, please write to us.</p>');
    tmp.write('<br><br>');
    tmp.write('<p><a href="javascript:self.close()">CLOSE THIS WINDOW</a></p>');
    tmp.write('</body></html>');
    tmp.close();
    return false;
}
/*
$(document).ready(function () {
    document.oncontextmenu = new Function("return false");
    $(window).keyup(function (e) {
		//Fix start for Issue ID 2505 on 28-Jul-2025
		/*
        if (e.keyCode === 44){
            show_error_popup();
            return false;
        }
		*/
		//Fix end for Issue ID 2505 on 28-Jul-2025
  
	/*if (e.ctrlKey && (e.which === 80 || e.keyCode === 67 || e.which === 85 || e.which === 117)){
            e.preventDefault();
            e.stopImmediatePropagation();
            //show_error_popup();
            return false;
        }
    });
});*/

/*
$(document).ready(function () {
    $(window).keydown(function (e) {
        if (e.ctrlKey && (e.which === 80 || e.keyCode === 67 || e.which === 85 || e.which === 117)){
            e.preventDefault();
            e.stopImmediatePropagation();
            show_error_popup();
            return false;
        }
    });
});

$(document).keypress(function(e) {
    if(e.ctrlKey && (e.which === 85 || e.which === 117) ){
        //show_error_popup();
        return false;
    }
});

// Allow copy/select only inside #llm-stream-container
$(document).on("contextmenu", function (e) {
    if ($(e.target).closest('#llm-stream-container').length === 0) {
        e.preventDefault();
    }
});

$(document).on("keyup keydown keypress", function (e) {
    if ($(e.target).closest('#llm-stream-container').length > 0) {
        return true; // allow inside container
    }
    if (e.ctrlKey && (e.which === 80 || e.keyCode === 67 || e.which === 85 || e.which === 117)) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
    }
});*/


/*
function disableselect(e) {
    return false;
}

function reEnable() {
    return true;
}

//if IE4+

document.onselectstart = new Function("return false");

//if NS6

if (window.sidebar) {
    document.onmousedown = disableselect();
    document.onclick = reEnable;
}

var message = "Sorry, right-click has been disabled";
function clickIE() {
    if (document.all) {
        (message);
        return false;
    }
}

function clickNS(e) {
    if (document.layers || (document.getElementById && !document.all)){
        if (e.which == 2 || e.which == 3) {
            (message);
            return false;
        }
    }
}

if (document.layers){
    document.captureEvents(Event.MOUSEDOWN);
    document.onmousedown = clickNS;
} else {
    document.onmouseup = clickNS;
    document.oncontextmenu = clickIE;
}
document.oncontextmenu = new Function("return false");

*/
