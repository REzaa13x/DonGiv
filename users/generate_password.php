<?php
if (isset($_POST['password'])) {
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Password: " . htmlspecialchars($password) . "<br>";
    echo "Hash: <br><textarea cols='100' rows='2'>" . $hash . "</textarea>";
} else {
?>
    <form method="post">
        <label>Masukkan Password:</label><br>
        <input type="text" name="password" required>
        <button type="submit">Generate Hash</button>
    </form>
<?php
}
?>
