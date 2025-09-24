<?php
require_once dirname(__FILE__) . '/config/config.php';
include_once COMMON_TEMPLATE_PATH . 'header.php';
?>
<div class="header_img bgBlue_header">
    <div class="bannerImage bannerImage_society "></div>
    <div class="clientLogo"><img id="client_logo" style="width:200px;" src="" /></div>
</div>
<div class="clearfix"></div>
<div class="content2" style="min-height: 400px;">
    <div class="container">
        <div class="row marginTop20">
            <div class="container-fluid">
                <div class="row mb-3">
                    <div class="col-md-3">
                    </div>
                    <div class="col-md-9 marginTop13">
                            <input type="hidden" id="llm_request_id">
                            <div class="searchBGNone">
                                <h3><strong>Chatbot Query: Is president kennedy visited china lake?</strong></h3>
                                <h3 style="font-size: 18px; margin: 18px 0 0 0;"><strong>Chatbot Response:</strong></h3>
                            </div>
                            <div id="llm-stream-container" style="user-select:text;-webkit-user-select:text;-moz-user-select:text;-ms-user-select:text;">
                                <div id="llm-stream-content" style="user-select:text;-webkit-user-select:text;-moz-user-select:text;-ms-user-select:text;"></div>
                                <button id="cancel-llm-query" type="button" class="btn btn-primary btn-sm pull-right" style="line-height: normal;padding: inherit;margin-top:5px;">Cancel Query</button>
                            </div>
                            <!-- Debug area (hidden in production) -->
                            <!-- <pre id="llm-debug-response" style="max-width: 800px; margin: 10px auto; padding: 10px; background: #eee; font-size: 12px; overflow-x: auto; border: 1px dashed #ccc;"></pre> -->
                    </div>
                </div>
            
                <!-- <h2>Popup Demo</h2>
                <button id="openPopupBtn" class="btn btn-primary">Open Popup</button> -->
            </div>
        </div>
    </div>
</div>
    <!-- Popup Modal -->
    <div class="modal fade" id="popupModal" tabindex="-1" role="dialog" aria-labelledby="popupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document"> <!-- Use modal-lg for more space -->
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="popupModalLabel">Popup Title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Image and button will go here -->
            </div>
            <div class="modal-footer" id="popupModalFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to handle the popup
        document.getElementById('openPopupBtn').addEventListener('click', function() {
            $('#popupModal').modal('show');
        });
    </script>

