<!-- admin/user_management/main_section/user_table.php -->
<?php
$stmt = $conn->query("
    SELECT 
        u.userId,
        u.userName,
        u.email,
        u.level,
        u.greenPoints,
        u.isBanned,

        COUNT(q.submissionId) AS totalSub,
        SUM(q.approveStatus = 'Completed') AS approvedSub,
        SUM(q.approveStatus = 'Rejected') AS rejectedSub

    FROM users u
    LEFT JOIN questSubmissions q 
        ON q.submittedByUserId = u.userId

    GROUP BY u.userId
");
$users = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!-------------------------------------------------------------------------------------------- HTML -->
<div class="user-management user">
    <!-- TOP normal or moderator selector -->
    <div class="top-type-option">
        <a href="?module=user&page=user"
            class="type-option-btn active">
            Normal User
        </a>

        <a href="?module=user&page=mod"
            class="type-option-btn">
            Moderator
        </a>
    </div>

    <!-- Create Delete Option (Use Js to control) -->
    <div class="create-delete-option">
        <button id="addBtn" class="add-btn">
            Add New +
        </button>

        <button id="deleteBtn" class="del-btn">
            Delete -
        </button>
    </div>

    <!-- Table Record -->
    <div class="management-table">
        <div class="table-wrapper">
            <div class="record-table">
                <table>
                    <thead>
                        <tr>
                            <th>All<input type="checkbox" id="selectAll" class="check-all"></th>
                            <th>Username</th>
                            <th>Email</th>
                            <th class="phone-dissable" >Level</th>
                            <th class="phone-dissable" >Green Points</th>
                            <th class="phone-dissable" >Status</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>

                                <tr
                                    data-id="<?= $u['userId'] ?>"
                                    data-username="<?= strtolower($u['userName']) ?>"
                                    data-level="<?= $u['level'] ?>"
                                    data-points="<?= $u['greenPoints'] ?>"
                                    data-banned="<?= $u['isBanned'] ?>"
                                    data-total="<?= $u['totalSub'] ?>"
                                    data-approved="<?= $u['approvedSub'] ?>"
                                    data-rejected="<?= $u['rejectedSub'] ?>"
                                >

                                <td><input type="checkbox" class="row-check"></td>
                                <td><?= htmlspecialchars($u['userName']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td class="phone-dissable" ><?= $u['level'] ?></td>
                                <td class="phone-dissable" ><?= $u['greenPoints'] ?></td>
                                <td class="phone-dissable"><?= $u['isBanned'] ? 'Banned' : 'Normal' ?></td>
                                <td>
                                    <button class="edit-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="m5 16l-1 4l4-1L19.586 7.414a2 2 0 0 0 0-2.828l-.172-.172a2 2 0 0 0-2.828 0zM15 6l3 3m-5 11h8"/></svg>
                                    </button>
                                </td>

                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>