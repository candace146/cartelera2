<?php
session_start();

// Database connection
define('DATABASE_HOST', '127.0.0.1');
define('DATABASE_USER', 'root');
define('DATABASE_PASSWD', '');
define('DATABASE_NAME', 'cartelera');

$conn = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWD, DATABASE_NAME);
if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}

// Initialize variables
$is_admin = isset($_SESSION['admin_id']) && $_SESSION['admin_id'];
$congregacion = $_SESSION['congregacion'] ?? null;
$cong = $_GET['congregacion'] ?? null;



// Handle congregation setting
if (isset($_POST['congregacion']) || isset($_GET['congregacion'])) {
    $_SESSION['congregacion'] = $_POST['congregacion'] ?? $_GET['congregacion'];
    $congregacion = $_SESSION['congregacion'];
}

// Set the SQL table based on the congregation
$sqltable = ($congregacion == 'andes') ? 'anuncios_andes' : 'anuncios_liniers';

// Init to create admin user if needed
$init = (isset($_GET['init']) && $_GET['init'] == 'True') ? true : null;

// Default credentials variables
$default_credentials = [
    "username" => "admin",
    "password" => "password123"
];

// Create admin user if init is set
if ($init) {
    createAdminUser($conn, $default_credentials);
}
if ($is_admin && $_SERVER['REQUEST_URI'] == '/'){
    header('Location: /admin');
}
// Create new users if addnewusers is set
if (isset($_POST['create-new-user'])) {
    $newuser = $_POST['newusername'];
    $newpass = $_POST['newpassword'];
    $newaccountcong = $_POST['new-account-congregacion'];
    $rights = $_POST['rights'];
    if ($rights == "lectura"){
        $rights = "1";
    } elseif ($rights == "lectura-escritura"){
        $rights = "2";
    } elseif ($rights == "all"){
        $rights = "0";
    }


    createNewUser($newuser, $newpass, $newaccountcong, $rights);
}

// Redirect admins users to admin page if login  is set
if ($is_admin && $_SERVER['REQUEST_URI'] == '/login'){
    header('Location: /admin');
    exit;
}

// Redirect non-admin users
if (!$is_admin && $_SERVER['REQUEST_URI'] == '/admin') {
    header('Location: /login');
    exit;
}


// Handle login
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    authenticate($username, $password);
}

