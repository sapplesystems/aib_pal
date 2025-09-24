<?php if (!empty($items)): ?>
    <?php $sn = 1; ?>
    <?php foreach ($items as $item): ?>
        <tr data-id="<?= $item['item_id'] ?>">
            <td class="text-center"><?= $sn; ?></td>
            <td class="title-cell" width="60%">
                <span class="view-title"><?= htmlspecialchars($item['item_title']) ?></span>
                <input type="text" class="edit-title d-none form-control" value="<?= htmlspecialchars($item['item_title']) ?>">
            </td>
            <td width="40%">
                <button class="btn-edit btn btn-info btn-sm">Edit</button>
                <button class="btn-save d-none btn btn-info btn-sm">Save</button>
                <button class="btn-cancel d-none btn btn-warning btn-sm">Cancel</button>
            </td>
        </tr>
    <?php $sn++; endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="3" class="text-center text-danger">No Record Found!</td>
    </tr>
<?php endif; ?>
