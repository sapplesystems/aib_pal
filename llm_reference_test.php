<?php
require_once dirname(__FILE__) . '/config/config.php';


$postData = array(
        "_id" => APIUSER,
        "_key" => APIKEY
    );

$CurlObj = curl_init();
	$Options = array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => "https://www.archiveinabox.com/api/session.php",
		CURLOPT_FRESH_CONNECT => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FORBID_REUSE => 0,
		CURLOPT_TIMEOUT => 300,
		CURLOPT_POSTFIELDS => http_build_query($postData)
	);

	curl_setopt_array($CurlObj,$Options);
	 $Result = curl_exec($CurlObj);
	if ($Result == false)
	{
		$OutData = array("status" => "ERROR", "info" => curl_error($CurlObj));
	}
	else
	{
		$OutData = json_decode($Result,true);
	}

	curl_close($CurlObj);

//print_R($OutData);die;
 $sessionKey = $OutData['info'];
// ----------- Configuration -----------
// define('APIKEY', APIKEY);
define('SESSION_KEY', $sessionKey); // Adjust as needed
// define('BROWSE_API_URL', 'https://your-server.com/path/to/api'); // Adjust to your real endpoint
define('THUMB_PLACEHOLDER', 'https://via.placeholder.com/80x80?text=No+Thumb');

// MOCK FUNCTION: Replace with actual implementation
function aibServiceRequest($postData, $fileName, $mail = null) {
    // Create a new curl resource
    $curlObj = curl_init();
    $options = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => AIB_SERVICE_URL . '/api/' . $fileName . ".php",
        CURLOPT_FRESH_CONNECT => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 0,
        CURLOPT_TIMEOUT => 1200,
        CURLOPT_POSTFIELDS => http_build_query($postData)
    );
    // Set multiple options for a curl transfer
    curl_setopt_array($curlObj, $options);
    $result = curl_exec($curlObj);
    if ($result == false) {
        $outData = array("status" => "ERROR", "info" => curl_error($curlObj));
    } else {
        $outData = json_decode($result, true);
    }
    // close curl resource
    curl_close($curlObj);
    if (isset($outData['info']) && $outData['info'] == 'EXPIRED' && $mail == null) {
        unset($_SESSION);
        session_destroy();
        header('Location: home.php');
        exit;
    } else {
        return ($outData);
    }
}

// MOCK FUNCTION: Replace with actual implementation
function getTreeData($folderId = '') {
    if ($folderId != '') {
        if (isset($_SESSION['tree_data'][$folderId])) {
            return $_SESSION['tree_data'][$folderId];
        } else {
            $sessionKey = $_SESSION['aib']['session_key'];
            // Request array to get item tree data        
            $postData = array(
                "_key" => APIKEY,
                "_session" => $sessionKey,
                "_user" => 1,
                "_op" => "get_path",
                "obj_id" => $folderId,
                "opt_get_property" => 'Y'
            );
            // Service request to get item tree data        
            $apiResponse = aibServiceRequest($postData, 'browse');
            if ($apiResponse['status'] == 'OK') {
                $_SESSION['tree_data'][$folderId] = $apiResponse['info']['records'];
                return $apiResponse['info']['records'];
            }
        }
    }
}

// -------------------------------------

function extractReferencesFromRawInfo($raw_info) {
    $refs = [];
    if (preg_match_all('/<llm_doc>(.*?)<\/llm_doc>/s', $raw_info, $matches)) {
        foreach ($matches[1] as $block) {
            $score = $content = $meta = $nodeid = $fragment = '';
            if (preg_match('/<score>(.*?)<\/score>/s', $block, $m)) $score = trim($m[1]);
            if (preg_match('/<content>(.*?)<\/content>/s', $block, $m)) $content = trim($m[1]);
            if (preg_match('/<metadata>(.*?)<\/metadata>/s', $block, $m)) {
                // Try to read as JSON-like string
                $rawMet = trim($m[1]);
                // 'nodeid': '000204da', 'fragment': '4'
                $rawMet = str_replace("'", '"', $rawMet);
                $rawMet = '{' . preg_replace('/,?(\s*)"(.+?)":/', ',"$2":', trim(trim($rawMet, '{}'))) . '}';
                $metaArr = @json_decode($rawMet, true);
                if (is_array($metaArr)) {
                    $nodeid = $metaArr['nodeid'] ?? '';
                    $fragment = $metaArr['fragment'] ?? '';
                }
            }
            if (!$nodeid && preg_match('/@uri=(\d+)/', $content, $m)) {
                $nodeid = $m[1];
            }
            $refs[] = [
                'score'    => $score,
                'content'  => $content,
                'nodeid'   => $nodeid,
                'fragment' => $fragment,
            ];
        }
    }
    return $refs;
}

