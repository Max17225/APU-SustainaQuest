<!-- admin/user_management/main_section/moderator_table.php -->
<?php
$stmt = $conn->query("
    SELECT  
        moderatorId,
        modName,
        email,
        phoneNumber
    FROM moderators
    ORDER BY moderatorId DESC;
    ");
$mods = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!-------------------------------------------------------------------------------------------- HTML -->
<div class="user-management moderator">
    <!-- TOP daily weekly selector -->
    <div class="top-type-option">
        <a href="?module=user&page=user"
            class="type-option-btn">
            Normal User
        </a>

        <a href="?module=user&page=mod"
            class="type-option-btn active">
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

    <!-- Table -->
    <div class="management-table">
        <div class="table-wrapper">
            <div class="record-table">
                <table>
                    <thead>
                        <tr>
                            <th>All <input type="checkbox" id="selectAll" class="check-all"></th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Edit</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($mods as $m): ?>
                            <tr
                                data-id="<?= $m['moderatorId'] ?>"
                                data-username="<?= strtolower($m['modName']) ?>"
                            >
                                <td>
                                    <input type="checkbox" class="row-check">
                                </td>

                                <td><?= htmlspecialchars($m['modName']) ?></td>
                                <td><?= htmlspecialchars($m['email']) ?></td>
                                <td><?= htmlspecialchars($m['phoneNumber']) ?></td>

                                <td>
                                    <button class="edit-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24">
                                            <path fill="none" stroke="currentColor"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.4"
                                                d="m5 16l-1 4l4-1L19.586 7.414
                                                a2 2 0 0 0 0-2.828l-.172-.172
                                                a2 2 0 0 0-2.828 0zM15 6l3 3
                                                m-5 11h8"/>
                                        </svg>
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