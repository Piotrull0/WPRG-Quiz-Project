 <nav class="custom-sidebar p-3">
        <h4 class="mb-4">Categories</h4>
        <ul class="nav flex-column">
            <?php
            global $res;
            while ($row = $res->fetch_assoc()) {
                $catId = $row['id'];
                $catName = $row['name'];
                echo '<li class="nav-item">
                <a class="nav-link text-light" href="?quizType=' . $catId . '">' . $catName . '</a>
                </li>';
            }
            ?>
        </ul>
 </nav>