// Handle new event creation
if (isset($_POST['create-event'])) {
    $name = $_POST['eventname'];
    $tema = $_POST['tema'];
    $date = $_POST['eventdate'] ?? '';
    $color = $_POST['color'];
    $owner = $_SESSION['username'];
    $congregacion = $_SESSION['congregacion'];
    $randomId = rand();
    if (isset($_FILES['eventimage'])) {
        $imageFolder = "images/" . $congregacion . "/";
        $imageFile = $imageFolder . basename($_FILES['eventimage']['name']); // Name por .pdf
        
        // Subir el archivo PDF
        move_uploaded_file($_FILES["eventimage"]["tmp_name"], $imageFile);
        if (str_contains($_FILES['eventimage']['type'], "pdf")){
            $pdfinfo = convertToPdf($imageFile, $imageFolder);
            $imageFile = $pdfinfo["imageFile"];
            $siblingsCount = $pdfinfo["siblingsCount"];
            $siblingsPath = $pdfinfo["siblingsPath"];
            $hasSiblings = $pdfinfo["hasSiblings"];
        } else {
            $imageFile = $imageFile;
            $siblingsCount = "0";
            $siblingsPath = "";
            $hasSiblings = "0";
        }
        
        
        // Insertar en la base de datos
        $query = "INSERT INTO $sqltable (`nombre`, `path`, `congregacion`, `tema`, `fecha`, `color`, `dueño`, `siblings`, `siblingsCount`, `siblingsPath`, `id`) 
                  VALUES ('$name', '$imageFile', '$congregacion', '$tema', '$date', '$color', '$owner', '$hasSiblings', '$siblingsCount', '$siblingsPath', '$randomId')";
        
        if (mysqli_query($conn, $query)) {
            echo "<div class='bg-green-400 text-white p-4 rounded shadow-md mb-4 font-semibold w-48 text-center mx-auto mt-4'>Evento creado exitosamente</div>";
        } else {
            echo "<div class='bg-red-400 text-white p-4 rounded shadow-md mb-4 font-semibold w-48 text-center mx-auto mt-4'>Error: " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "No se ha subido ninguna imagen.";
    }
}


// Handle edit event
if ($is_admin && str_contains($_SERVER['REQUEST_URI'], '/admin/edit-event')) {
    if (empty($_GET['id'])) {
        header("Location: /admin");
        exit;
    }
    
    $id = $_GET['id'];
    $previousEventDetails = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM $sqltable WHERE `id` = '$id' AND `congregacion` = '$congregacion'"));
    if ($is_admin && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit-event'])) {
        $nuevoNombre = !empty($_POST['new-name']) ? $_POST['new-name'] : $previousEventDetails['nombre'];
        $nuevaFecha = !empty($_POST['new-eventdate']) ? $_POST['new-eventdate'] : $previousEventDetails['fecha'];
        $nuevoTema = !empty($_POST['new-tema']) ? $_POST['new-tema'] : $previousEventDetails['tema'];
        $newColor = !empty($_POST['new-color']) ? $_POST['new-color'] : $previousEventDetails['color'];
        $randomInt = $previousEventDetails['id'];
        $newOwner = $_SESSION['username'];
        $newSiblings = $previousEventDetails['siblings'];
        $newSiblingsCount = $previousEventDetails['siblingsCount'];
        $newSiblingsPath = $previousEventDetails['siblingsPath'];
        $imageFile = '';

        if (isset($_FILES['new-eventimage']) && $_FILES['new-eventimage']['tmp_name'] != '') {
            $imageFolder = "images/" . $congregacion . "/";
            $imageFile = $imageFolder . basename($_FILES['new-eventimage']['name']); // Ruta final del archivo, con .pdf o .png segun corresponda.
            // Subir el archivo PDF
            move_uploaded_file($_FILES["new-eventimage"]["tmp_name"], $imageFile);
            if (str_contains($_FILES['new-eventimage']['type'], "pdf")){
                $pdfinfo = convertToPdf($imageFile, $imageFolder);
                $imageFile = $pdfinfo["imageFile"];
                $newSiblingsCount = $pdfinfo["siblingsCount"];
                $newSiblingsPath = $pdfinfo["siblingsPath"];
                $newSiblings = $pdfinfo["hasSiblings"];
            } else {
                $newSiblings = "0"; 
                $newSiblingsCount = "0";
                $newSiblingsPath = "";
            }
        } else {
            $imageFile = $previousEventDetails['path']; //  Si no hay imagen no movemos nada y dejamos todo como esta.
        }

        $query = "UPDATE $sqltable SET `nombre` = '$nuevoNombre', `congregacion` = '$congregacion', `tema` = '$nuevoTema', `fecha` = '$nuevaFecha', `color` = '$newColor', `dueño` = '$newOwner', `siblings` = '$newSiblings', `siblingsCount` = '$newSiblingsCount', `siblingsPath` = '$newSiblingsPath'";
        if ($imageFile) {
            $query .= ", `path` = '$imageFile'";
        }
        echo $nuevoNombre, $id;
        $query .= " WHERE `id` = '$randomInt' AND `congregacion` = '$congregacion'";

        if (mysqli_query($conn, $query)) {
            header('Location: /admin');
            exit;
        } else {
            $update_evento_error = "Error al actualizar el evento: " . mysqli_error($conn);
        }
    }
}

// Handle delete event
if ($is_admin && str_contains($_SERVER['REQUEST_URI'], '/admin/delete-event')) {
    if (empty($_GET['id'])) {
        header("Location: /admin");
        exit;
    }
    $id = $_GET['id'];
    if (isset($_POST['delete-event'])) {
        $query = "DELETE FROM $sqltable WHERE `id` = '$id' AND `congregacion` = '$congregacion'";
        if (mysqli_query($conn, $query)) {
            header("Location: /admin");
            exit;
        } else {
            $delete_event_error = "Error al eliminar el evento: " . mysqli_error($conn);
        }
    }
}

if ($is_admin && $_SESSION['hasRights'] == "0" &&  str_contains($_SERVER['REQUEST_URI'], '/admin/delete-user')) {
    $name = $_GET['id'];
    if (isset($_POST['delete-user'])){
        $query = "DELETE FROM `usuarios` WHERE `name` = '$name' AND  `congregacion` = '$congregacion'";
        if (mysqli_query($conn, $query)){
            header("Location: /admin");
            exit;
        } else {
            $delete_event_error = "Error al eliminar el anuncio: " . mysqli_error($conn);
        }

    }
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: /");
    exit();
}

// Functions


function convertToPdf($imageFile, $imageFolder) {
    // Eliminar la extensión .pdf para generar los archivos PNG
    $imageFileInfo = pathinfo($imageFile);
    $imageFileWithoutPDF = $imageFileInfo['dirname'] . "/" . $imageFileInfo['filename'];
    
    // Comando para convertir PDF a PNG
    $passToPDFConvert = "pdftoppm -r 150 -png " . escapeshellarg($imageFile) . " " . escapeshellarg($imageFileWithoutPDF);
    
    // Ejecutar la conversión
    shell_exec($passToPDFConvert);
    
    // Buscar todos los archivos PNG generados
    $pattern = $imageFileInfo['filename'] . "-*.png"; // Usamos -N para buscar todos los PNGs generados
    $directory = $imageFolder . $pattern;
    $siblings = glob($directory);
    
    if (count($siblings) > 0) {
        $hasSiblings = "1"; // Segunda imagen PNG generada, ya que la primera siempre es la misma
        $siblingsCount = count($siblings); // Contamos cuántos archivos PNG se generaron
        $siblingsPath = $siblings[1];  // Usamos la ruta del SEGUNDO archivo generado, es la que se mostrara a la derecha al tocar en una imagen que tenga siblings

        // Retornar los valores necesarios
        $imageFile = $imageFileInfo['dirname']. '/' . $imageFileInfo['filename'] . '-1.png';
        return [
            'hasSiblings' => $hasSiblings,
            'imageFile' => $imageFile,
            'siblingsCount' => $siblingsCount,
            'siblingsPath' => $siblingsPath
        ];
    } else {
        echo "<div class='bg-red-400 text-white p-4 rounded shadow-md mb-4 font-semibold w-48 text-center mx-auto mt-4'>Error: No se generaron imágenes.</div>";
        exit;  // Terminamos el script si no se generaron imágenes
    }
}
// Create admin user if init is true
function createAdminUser($conn, $default_credentials) {
    $sqlquery = "INSERT INTO usuarios (name, passwd, rights) VALUES ('$default_credentials[username]', '$default_credentials[password]', 'admin')";
    if (mysqli_query($conn, $sqlquery)) {
        echo "<div class='bg-green-400 text-white p-4 rounded shadow-md mb-4 font-semibold w-48 text-center mx-auto mt-4'>Usuario administrador creado exitosamente.</div>";
    } else {
        echo "<div class='bg-red-400 text-white p-4 rounded shadow-md mb-4 font-semibold w-48 text-center mx-auto mt-4'>Error: " . mysqli_error($conn) . "</div>";
    }
}

// Authenticate admin input to admin in db
function authenticate($username, $password) {
    global $conn;
    $sqlquery = "SELECT * FROM usuarios WHERE `name` = '$username' AND `passwd` = '$password'";
    $result = mysqli_query($conn, $sqlquery);
    $admin = mysqli_fetch_assoc($result);
    if ($admin['passwd'] === $password && $admin['name'] === $username) {
        $_SESSION['congregacion'] = $admin['congregacion'];
        $_SESSION["admin_id"] = "1";
        $_SESSION['username'] = $admin['name'];
        $_SESSION['hasRights'] = $admin['rights'];
        header("Location: /admin");
        exit;
    } else {
        echo "<div class='bg-red-400 text-white p-4 rounded shadow-md mb-4 font-semibold w-48 text-center mx-auto mt-4'>Credenciales inválidas</div>";
        $_SESSION["admin_id"] = "";
    }
}

// Get events extended
function getEventsEx($congregacion) {
    global $sqltable, $conn;
    $congregacionSql = mysqli_real_escape_string($conn, $congregacion);
    $query = "SELECT * FROM $sqltable WHERE `congregacion` = '$congregacionSql';";
    $result = mysqli_query($conn, $query);
    $events = [];
    while ($event = mysqli_fetch_assoc($result)) {
        $events[] = $event;
    }
    return $events;
}

function createNewUser($newuser, $newpass, $newaccountcong, $rights){
    global $conn;
    $query = "INSERT INTO `usuarios` (`name`, `passwd`, `congregacion`, `rights`) VALUES ('$newuser', '$newpass', '$newaccountcong', '$rights');";
    $result = mysqli_query($conn, $query);
    if ($result) {
        echo "<div class='bg-green-400 text-white p-4 rounded shadow-md mb-4 font-semibold w-48 text-center mx-auto mt-4'>Usuario creado exitosamente.</div>";
    } else {
        echo "<div class='bg-red-400 text-white p-4 rounded shadow-md mb-4 font-semibold w-48 text-center mx-auto mt-4'>Error: " . mysqli_error($conn) . "</div>";
    }
}

function fetchSiblingsImages($event) {
        global $sqltable;
        global $conn;
        // Prepara la consulta SQL para obtener las rutas de las imágenes para el evento dado
        $sql = "SELECT `siblingsPath` FROM $sqltable WHERE `nombre` = '$event'"; 
        $result = mysqli_query($conn, $sql);
    
        // Verifica si se obtuvo algún resultado
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Se separan las rutas de las imágenes usando explode(';')
                $imagePaths = explode(';', $row['siblingsPath']);
            }
        }
        // Devuelve las rutas de las imágenes relacionadas
        return $imagePaths;
    }
    
