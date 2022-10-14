<nav>
    <h2>Menu</h2>
    <a href="index.php">Home</a>
    <?php if($user !== false)
    { ?>
        <div> 
            <a href="view-all-lists.php">My Wish Lists</a>
            <button onclick="window.location.href='create-list.php'">
                <abbr title="Create New List"><i class="fas fa-plus"></i></abbr>
            </button>
        </div>
        <?php require_once './includes/library.php';
        $pdo = connectDB();
        $query = "SELECT listId, title FROM `GiftinatorLists` WHERE userId = ?";
        $titles = $pdo->prepare($query);
        $titles->execute([$user]);
        if ($titles !== false)
        {
            $titles = $titles->fetchALL(PDO::FETCH_KEY_PAIR);
            foreach ($titles as $key=>$value)
            {?>
                <a href="view-list.php?id=<?php echo $key; ?>"><?php echo $value; ?></a>
            <?php }
        }
    }
    else
    { ?>
        <a href="create-account.php">Create Account</a>
        <a href="login.php">Login</a>
    <?php } ?>
</nav>