<div class="header">
    <div class="navbar">
        <div class="left">
            <a class="logo" href="#" onclick="clearViewOfModals()" style="cursor:pointer;">tusktimer</a>
        </div>
        <div class="right">
            <?php 
                if (isset($_SESSION['userid']) && $_SESSION['userid'] != -1) {
                    echo '<a href="index.php?logout">Log out</a>';
                    echo '<a href="#" onclick="showSettingsModal()">Settings</a>';
                    echo '<a href="#" onclick="showReportModal()">Report</a>';
                } else {
                    echo '<a href="#" onclick="showLoginModal()">Log in</a>';
                    echo '<a href="#" onclick="showSettingsModal()">Settings</a>';
                }
            ?> 
        </div>
    </div>
</div>