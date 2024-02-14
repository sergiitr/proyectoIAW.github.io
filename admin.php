<?php 
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <title>Proyecto</title>
        <link rel="stylesheet" href="styles.css">
        <link rel="shortcut icon" href="./imagenes/logo.jpeg"/>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    </head>
    <body>
    <div id="psup" class="container-fluid mt-2">
            <table id="tablaSecciones">
                <tr class="align-middle">
                    <td class="tdDatos">
                        <p class="principal"><a class="enlacePaginaActual" href="./index.php">PAGINA PRINCIPAL</a></p>
                    </td>
                    <td class="tdDatos">
                        <p class="sobreNos"><a class="enlacePaginaActual" href="./nosotros.php">SOBRE NOSOTROS</a></p>
                    </td>
                    <?php
                        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true) {
                            // Verificar si el usuario no es root
                            if ($_SESSION["usuario"] != "admin") {
                                echo '
                                    <td class="tdDatos">
                                        <select aria-label="Default select example" onchange="redirectPage(this.value)">
                                            <option selected disabled>SELECCIONE CARRITO</option>
                                            <option value="carrito">CARRITO VENTA</option>
                                            <option value="alquiler">CARRITO ALQUILER</option>
                                        </select>
                                    </td>
                                ';
                            }
                            echo '
                                <td class="tdDatos">
                                    <div class="user-info">
                                        <p class="username">¡Hola, ',$_SESSION["usuario"],'!</p>';
                            // Verificar si el usuario es administrador
                            if ($_SESSION["usuario"] == "admin") {
                                echo '
                                    <select aria-label="Default select example" onchange="redirectPage2(this.value)">
                                        <option selected disabled>Seleccione una opción</option>
                                        <option value="admin">Administrar</option>
                                        <option value="cerrarSesion">Cerrar sesión</option>
                                    </select>
                                    <a id="logoutLink" class="logout-link" style="display: none;" onclick="cerrarSesion()">Cerrar sesión</a>';
                                    
                            } else {
                                echo '
                                    <select aria-label="Default select example" onchange="redirectPage2(this.value)">
                                        <option selected disabled>Seleccione una opción</option>
                                        <option value="pedidos">Mis pedidos</option>
                                        <option value="cerrarSesion">Cerrar sesión</option>
                                        <option value="borrarUsuario">Borrar Usuario</option>
                                    </select>
                                    <a id="logoutLink" class="logout-link" style="display: none;" onclick="cerrarSesion()">Cerrar sesión</a>';
                            }
                            echo '
                                    </div>
                                </td>
                            ';
                        }else {
                            echo '
                                <td class="tdDatos">
                                    <p class="sobreNos"><a class="enlacePaginaActual" href="./crearUsuario.php">Crear Usuario</a></p>
                                </td>
                                <td class="tdDatos">
                                    <p class="carrito"><a class="enlacesPaginas" href="./formInicioSesion.php">Inicio Sesion</a></p>
                                </td>
                            ';
                        }
                    ?>
                </tr>
            </table>
        </div>

        <script>
            var logoutLink = document.getElementById("logout-link");
            function redirectPage(value) {
                if (value === "carrito")
                    window.location.href = "./carrito.php";
                else if (value === "alquiler")
                    window.location.href = "./alquiler.php";
                
            }
            function redirectPage2(value) {
                if (value === "pedidos") {
                    window.location.href = "./cliente.php";
                } else if (value === "cerrarSesion") {
                    console.log("Cerrando sesión...");
                    logoutLink.style.display = "block";
                    cerrarSesion();
                }  else if (value === "borrarUsuario") {
                    // Confirmar antes de borrar
                    var confirmar = confirm("¿Está seguro de que desea borrar su usuario? Esta acción no se puede deshacer.");
                    if (confirmar) {
                        window.location.href = "./borrarUsuario.php";
                    }
                } else if (value == "admin")
                    window.location.href = "./admin.php";
            }

            function cerrarSesion() {
                window.location.href = './cerrarSesion.php';
            }
        </script>
        <script>
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true) { ?>
                // Si el usuario ha iniciado sesión
                var logoutLink = document.getElementById('logoutLink');

                logoutLink.addEventListener('click', function () {
                    // Redirige a la página de cerrar sesión
                    window.location.href = './cerrarSesion.php';
                });
            <?php } ?>
        </script>
        <?php
            require_once "login.php";

            $conexion = mysqli_connect($host, $user, $pass, $database);
            if (!$conexion) {
                die("Error de conexión: " . mysqli_connect_error());
            }

            // Verificar si el usuario es administrador
            if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"] !== "admin") {
                echo "Acceso denegado.";
                exit; // O redirigir al usuario a otra página.
            }

            // Verificar si se envió el formulario de baja
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["usuarios_baja"])) {
                foreach ($_POST["usuarios_baja"] as $idUsuario) {
                    // Asegúrate de no eliminar el usuario administrador por accidente
                    if ($idUsuario == "admin")
                        continue; // No eliminar el usuario administrador
                    
                    // Eliminar el usuario y sus registros relacionados
                    $queries = [
                        "DELETE FROM detalle_pedido WHERE idPed IN (SELECT idPed FROM compran WHERE idUsuario = '$idUsuario')",
                        "DELETE FROM compran WHERE idUsuario = '$idUsuario'",
                        "DELETE FROM usuarios WHERE idUsuario = '$idUsuario'"
                    ];
                    
                    foreach ($queries as $query) {
                        if (mysqli_query($conexion, $query)) 
                            echo "Registros eliminados correctamente. <br>";
                        else
                            echo "Error al eliminar registros: " . mysqli_error($conexion);
                    }
                }
                // Refrescar la página para mostrar el estado actualizado
                header("Refresh:0");
            }

            $query = "SELECT idUsuario, nombre FROM usuarios";
            $resultado = mysqli_query($conexion, $query);
        ?>
        <div class="container mt-4">
            <h2>Administración de Usuarios</h2>
            <form method="POST" action="">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Seleccionar</th>
                            <th>ID Usuario</th>
                            <th>Nombre</th>
                            <th>Administrador</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($fila = mysqli_fetch_assoc($resultado)):
                            $esAdmin = $fila["idUsuario"] === "admin"; // Verificar si el usuario es administrador
                        ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="usuarios_baja[]" value="<?php echo $fila["idUsuario"]; ?>" <?php if ($esAdmin) echo 'disabled'; ?>>
                                </td>
                                <td><?php echo $fila["idUsuario"]; ?></td>
                                <td><?php echo $fila["nombre"]; ?></td>
                                <td><?php echo $esAdmin ? 'Sí' : 'No'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-danger">Dar de baja a seleccionados</button>
            </form>
        </div>
    </body>
</html>