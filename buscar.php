<?php
require_once("classes/autoload.php");
$parqueadero = new Parqueadero();
$mensaje = '';
$vehiculo = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['buscar'])) {
        $vehiculo = $parqueadero->buscarVehiculo($_POST['placa']);
        if(!$vehiculo) {
            $mensaje = '<div class="alert alert-warning">Vehículo no encontrado</div>';
        }
    } elseif(isset($_POST['retirar']) && isset($_POST['id'])) {
        $resultado = $parqueadero->ingresarSalida($_POST['id']);
        if(isset($resultado['error'])) {
            $mensaje = '<div class="alert alert-danger">' . $resultado['error'] . '</div>';
        } else {
            $mensaje = '<div class="alert alert-success">Vehículo retirado. Valor a pagar: $' . 
                       $resultado['valorPagar'] . ' USD ('. $resultado['horasEstacionado'] . ' horas)</div>';
        }
    }
}

$vehiculosActivos = $parqueadero->vehiculosEnParqueadero();


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parqueadero "El Aguacatal" - Búsqueda</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/buscar.css" rel="stylesheet">
    <script src="js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Parqueadero El Aguacate</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Registro</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="buscar.php">Buscar/Retirar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    
    <div class="container mt-5">
        <h2 class="text-center mb-4">Buscar/Retirar Vehículo</h2>
        <?php echo $mensaje; ?>

        <!-- Formulario de búsqueda -->
        <form action="" method="POST" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="placa" placeholder="Ingrese la placa" required>
                <button type="submit" name="buscar" class="btn btn-primary">Buscar</button>
            </div>
        </form>

        <!-- Resultado de búsqueda -->
        <?php if($vehiculo && $vehiculo['horaSalida'] === null): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Vehículo Encontrado</h5>
                <p>Placa: <?php echo $vehiculo['placa']; ?></p>
                <p>Marca: <?php echo $vehiculo['marca']; ?></p>
                <p>Color: <?php echo $vehiculo['color']; ?></p>
                <p>Cliente: <?php echo $vehiculo['nombreCliente']; ?></p>
                <p>Ubicación: Piso <?php echo $vehiculo['piso']; ?>, Posición <?php echo $vehiculo['posicion']; ?></p>
                <p>Ingreso: <?php echo $vehiculo['horaIngreso']; ?></p>
                <form action="" method="POST">
                    <input type="hidden" name="id" value="<?php echo $vehiculo['id']; ?>">
                    <button type="submit" name="retirar" class="btn btn-danger">Retirar Vehículo</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <div class="container">
        <h1 class="mt-4">Estado del Parqueadero</h1>
        <?php for ($piso = 1; $piso <= 4; $piso++): ?>
    <h3 class="mt-3">Piso <?php echo $piso; ?></h3>
    <div class="grid-container">
        <?php for ($posicion = 1; $posicion <= 10; $posicion++): ?>
            <?php 
                // Verificar si el espacio está ocupado
                $ocupado = false;
                $placa = '';
                foreach ($vehiculosActivos as $vehiculoActivo) {
                    if ($vehiculoActivo['piso'] == $piso && $vehiculoActivo['posicion'] == $posicion) {
                        $ocupado = true;
                        $placa = $vehiculoActivo['placa']; // Obtener la placa del vehículo
                        break;
                    }
                }
            ?>
            <div class="grid-item <?php echo $ocupado ? 'ocupado' : 'disponible'; ?>">
                <?php echo $ocupado ? 'Ocupado - ' . $placa : 'Disponible'; ?>
            </div>
        <?php endfor; ?>
    </div>
    <?php endfor; ?>
</body>
</html>