<?php
include_once COMMON_TEMPLATE_PATH . 'footer_new.php';
?>
<script>
    /**
     * Function: triggerLlmStreamRequest
     * Purpose:  Polls backend for LLM stream response, displays content incrementally
     *           with typing animation and inline loader
     */
    function triggerLlmStreamRequest(llmRequestId, chat_request) {
        // ====== Cached DOM elements ======
        const container = $("#llm-stream-content"); // where we display the text
        const scrollArea = $("#llm-stream-container"); // scroll container for auto-scroll
        const loaderHtml = '<span class="inline-loader"><span></span><span></span><span></span></span>'; // blinking dots loader

        // ====== Internal tracking ======
        let lastShownText = "";        // text that has already been rendered to user
        let typingInProgress = false;  // prevents overlapping character animations
        let llmPollingInterval = null; // setInterval reference to stop later
        let isCancelled = false;

        // ===============================================
        // Renders the partial text with inline loader
        // Called when status = WORKING
        // ===============================================
        function renderPartial(text, isWorking) {
            container.html(
                '<div class="llm-main-text">' +
                text.replace(/\n/g, '<br>') +
                (isWorking ? loaderHtml : '') +
                '</div>'
            );

            // Auto-scroll so latest text remains visible
            scrollArea.scrollTop(scrollArea[0].scrollHeight);
        }

        // ===============================================
        // Renders the fully completed text + references
        // Called when status = DONE
        // ===============================================
        function renderFinal(text, referencesHtml) {
            let html = '<div class="llm-main-text">' +
                text.replace(/\n/g, '<br>') +
                '</div>';

            // Append references if provided
            if (referencesHtml) {
                html += '<div class="llm-doc-section"><h4>Chatbot Response References</h4>' + referencesHtml + '</div>';
            }

            container.html(html);
            scrollArea.scrollTop(scrollArea[0].scrollHeight);
        }

        // ===============================================
        // Typing animation - appends new text chunk character by character
        // Calls renderPartial() on each frame to refresh the display
        // ===============================================
        function typeText(newText, isWorking, callback) {
            if (typingInProgress) return; // block if a typing animation is already running
            typingInProgress = true;
            let i = 0;

            function addChar() {
                // Append 1 more character from the new text
                lastShownText += newText.charAt(i);

                // Render with loader if still in WORKING
                renderPartial(lastShownText, isWorking);

                i++;
                if (i < newText.length) {
                    setTimeout(addChar, 20); // speed: ms per char
                } else {
                    typingInProgress = false;
                    if (callback) callback(); // run callback when done typing
                }
            }

            addChar();
        }

        // ====== Initial State: Only loader visible until text arrives ======
        container.html(loaderHtml);

        $('#cancel-llm-query').off('click').on('click', function () {
            if (isCancelled) return;
            isCancelled = true;
            clearInterval(llmPollingInterval);
            $('#cancel-llm-query').hide();
            container.html('<div class="alert alert-danger" style="padding:8px 10px;font-size:16px;">Query cancelled by user.</div>');
        });
        // ===============================================
        // Polling function
        // Called every few seconds to get latest partial output from backend
        // ===============================================
        function pollLlm() {
            if (isCancelled) return;
            var archive_id= "<?php echo $_REQUEST['archive_id']; ?>";
            $.ajax({
                url: "services.php",
                type: "post",
                dataType: "json",
                data: {
                    mode: "get_llm_stream_poll",
                    llm_request_id: llmRequestId,
                    archive_id: archive_id,
                    chat_request: chat_request,
                    demo: 1
                },
                success: function (streamResponse) {
                    if (isCancelled) return;
                    // Extract the raw info text from API (either in api_response.info or info)
                    let infoRaw = "";
                    if (streamResponse.api_response && streamResponse.api_response.info) {
                        infoRaw = streamResponse.api_response.info;
                    } else if (streamResponse.info) {
                        infoRaw = streamResponse.info;
                    }

                    if (streamResponse.status === "WAITING") {
                        // Keep showing loader until we get first non-empty text
                        container.html(loaderHtml);
                    }

                    else if (streamResponse.status === "WORKING") {
                        // Get <llm_text> partial (may not have closing tag yet)
                        const match = infoRaw.match(/<llm_text>([\s\S]*?)(?:<\/llm_text>|$)/);
                        const currentPartial = match ? match[1].trim() : "";

                        // If we have something new, type it out
                        if (currentPartial && currentPartial !== lastShownText && !typingInProgress) {
                            if (currentPartial.startsWith(lastShownText)) {
                                // Only new part gets typed out
                                const newSegment = currentPartial.slice(lastShownText.length);
                                typeText(newSegment, true);
                            } else {
                                // If mismatch (reset case), type whole thing
                                lastShownText = "";
                                typeText(currentPartial, true);
                            }
                        }
                    } 
                    else if (streamResponse.status === "DONE") {
                        // Stop polling immediately
                        clearInterval(llmPollingInterval);

                        // Safety: decode HTML entities first in case they're escaped
                        let decodedInfo = $("<textarea/>").html(
                            streamResponse.api_response && streamResponse.api_response.info
                                ? streamResponse.api_response.info
                                : (streamResponse.info || "")
                        ).text();

                        // Extract final <llm_text>
                        const textMatch = decodedInfo.match(/<llm_text>([\s\S]*?)<\/llm_text>/i);
                        let finalText = textMatch ? textMatch[1].trim() : "";

                        // If fail to parse, fallback to whatever was last shown
                        if (!finalText && lastShownText) {
                            finalText = lastShownText;
                        }

                        // Extract all <llm_doc> blocks for references
                        const docRegex = /<llm_doc>([\s\S]*?)<\/llm_doc>/gi;
                        let docsHtml = "", docMatch;
                      //  while ((docMatch = docRegex.exec(decodedInfo)) !== null) {
                            /*const docBlock = docMatch[1];
                            const scoreMatch = docBlock.match(/<score>(.*?)<\/score>/i);
                            const contentMatch = docBlock.match(/<content>([\s\S]*?)<\/content>/i);
                            docsHtml += '<div class="llm-doc">';
                            if (scoreMatch) {
                                docsHtml += '<div class="llm-doc-score">Score: ' + scoreMatch[1].trim() + '</div>';
                            }
                            if (contentMatch) {
                                docsHtml += '<div class="llm-doc-content">' +
                                    contentMatch[1].trim().replace(/\n/g, '<br>') +
                                    '</div>';
                            }
                            docsHtml += '</div>';*/
							
							docsHtml +=streamResponse.finalHtmlNew;
                       // }

                        // Always render immediately to remove loader
                        renderFinal(finalText, docsHtml);

                        // If there is untyped new text, animate it, but still keep final after completion
                        if (finalText.length > lastShownText.length) {
                            typeText(finalText.slice(lastShownText.length), false, function () {
                                renderFinal(finalText, docsHtml);
                            });
                        }

                        // Update lastShownText so next comparisons are correct
                        lastShownText = finalText;
                    }

                    else if (streamResponse.api_response && streamResponse.api_response.status === "ERROR") {
                        const errorMessage = streamResponse.api_response.info || "An error occurred.";
                        container.html(`<span style="color:red;">${errorMessage}</span>`);
                        clearInterval(llmPollingInterval);
                    }
                },
                error: function () {
                    if (isCancelled) return;
                    container.html("<span style='color:red;'>AJAX request failed.</span>");
                    clearInterval(llmPollingInterval);
                }
            });
        }

        // ===============================================
        // Start polling every 2 seconds
        // ===============================================
        llmPollingInterval = setInterval(pollLlm, 2000);
        pollLlm(); // Immediate first call
    }

    triggerLlmStreamRequest(1, 'Is president kennedy visited china lake?')

    $(document).on('click', '.search_result_clicked', function(e) {
        var img = $(this).find('img');
        var detailsUrl = $(this).attr('href');
        if (img.length) {
            e.preventDefault(); 

            $('#popupModalLabel').text('Image Preview');

            $('#popupModal .modal-body').html(
                '<img src="' + img.attr('src') + '" style="max-width:100%;height:auto;display:block;margin-bottom:12px;" />' +
                '<button type="button" id="openRecordDetails" class="btn btn-info" style="margin-bottom:12px;">Open Record Details</button>' +
                '<div id="recordDetailsContainer"></div>'
            );

            $('#openRecordDetails').off('click').on('click', function(){
                // Hide the image (clear modal body except button container)
                $('#popupModal .modal-body > img').remove();

                // Load record details view in iframe inside container below button
                $('#recordDetailsContainer').html(
                    '<iframe src="' + detailsUrl + '" style="width:100%;height:500px;border:1px solid #ccc;"></iframe>'
                );
            });

            $('#popupModal').modal('show');
        }
    });



</script>