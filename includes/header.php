<header>
    <i class="fas fa-gift"></i>
    <h1>Gift-inator</h1>
    <?php if($user !== false): ?>
        <div class="buttons left">
            <button onclick="window.location.href='log-out.php'">
                <i class="fas fa-user"></i>
                <span>Log Out</span>
            </button>
            <button onclick="window.location.href='edit-account.php'">
                <abbr title="settings"><i class="fas fa-cog"></i></abbr>
            </button>
        </div>
    <?php else: ?>
        <div class="buttons left">
            <button onclick="window.location.href='login.php'">
            <i class="fas fa-user"></i>
            <span>Login</span>
            </button>
        </div>
    <?php endif ?>
</header>