?>

<!-- HTML Structure for tailwind css -->
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $cong ? 'Cartelera' : 'Selecciona una congregación'; ?></title>
</head>
<body class="bg-gray-100">


    <!-- Login Form -->
    <?php if (!$is_admin && $_SERVER['REQUEST_URI'] == '/login'): ?>
        <div class="container mx-auto p-4">
            <a href="/" class="bg-indigo-400 hover:bg-indigo-500 rounded py-2 px-4 mt-4 mb-4 text-white">Volver a la pagina principal</a>
            <section class="py-12">   
                <div class="container mx-auto">
                    <h2 class="text-2xl font-bold mb-4">Ingresar</h2>
                    <form method="POST" action="/login">
                        <div class="mb-4">
                            <label for="username" class="block mb-2">Nombre de usuario</label>
                            <input type="text" id="username" name="username" required class="w-full p-2 border rounded">
                        </div>
                        <div class="mb-4">
                            <label for="password" class="block mb-2">Contraseña</label>
                            <input type="text" id="password" name="password" required class="w-full p-2 border rounded">
                        </div>
                        <button type="submit" name="login" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded">
                            Ingresar
                        </button>
                    </form>
                </div>
            </section>
        </div>

    <!-- Admin Panel -->
    <?php elseif ( $is_admin && $_SERVER['REQUEST_URI'] == '/admin'): ?>
        <div class="container mx-auto p-4">
            <section class="py-12 ">
                <div class="container mx-auto ">
                    <h2 class="text-2xl font-bold mb-4">Bienvenido <?php $username = $_SESSION['username']; echo $username; ?> al panel de administracion.</h2>
                    <p class="mb-4">Desde aqui puedes subir o borrar anuncios.</p>
                    <hr class="border-t-3 border-gray-300">
                    <br>
                    
                    <a href="/admin/new-event" class="bg-green-500 hover:bg-green-600 rounded py-2 px-4 mt-4 text-white">Agregar anuncio</a>
                    <a class="bg-green-500 hover:bg-green-600 rounded py-2 px-4 mt-4 text-white" href="/?congregacion=<?php echo $congregacion; ?>" id="go-to-congregation">Ir a la cartelera de tu congregación</a> <!-- Preview de la cartelera-->
                    <script>
                        document.getElementById('go-to-congregation').addEventListener('click', function(e) {
                            e.preventDefault();  // Prevenir el comportamiento por defecto del enlace

                            // Usar localStorage para almacenar temporalmente el Referer
                            localStorage.setItem('referer', window.location.pathname);

                            // Redirigir a la cartelera de la congregación
                            const congregacion = '<?php echo $congregacion; ?>'; // Esto permite variar de cartelera dependiendo del usuario.
                            window.location.href = '/?congregacion=' + congregacion; // Esto genera la URL a la que se va a acceder.
                        });
                    </script>
                </div>
                

                <div class="mb-4"></div> <!-- Espaciador -->

                <table class="w-full bg-white shadow-md rounded-tl-lg rounded-tr-lg rounded-bl-lg rounded-br-lg mb-6">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left rounded-tl-lg">Nombre</th>
                            <th class="py-3 px-6 text-center">Tema</th>
                            <th class="py-3 px-6 text-center">Subido por</th>
                            <th class="py-3 px-6 text-center rounded-tr-lg">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $congregacion = $_SESSION['congregacion'];
                        $result = mysqli_query($conn, "SELECT * FROM $sqltable WHERE congregacion = '$congregacion'");
                        while ($events = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-4 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($events['nombre']); ?></td> <!-- Columna de nombre -->
                                <td class="py-3 px-6 text-center"><?php echo htmlspecialchars($events['tema']); ?></td> <!-- Tema -->
                                <td class="py-3 px-6 text-center"><?php echo htmlspecialchars($events['dueño']); ?></td> <!-- Quien la subio -->
                                <td class="py-3 px-6 text-center rounded-br-lg">
                                    <a href="/admin/edit-event?id=<?php echo $events['id']?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded inline-block mr-2">Editar</a>
                                    <form method="POST" action="/admin/delete-event?id=<?php echo $events['id']; ?>" class="inline-block">
                                        <button type="submit" name="delete-event" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded" onclick="return confirm('¿Está seguro de que desea eliminar este anuncio?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                
                <?php if ($is_admin && $_SESSION['hasRights'] == "0"){
                    echo "<a class='bg-green-500 hover:bg-green-600 rounded py-2 px-4 mt-4 text-white' href='/admin/add-new-users'> Crear, agregar nuevos usuarios. </a>";
                    echo "<a class='ml-4 bg-green-500 hover:bg-green-600 rounded py-2 px-4 mt-4 text-white' href='/admin/users-panel'> Administrar usuarios.</a>";
                } 
                ?>
                
                <form method="POST" action="/logout">
                    <button type="submit" name="logout" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded inline-block mt-4">Cerrar sesion</button>
                </form>

            </section>
        </div>

    <!-- Create new user-->
    <?php elseif($is_admin && $_SERVER['REQUEST_URI'] == '/admin/add-new-users'): ?>
        <div class="container mx-auto p-4">
            <a href="/admin" class="bg-green-400 hover:bg-green-500 rounded py-2 px-2 mt-2 mb-2 text-white">Volver a la pagina anterior</a>
            <section class="py-10">
                <hr class="border-t-3 border-gray-300">
                <br>
                <div class="container mx-auto ">
                    <h2 class="text-2xl font-bold mb-4">Agregar usuarios</h2>
                    <p class="mb-4">Desde aqui agregar usuarios.</p>
                </div>
                <form method="POST" action="/admin/add-new-users">
                        <div class="mb-4">
                            <label for="username" class="block mb-2">Nombre de usuario de la nueva cuenta</label>
                            <input type="text" id="username" name="newusername" required class="w-full p-2 border rounded">
                        </div>
                        <div class="mb-4">
                            <label for="username" class="block mb-2">Contraseña de la nueva cuenta</label>
                            <input type="text" id="username" name="newpassword" required class="w-full p-2 border rounded">
                        </div>
                        <div class="mb-4">
                            <label for="username" class="block mb-2">¿A que congregacion pertenecera la cuenta?</label>
                            <select class="w-full p-2 border rounded" name="new-account-congregacion" id="congregacion">
                                <option value="andes"> Los Andes </option>
                                <option value="liniers"> Liniers </option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="rights" class="block mb-2">¿Que permisos tiene la cuenta?</label>
                            <select name="rights" id="rights" class="w-full p-2 border rounded">
                                <option value="lectura"> Solamente puede ver los anuncios subidos. </option>
                                <option value="lectura-escritura"> Puede ver y modificar anuncios subidos. </option>
                                <option value="all"> Puede leer, modificar, agregar y sacar usuarios. </option>
                            </select>
                        </div>
                    <button type="submit" name="create-new-user"class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded"> Crear nueva cuenta</button>
                </form>
            </section>
        </div>

    <!-- Modify User panel -->
    <?php elseif($is_admin && $_SESSION['hasRights'] == "0" && $_SERVER['REQUEST_URI'] == '/admin/edit-user'): ?>
        <div class="container mx-auto p-4">
            <a href="/admin" class="bg-green-400 hover:bg-green-500 rounded py-2 px-2 mt-2 mb-2 text-white">Volver a la pagina anterior</a>
            <section class="py-10">
                <hr class="border-t-3 border-gray-300">
                <br>
                <div class="container mx-auto ">
                    <h2 class="text-2xl font-bold mb-4">Modificar usuario</h2>
                    <p class="mb-4">Desde aqui puedes modificar usuarios. Si no hace falta modificar algo, solo deja en blanco el espacio.</p>

                    <p class="mb-4">Estas modificando el usuario:  <?php echo $_GET['id']; ?></p>

                </div>
                <form method="POST" action="/admin/add-new-users">
                        <div class="mb-4">
                            <label for="username" class="block mb-2">Nuevo nombre de usuario de la nueva cuenta</label>
                            <input type="text" id="username" name="newusername" required class="w-full p-2 border rounded">
                        </div>
                        <div class="mb-4">
                            <label for="username" class="block mb-2">Nueva contraseña de la nueva cuenta</label>
                            <input type="text" id="username" name="newpassword" required class="w-full p-2 border rounded">
                        </div>s
                        <div class="mb-4">
                            <label for="username" class="block mb-2">¿A que congregacion pertenecera la cuenta?</label>
                            <select class="w-full p-2 border rounded" name="new-account-congregacion" id="congregacion">
                                <option value="andes"> Los Andes </option>
                                <option value="liniers"> Liniers </option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="rights" class="block mb-2">¿Que nuevos permisos tiene la cuenta?</label>
                            <select name="rights" id="rights" class="w-full p-2 border rounded">
                                <option value="lectura"> Solamente puede ver los anuncios subidos. </option>
                                <option value="lectura-escritura"> Puede ver y modificar anuncios subidos. </option>
                                <option value="all"> Puede leer, modificar, agregar y sacar usuarios. </option>
                            </select>
                        </div>
                    <button type="submit" name="create-new-user"class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded"> Crear nueva cuenta</button>
                </form>
            </section>
        </div>
    <!-- Users administrator panel -->
    <?php elseif ($is_admin && $_SESSION['hasRights'] == "0" && $_SERVER['REQUEST_URI'] == '/admin/users-panel'): ?>
        <div class="container mx-auto p-4">
            <a href="/admin" class="bg-green-400 hover:bg-green-500 rounded py-2 px-2 mt-2 mb-2 text-white">Volver a la pagina anterior</a>
            <section class="py-10">
                <hr class="border-t-3 border-gray-300">
                <br>
                <div class="border-t-3 border-gray-300">
                    <h2 class="text-2xl font-bold mb-4">Panel de administrador </h2>
                    <p class="mb-4">Desde aqui puedes ver y editar los usuarios.</p>
                </div>
                <table class="w-full bg-white shadow-md rounded mb-6 gap-5">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal ">
                            <th class="py-3 px-6 text-left">Usuario</th>
                            <th class="py-3 px-6 text-center">Contraseña</th>
                            <th class="py-3 px-6 text-center">Congregacion</th>
                            <th class="py-3 px-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Modify -->
                        <?php
                        $congregacion = $_SESSION['congregacion'];
                        $result = mysqli_query($conn, "SELECT * FROM `usuarios` WHERE `congregacion` = '$congregacion' ");
                        while ($users = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-4 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($users['name']); ?></td>
                                <td class="py-3 px-6 text-center"><?php echo htmlspecialchars($users['passwd']); ?></td>
                                <td class="py-3 px-6 text-center"><?php echo htmlspecialchars($users['congregacion']); ?></td>
                                <td class="py-3 px-6 text-center">
                                    <a href="/admin/edit-user?id=<?php echo $users['name']?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded inline-block mr-2">Editar</a>
                                    <form method="POST" action="/admin/delete-user?id=<?php echo $users['name']; ?>" class="inline-block">
                                        <button type="submit" name="delete-user" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded" onclick="return confirm('¿Está seguro de que desea eliminar este anuncio?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <!-- Modify -->
                    </tbody>
                </table>
            </section>
        </div>
    <!-- Edit event form -->

    <?php elseif($is_admin && str_contains($_SERVER['REQUEST_URI'], '/admin/edit-event')): ?>
        <div class="container mx-auto p-4">
            <a href="/admin" class="bg-green-400 hover:bg-green-500 rounded py-2 px-2 mt-2 mb-2 text-white">Volver a la pagina anterior</a>
            <section class="py-10">
                <hr class="border-t-3 border-gray-300">
                <br>
                <div class="container mx-auto">
                    <h2 class="text-2xl font-bold mb-4">Editar anuncio</h2>
                    <p class="mb-4"> Desde aqui puedes editar un anuncio. Si no hace falta editar algun campo dejalo en blanco.</p>
                </div>
                <?php 
                    $congregacion = $_SESSION['congregacion'];
                    $result = mysqli_query($conn, "SELECT * FROM $sqltable WHERE congregacion = '$congregacion'");
                    $events = mysqli_fetch_assoc($result);
                ?>
                <div class="container mx-auto">
                    <p class="mb-4"> <b> Estas modificando el anuncio con ID nro: <?php echo $_GET['id'];?>  </b> </p>
                </div>
                <form method="POST" action="/admin/edit-event?id=<?php echo $_GET['id']; ?>" enctype="multipart/form-data">
                    <div class="mb-4">
                            <label for="name" class="block mb-2"> Nuevo nombre del anuncio</label>
                            <input type="text" id="name" name="new-name" class="w-full p-2 border rounded">
                    </div>
                    <div class="mb-4">
                        <label for="name" class="block mb-2"> Nuevo tema del anuncio</label>
                        <select name="new-tema" id="tema">
                            <option value="salidas">Salidas de predicación</option>
                            <option value="acomodadores_microfonistas">Acomodadores y microfonistas</option>
                            <option value="audio_video_plataforma">Audio, video y plataforma</option>
                            <option value="grupos">Grupos para el servicio</option>
                            <option value="reuniones_entre_semana">Reunión de entre semana (Especificar fecha)</option>
                            <option value="reuniones_fin_semana">Reunión de fin de semana</option>
                        </select>
                    </div>
                    <div class="mb-4">
                            <label for="date" class="block mb-2">Nueva fecha del programa de reunión (Formato: Año/Mes/Día)</label>
                            <input type="text" id="date" name="new-eventdate" class="w-full p-2 border rounded">
                    </div>
                    <div class="mb-4">
                        <label for="image" class="block mb-2"> Nueva imagen (PNG o JPG)</label>
                        <input type="file" id="image" name="new-eventimage" class="w-full p-2 border rounded">
                    </div>
                    <div class="mb-4">
                            <label for="cong" class="block mb-2">Nuevo color del cuadro del anuncio</label>
                            <input type="color" name="new-color" id="color" class="p-2 border rounded">
                    </div>
                    <button type="submit" name="edit-event" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Modificar anuncio</button>
                </form>
            </section>
        </div>
    <!-- New Event Form -->
    <?php elseif ($is_admin && $_SERVER['REQUEST_URI'] == '/admin/new-event'): ?>
        <div class="container mx-auto p-4">
            <a href="/admin" class="bg-green-400 hover:bg-green-500 rounded py-2 px-2 mt-2 mb-2 text-white">Volver a la pagina anterior</a>
            <section class="py-12">
                <hr class="border-t-3 border-gray-300">
                <br>
                <div class="container mx-auto ">
                    <h2 class="text-2xl font-bold mb-4">Agregar anuncio</h2>
                    <form method="POST" action="/admin/new-event" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="name" class="block mb-2">Nombre del anuncio</label>
                            <input type="text" id="name" name="eventname" required class="w-full p-2 border rounded">
                        </div>
                        <div class="mb-4">
                            <label for="tema" class="block mb-2">Tema del anuncio</label>
                            <select class="w-full p-2 border rounded" name="tema" id="tema">
                                <option value="salidas">Salidas de predicación</option>
                                <option value="acomodadores_microfonistas">Acomodadores y microfonistas</option>
                                <option value="audio_video_plataforma">Audio, video y plataforma</option>
                                <option value="grupos">Grupos para el servicio</option>
                                <option value="reuniones_entre_semana">Reunión de entre semana (Especificar fecha)</option>
                                <option value="reuniones_fin_semana">Reunión de fin de semana</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="date" class="block mb-2">Fecha del programa de reunión (Formato: Año/Mes/Día)</label>
                            <input type="text" id="date" name="eventdate" class="w-full p-2 border rounded">
                        </div>
                        <div class="mb-4">
                            <label for="image" class="block mb-2">Imagen (PNG o JPG)</label>
                            <input type="file" id="image" name="eventimage" class="w-full p-2 border rounded">
                        </div>
                        <div class="mb-4">
                            <label for="cong" class="block mb-2">Color del cuadro del anuncio</label>
                            <input type="color" name="color" id="color" class="p-2 border rounded">
                        </div>
                        <button type="submit" name="create-event" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Añadir anuncio</button>
                    </form>
                </div>
            </section>
        </div>
    <!-- Select Congregation -->
    <?php elseif (!$cong): ?>
        <div class="relative min-h-screen bg-cover bg-center max-w-100vh" style="background-image: url('fondo.jpg');">
            <!-- Filtro oscuro para que el contenido sea legible (opcional) -->
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>

            <div class="container mx-auto p-4 relative z-10">
                <h1 class="text-5xl font-bold text-center text-white mb-8">Cartelera de anuncios</h1>
                
                <!-- Contenedor con fondo blanco y opacidad media -->
                <div class="bg-white bg-opacity-80 p-8 rounded-lg shadow-lg max-w-md mx-auto">
                    <h2 class="text-2xl font-semibold text-center text-black mb-4">Selecciona una congregación</h2>
                    
                    <form action="index.php" class="flex flex-col items-center mt-4">
                        <select name="congregacion" id="congregacion-select" class="border rounded p-2 mb-4 w-full">
                            <option value="andes">Plaza los Andes</option>
                            <option value="liniers">Liniers</option>
                        </select>
                        <button class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 w-full" type="submit">Entrar a la cartelera</button>
                        
                        <?php if (!$is_admin): ?>
                            <a class="bg-green-500 text-white py-2 px-4 rounded mt-4 hover:bg-green-600 w-full text-center" href="/login">Iniciar sesión</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

   <!-- Display Events -->
    <?php elseif ($cong): ?>
        <div class="justify-center w-full p-8">
            <!-- Botón de "Regresar" con el href dinámico -->
            <a name='back' id='back-button' href='/' class='bg-green-400 hover:bg-green-500 rounded py-2 px-2 mt-2 mb-2 text-white'>Regresar</a>
           
            
            
            <h1 class="text-3xl font-bold text-center mb-4 pt-4">
                    Cartelera de la congregación <?php echo ($cong == "andes") ? " Plaza los Andes" : "Liniers"; ?>
                </h1>
            <hr class="border-t-4 border-gray-300">

            <main class="py-12">
                <!-- Div con estilo en línea para anular las clases container y mx-auto -->
                <div class="p-7">
                    <!-- Grid responsivo, ajustando el número de columnas según el tamaño de pantalla -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php 
                            // Obtener los eventos de la congregación
                            $events = getEventsEx($_SESSION['congregacion'], $sqltable); 
                        ?>
                        <?php foreach ($events as $event): 
                            // Obtener las imágenes hermanas para el evento
                            $siblingsImages = fetchSiblingsImages($event['nombre']);
                            // Codificar el array de imágenes hermanas en formato JSON
                            $siblingsImagesJson = json_encode($siblingsImages);
                        ?>
                            <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer w-full">
                                <!-- Imagen principal del evento -->
                                <img src="<?php echo $event['path']; ?>"  
                                    alt="<?php echo htmlspecialchars($event['nombre']); ?> Image" 
                                    class="event-image w-full h-40 object-cover" 
                                    data-siblings-images='<?php echo $siblingsImagesJson; ?>' 
                                    onclick="openPopup('<?php echo addslashes($event['path']); ?>', this)">

                                <!-- Información del evento -->
                                <div class="p-4" style="background-color: <?php echo $event['color']; ?>">
                                    <h2 class="text-lg font-bold mb-2"><?php echo htmlspecialchars($event['nombre']); ?></h2>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    <?php endif; ?>
    <div class="popup-image fixed inset-0 bg-black bg-opacity-80 flex justify-center items-center z-50 hidden">
        <!-- Botón de cierre -->
        <span class="close-popup absolute top-5 right-6 text-white text-4xl cursor-pointer">&times;</span>
        
        <!-- Contenedor de las imágenes -->
        <div class="popup-content w-full xl:w-auto 2xl:w-auto justify-content items-center flex flex-col  xl:flex-row 2xl:flex-row h-auto max-h-full overflow-auto ">
    
            <!-- Imagen principal del popup -->
            <img src="" alt="Imagen del Popup" 
                class="popup-img w-auto min-h-50vh md:min-h-75vh lg:min-h-75vh xl:min-h-90vh 2xl:min-h-90vh sm:max-w-md md:max-w-lg lg:max-w-3xl xl:max-w-4xl object-contain p-4" 
                style=" max-width: 95%; max-height: 100vh; height: auto; width: auto;" />
            
            <!-- Imagen hermana del popup, inicialmente oculta -->
            <img src="" alt="Imagen popup hermana" 
                class="popup-img-siblings w-auto min-h-50vh md:min-h-75vh lg:min-h-75vh xl:min-h-90vh 2xl:min-h-90vh  sm:max-w-md md:max-w-lg lg:max-w-3xl xl:max-w-4xl object-contain p-4 hidden " 
                style="max-width: 95%; max-height: 100vh; height: auto; width: auto; " />
        </div>
    </div>
















    <!-- Scripting for image popup-->
    <script>
    let timeoutId;
    
    function openPopup(imageSrc, imgElement) {
        const popupImage = document.querySelector('.popup-image');
        const popupImgTag = document.querySelector('.popup-img');
        const popupSiblingImgTag = document.querySelector('.popup-img-siblings');
        
        // Mostrar el pop-up y establecer la imagen principal
        popupImage.classList.remove('hidden');
        popupImgTag.src = imageSrc;
        popupImgTag.style.objectPosition = 'left top'; // Ajuste de la posición de la imagen
        
        // Obtener las imágenes hermanas desde el atributo `data-siblings-images` del elemento
        const siblingsPath = JSON.parse(imgElement.getAttribute('data-siblings-images'));
        console.log(siblingsPath);
        // Verificar si hay imágenes hermanas y mostrarlas
        if (siblingsPath != "") {
            popupSiblingImgTag.src = siblingsPath[0]; // Asignar la primera imagen hermana
            popupSiblingImgTag.classList.remove('hidden'); // Mostrar la imagen hermana
        } else {
            popupSiblingImgTag.classList.add('hidden'); // Ocultar la imagen hermana si no hay
        }
        
        // Reiniciar el temporizador de inactividad
        clearTimeout(timeoutId);
        timeoutId = setTimeout(returnToGallery, 60000); // 1 minuto de inactividad
    }
    // Función para cerrar el pop-up
    document.querySelector('.close-popup').addEventListener('click', function() {
        document.querySelector('.popup-image').classList.add('hidden');
        clearTimeout(timeoutId); // Limpiar el temporizador al cerrar el pop-up
    });

    document.addEventListener('click', resetTimeout); 

    function resetTimeout() {
        clearTimeout(timeoutId); 
        timeoutId = setTimeout(returnToGallery, 120000); // Reinicia después de 2 minutos de inactividad
    }

    function returnToGallery() {
        document.querySelector('.popup-image').classList.add('hidden'); 
    }
    </script>

   

</body>
</html>
