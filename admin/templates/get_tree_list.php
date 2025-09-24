<?php

//echo $parent_id;


include('/mnt/storage/StorageOne/stparch/virtual_sites/aib_historicals/config/aib.php');
include("/mnt/storage/StorageOne/stparch/virtual_sites/aib_historicals/include/folder_tree.php");
include("/mnt/storage/StorageOne/stparch/virtual_sites/aib_historicals/include/fields.php");
include('/mnt/storage/StorageOne/stparch/virtual_sites/aib_historicals/include/aib_util.php');
aib_open_db();
$TreeNavInfo = array();
$TreeNavInfo = aib_generate_tree_nav_div($GLOBALS["aib_db"],1522836604,$parent_id,"fetch_tree_children","aib-nav-tree-ul","aib-nav-tree-li","aib-nav-tree-li","aib-nav-tree-li");

//echo '<pre>';
echo $TreeNavInfo['html'];
//aib_close_db();
?>
<input type="text" name="parent_list" id="parent_list" value="">
<br><br><div class='aib-selected-tree-items' id='aib-selected-tree-items'> </div>		
<script type='text/javascript' src='http://develop.archiveinabox.com/jquery-3.2.0.min.js'> </script>
<script type='text/javascript' src='http://develop.archiveinabox.com/js/aib.js'> </script>
<script src="http://develop.archiveinabox.com/js/vendor/jquery.ui.widget.js"></script>
<script type="text/javascript">
<?php
print("
		

		// Global to hold current form

		var CurrentFormID = -1;

		// Global to hold field values

		var UserDefFieldValues = {};

		// Global to hold already-loaded subtrees
	");

	// If there is already nav info, initialize array with data, else empty array.

	if (count(array_keys($TreeNavInfo)) > 0)
	{
		print("
		var CheckedTreeItems = {".$TreeNavInfo["init_item"].":'Y'};
		");
	}
	else
	{
		print("
				var CheckedTreeItems = {};
		");
	}

	if (isset($TreeNavInfo["idlist"]) == true)
	{
		if (count($TreeNavInfo["idlist"]) > 0)
		{
			print("
				var NavLoadedMap = {
			");
			foreach($TreeNavInfo["idlist"] as $ItemID)
			{
				print("
					$ItemID:'Y',
				");
			}

			print("
				};
			");
		}
		else
		{
			print("
			var NavLoadedMap = {};
			");
		}
	}
	else
	{
		print("
		var NavLoadedMap = {};
		");
	}


	print("
		var InitCheckedDisplay = false;");
?>

var InitCheckedDisplay = false;

// TREE NAVIGATION FUNCTIONS
// Global to hold already-loaded subtrees
	
				
		// Fetch children for tree

		function fetch_tree_children(LocalEvent,RefObj,ItemID)
		{
			$(RefObj).css('list-style-image',"url('/images/button-open.png')");
			var QueryParam = {};
			var ChildList;

			LocalEvent.stopPropagation();
			if (NavLoadedMap[ItemID] == undefined)
			{
				NavLoadedMap[ItemID] = 'Y';
				QueryParam['o'] = '63686c';
				QueryParam['i'] = '31';
				QueryParam['pi'] = ItemID;
				aib_ajax_request('http://develop.archiveinabox.com/services/treenav.php',QueryParam,fetch_tree_children_result);
				return;
			}
			var cl = document.getElementById('aib_navlist_childof_' + ItemID);
			ChildList = $('#aib_navlist_childof_' + ItemID);
			if (ChildList !== undefined && cl)
			{
				if (ChildList.css('display') != 'none')
				{
					ChildList.css('display','none');
					$(RefObj).css('list-style-image',"url('/images/button-closed.png')");
				}
				else
				{
					ChildList.css('display','block');
					$(RefObj).css('list-style-image',"url('/images/button-open.png')");
				}
			}

		}

		// Set checkbox for tree item, preventing bubble-up

		function set_tree_checkbox(LocalEvent,RefObj)
		{
			var ElementID;

			LocalEvent.stopPropagation();
			ElementID = $(RefObj).attr('id');
			ElementID = ElementID.replace('aib_item_checkbox_','',ElementID);
			if ($(RefObj).is(':checked') == true)
			{
				$(RefObj).prop('checked',true);
				CheckedTreeItems[ElementID] = 'Y';
			}
			else
			{
				$(RefObj).prop('checked',false);
				CheckedTreeItems[ElementID] = 'N';
			}

			
			show_checked_tree_items();
		}

		// Callback for tree children fetch

		function fetch_tree_children_result(InData)
		{
			var ElementID;
			var ItemID;

			if (InData['status'] != 'OK')
			{
				alert('ERROR PROCESSING CHILD REQUEST: ' + InData['info']['msg']);
				return;
			}

			ItemID = InData['info']['item_id'];
			ElementID = 'aib_navlist_entry_' + ItemID;
			$('#' + ElementID).append(InData['info']['html']);
			show_checked_tree_items();
		}

		// Show a list of all checked tree items using AJAX to retrieve HTML from
		// a back-end HTML generator.

		function show_checked_tree_items()
		{
			var CheckedItemsList;
			var Size;
			var Counter;
			var IDValue;
			var QueryParam = {};
			var IDList = [];
			var Key;

			// Get a list of all checked items

			for (Key in CheckedTreeItems)
			{
				if (CheckedTreeItems[Key] == 'Y')
				{
					IDList.push(Key);
				}
			}


			// Generate an unsorted list in display area to show items

			QueryParam['idlist'] = IDList.join(',');
			QueryParam['o'] = '67736c';
			QueryParam['i'] = '31';
			aib_ajax_request('http://develop.archiveinabox.com/services/treenav.php',QueryParam,show_selected_tree_items);
			return;
		}

		function show_selected_tree_items(InData)
		{
			if (InData['status'] != 'OK')
			{
				$('#aibselectedtreeitems').html("ERROR: Can't get list");
				return;
			}

			$('#aib-selected-tree-items').html(InData['info']['html']);
			post_process_form();
		}

		// Copy the selected items array to input form

	
		function post_process_form()
		{
			var IDList = [];
			var Key;

			// Get a list of all checked items

			for (Key in CheckedTreeItems)
			{
				if (CheckedTreeItems[Key] == 'Y')
				{
					IDList.push(Key);
				}
			}

			$('#parent_list').val(IDList.join(','));
			return(true);
		}
		

		// If the checked display area hasn't been initialized, do so here

		if (InitCheckedDisplay == false)
		{
			InitCheckedDisplay = true;
			show_checked_tree_items();
		}


		</script>