$references = [];
$fullItemInfo = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['raw_info'])) {
    $raw_info = $_POST['raw_info'];
    $references = extractReferencesFromRawInfo($raw_info);
    $itemNodeIds = array_unique(array_filter(array_column($references, 'nodeid')));
    if (!empty($itemNodeIds)) {
        // ---------- Fetch details from your Browse API ------------
        $browseData = [
            "_key"              => APIKEY,
            "_session"          => SESSION_KEY,
            "_user"             => 1,
            "_op"               => "list",
            "opt_sort"          => "ID",
            "parent"            => -1,
            "opt_get_files"     => "Y",
            "opt_deref_links"   => "Y",
            "opt_get_property"  => "Y",
            "opt_get_long_prop" => "Y",
            "item_list"         => implode(',', $itemNodeIds)
        ];
        $fullItemInfo = aibServiceRequest($browseData, 'browse');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>LLM Reference Test Page</title>
    <style>
        .ref-card { border:1px solid #ccc; padding:15px; margin:15px 0; background:#f9f9f9; border-radius:5px; display:flex; }
        .ref-thumb { margin-right:15px; }
        .ref-thumb img { width:80px; height:80px; object-fit:cover; border-radius:3px; background:#eee; }
        .ref-content { flex:1; }
        .breadcrumb { font-size:13px; color:#666; margin-bottom:8px; }
        .ref-title { font-weight:bold; font-size:16px; margin-bottom:5px; }
        .snippet { color:#222; }
        .score { font-size:12px; color:#888; margin-top:8px; }
    </style>
</head>
<body>
    <h2>LLM Reference Match Viewer</h2>
    <form method="POST">
        <label for="raw_info">Paste <tt>raw_info</tt> result from API here:</label><br>
        <textarea name="raw_info" id="raw_info" style="width:98%;height:200px;"><?php echo htmlspecialchars($_POST['raw_info'] ?? ''); ?></textarea><br>
        <button type="submit">Test Parse & Display References</button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>

        <h3>Extracted References</h3>
        <?php if (empty($references)): ?>
            <div style="color:#c00;">No &lt;llm_doc&gt; blocks found.</div>
        <?php else: ?>
            <ol>
            <?php foreach ($references as $ref): ?>
                <li>
                    <b>Score:</b> <?php echo htmlspecialchars($ref['score']); ?> &ndash;
                    <b>Node ID:</b> <?php echo htmlspecialchars($ref['nodeid']); ?> &ndash;
                    <b>Fragment:</b> <?php echo htmlspecialchars($ref['fragment']); ?>
                    <pre style="white-space:pre-wrap"><?php echo htmlspecialchars($ref['content']); ?></pre>
                </li>
            <?php endforeach; ?>
            </ol>
        <?php endif; ?>

        <h3>Reference Cards (Enriched)</h3>
        <?php
        if (!empty($fullItemInfo['info']['records'])):
            foreach ($fullItemInfo['info']['records'] as $item):
                // Breadcrumb demo
                $tree = getTreeData($item['item_id']);
                $breadcrumb = [];
                foreach ($tree as $node) {
                    $breadcrumb[] = htmlspecialchars($node['item_title']);
                }
                // Thumbnail: pick first thumbnail file if available, else placeholder
                $thumbUrl = THUMB_PLACEHOLDER;
                if (!empty($item['files'])) {
                    foreach ($item['files'] as $f) {
                        if (isset($f['file_type']) && $f['file_type'] === 'tn' && !empty($f['file_id'])) {
                            $thumbUrl = '/path/to/thumb/service?id=' . urlencode($f['file_id']); // Replace!
                            break;
                        }
                    }
                }
                // Title/link
                $title = $item['item_title'] ?? ('Record ' . $item['item_id']);
                $itemLink = "item-details.html?q=" . urlencode('folder_id=' . $item['item_parent'] . '&itemId=' . $item['item_id']);
                $snippet = $item['snippet'] ?? '';
                ?>
                <div class="ref-card">
                    <div class="ref-thumb">
                        <a href="<?php echo $itemLink; ?>" target="_blank">
                            <img src="<?php echo $thumbUrl; ?>" alt="Thumbnail">
                        </a>
                    </div>
                    <div class="ref-content">
                        <div class="breadcrumb"><?php echo implode(' / ', $breadcrumb); ?></div>
                        <div class="ref-title">
                            <a href="<?php echo $itemLink; ?>" target="_blank"><?php echo htmlspecialchars($title); ?></a>
                        </div>
                        <div class="snippet">
                            <?php echo htmlspecialchars($snippet); ?>
                        </div>
                        <?php if (isset($item['score'])): ?>
                            <div class="score">Score: <?php echo htmlspecialchars($item['score']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php
            endforeach;
        else:
            if (!empty($references)) {
                echo "<div style='color:#c60;'>No enriched reference records found for these nodeids.</div>";
            }
        endif;
        ?>

    <?php endif; ?>

</body>
</html>
