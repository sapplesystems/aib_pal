<?php

print("<head><title>Test page</title></head><body>");
$Buffer = shell_exec("python3 /mnt/data/stparch/virtual_sites/www.archiveinabox.com/test.py 'find a leaping animal' 2>/tmp/python_debug.txt");
print("<pre>$Buffer</pre>");
print("</body></html>");
?>
