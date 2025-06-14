<nav class="navbar sticky-top navbar-expand-lg bg-dark navbar-dark">
    <div class="container-fluid">
        <div class="d-flex flex-row text-light">
            <h2>Quizzy</h2>
        </div>
        <div class="d-flex flex-row-reverse">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">News</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php
                            if(!isset($_SESSION['email'])) {
                                echo '<i class="bi bi-person-circle"></i> Account';
                            }
                            else {
                                echo '<i class="bi bi-person-circle"></i> ' . $_SESSION['nickname'];
                            }
                            ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg-end">
                            <?php
                            if(!isset($_SESSION['email'])) {
                                echo '<li><a class="dropdown-item" href="login.php">Log in</a></li>';
                            }
                            else {
                                if ($_SESSION['accountType'] == 'user') {
                                    echo '<li><a class="dropdown-item" href="#">Profile</a></li>';
                                    echo '<li><a class="dropdown-item" href="manageAccount.php">Manage Account</a></li>';
                                    echo '<li><a class="dropdown-item" href="logout.php" id="lgout">Log out</a></li>';
                                }
                                else if ($_SESSION['accountType'] == 'admin') {
                                    echo '<li><a class="dropdown-item" href="#">Profile</a></li>';
                                    echo '<li><a class="dropdown-item" href="manageAccount.php">Manage Account</a></li>';
                                    echo '<li><a class="dropdown-item" href="adminPanel.php">Admin Panel</a></li>';
                                    echo '<li><a class="dropdown-item" href="logout.php" id="lgout">Log out</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
