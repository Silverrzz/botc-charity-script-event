<div id="nav-desktop">
    <div class="links">
    </div>
    <div class="title">
        <a class="heading" href="/">Charity Script Event</a>
    </div>
    <div class="account">
        <?php if (isset($_SESSION['user_id'])) { ?>
            <a href="/account" class="account-chip">
                <img class="person-icon" src="/image/avatar/<?php echo $_SESSION['user_id']; ?>" alt="Avatar">
                <div class="text"><?php echo user_nickname($conn, $_SESSION['user_id']); ?></div>
            </a>
        <?php } else { ?>
            <a class="log-in-chip" href="/login">
                <div class="person-icon"><ion-icon name="person-circle"></ion-icon></div>
                <div class="text">Connect</div>
            </div>
        <?php } ?>
    </div>
</div>