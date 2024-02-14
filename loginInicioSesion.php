<?php 
    session_start();
    require_once "./login.php";
    $conexion = mysqli_connect($host, $user, $pass, $database);

    if ($conexion->connect_error) {
        die("Conexión fallida: " . $conexion->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $usuario = mysqli_real_escape_string($conexion, $_POST["usuario"]);
        $contrasena = $_POST["contrasena"];
        $sql = "SELECT * FROM usuarios WHERE idusuario='$usuario'";
        $result = $conexion->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($contrasena, $row["contrasena"])) {
                $_SESSION["usuario"] = $usuario;
                $_SESSION['user_logged_in'] = true;
                header("Location: index.php");
            } else {
                // Almacenar mensaje de error en la sesión y redirigir
                $_SESSION['error_login'] = "Contraseña equivocada";
                header("Location: formInicioSesion.php");
            }
        } else {
            $_SESSION['error_login'] = "Usuario no encontrado";
            header("Location: formInicioSesion.php");
        }
    }
?>