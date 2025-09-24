<?php



function parseChinaLakeResponse($raw) {
    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return ["error" => "Invalid input JSON: " . json_last_error_msg()];
    }

    $info = $data['info'] ?? '';
    $query = $data['query_text'] ?? '';

    $docs = [];
    if (preg_match_all('/<llm_doc>(.*?)<\/llm_doc>/s', $info, $matches)) {
        foreach ($matches[1] as $block) {
            $doc = [];

            if (preg_match('/<score>(.*?)<\/score>/', $block, $m)) {
                $doc['score'] = (float)$m[1];
            }
            if (preg_match('/<content>(.*?)<\/content>/s', $block, $m)) {
                $content = trim($m[1]);
                $doc['content_raw'] = $content;

                $structured = [
                    "uri" => null,
                    "title" => null,
                    "cdate" => null,
                    "archive_group_title" => null,
                    "archive_title" => null,
                    "item_id" => null,
                    "item_path" => null,
                    "File" => null,
                    "ocr_text" => null
                ];

				// Extract @fields
				if (preg_match_all('/@(\w+)=([^\n]+)/', $content, $pairs, PREG_SET_ORDER)) {
					foreach ($pairs as $p) {
						$key = strtolower($p[1]);
						$val = trim($p[2], " \t\n\r;");
						$structured[$key] = $val;
					}
				}

				// Also capture plain key=value lines (like item_path=...)
				if (preg_match_all('/\b(\w+)=([^\n]+)/', $content, $pairs2, PREG_SET_ORDER)) {
					foreach ($pairs2 as $p) {
						$key = strtolower($p[1]);
						$val = trim($p[2], " \t\n\r;");
						if (!isset($structured[$key]) || $structured[$key] === null) {
							$structured[$key] = $val;
						}
					}
				}

                // Extract File line
                if (preg_match('/File:(.*)/', $content, $fm)) {
                    $structured["File"] = trim($fm[0]);
                }

                // Extract OCR text
                if (preg_match('/OCR Text:(.*)/s', $content, $om)) {
                    $structured["ocr_text"] = trim($om[1]);
                }

                $doc['structured'] = $structured;
            }
            if (preg_match('/<metadata>(.*?)<\/metadata>/s', $block, $m)) {
                $meta = trim($m[1]);
                $meta = preg_replace("/'/", '"', $meta);
                $decodedMeta = json_decode($meta, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $doc['metadata'] = $decodedMeta;
                } else {
                    $doc['metadata_raw'] = $meta;
                }
            }
            $docs[] = $doc;
        }
    }

    $output= [
        "status" => $data['status'] ?? null,
        "query_text" => $query,
        "documents" => $docs
    ];
	
	 return json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

// ---- MAIN ----
$inputJson=trim(file_get_contents('/mnt/data/stparch/virtual_sites/www.archiveinabox.com/get_llm_stream_poll_rawResult_141755708753.txt'));

//$inputJson = file_get_contents("test.txt");
$result = parseChinaLakeResponse($inputJson);

$rawResultParse=json_decode($result) ;

echo '<pre>';print_r($rawResultParse);echo '</pre>';
	if(isset($rawResultParse->documents)){
				 foreach ($rawResultParse->documents as $doc) {
					 
					 if($doc->structured->ocr_text){
						 file_put_contents('/mnt/data/stparch/virtual_sites/www.archiveinabox.com/get_llm_stream_poll_rawResultParse_'.rand(1,100).time().'.txt',print_R($rawResultParse,true));
						   $finalHtml .= '<div class="llm-doc">
						    <div class="col-md-1></div>
						   <div class="col-md-2 text-center">
                                <a class="organizations search_result_clicked" search_item_id="878660" href="item-details.html?q=ZUxBV0JaZmFJYTFsS3dPbjF5Ylo4NW90WVlhVGVMZE13bElDQ010Qmxicmh3dEhYcUFuMjgyQjhVU0xzUGZMK0NqV3dUN0Jqc3pOdDdvdklJTW04MHc9PQ==">
                                    <img class="searchResultImg" src="http://aibpaldev.archiveinabox.com:53080/get_thumb.php?id='.$doc->structured->uri.'" alt="">
                                </a>
                            </div>
						   </div><div class="col-md-9 text-center">';
							if ($doc->structured->title !== '') {
								$finalHtml .= '<div class="llm-doc-score">Score: ' . htmlspecialchars($doc->structured->title) . '</div>';
							}
						 if ($doc->score !== '') {
								$finalHtml .= '<div class="llm-doc-score">Score: ' . htmlspecialchars($doc->score) . '</div>';
							}
							if ($doc->structured->score !== '') {


								$finalHtml .= '<div class="llm-doc-content">' . $doc->structured->ocr_text . '</div>';
							}
							$finalHtml .= '</div></div>';
					 }
					 
				 }
				}
echo $finalHtml;;
//echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

