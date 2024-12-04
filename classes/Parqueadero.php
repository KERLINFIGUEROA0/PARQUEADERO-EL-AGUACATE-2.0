<?php
require_once("conexion.php");

class Parqueadero extends Conexion
{
    private $conexion;

    public function __construct()
    {
        parent::__construct();
        $this->conexion = $this->conect();
    }

    private function EspacioDisponible()
    {
        for ($piso = 1; $piso <= 4; $piso++) {
            $sql = "SELECT posicion FROM Parqueadero WHERE piso = :piso AND horaSalida IS NULL ORDER BY posicion";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':piso', $piso);
            $stmt->execute();
            $ocupados = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($ocupados) < 10) {
                for ($pos = 1; $pos <= 10; $pos++) {
                    if (!in_array($pos, $ocupados)) {
                        return ['piso' => $piso, 'posicion' => $pos];
                    }
                }
            }
        }
        return false;
    }

    public function ingresoVehiculo($placa, $marca, $color, $nombreCliente, $documentoCliente, $horaIngreso, $tipoVehiculo)
    {
        try {
            $vehiculoExistente = $this->buscarVehiculo($placa);
            if ($vehiculoExistente && $vehiculoExistente['horaSalida'] === null) {
                return ['error' => 'El vehículo ya se encuentra en el parqueadero'];
            }

            $espacioLibre = $this->EspacioDisponible();
            if (!$espacioLibre) {
                return ['error' => 'No hay espacios disponibles en el parqueadero'];
            }

            $sql = "INSERT INTO Parqueadero (placa, marca, color, tipoVehiculo, nombreCliente, documentoCliente, horaIngreso, piso, posicion) 
        VALUES (:placa, :marca, :color, :tipoVehiculo, :nombreCliente, :documentoCliente, :horaIngreso, :piso, :posicion)";

            $stmt = $this->conexion->prepare($sql);

            $stmt->execute([
                ':placa' => $placa,
                ':marca' => $marca,
                ':color' => $color,
                ':tipoVehiculo' => $tipoVehiculo,
                ':nombreCliente' => $nombreCliente,
                ':documentoCliente' => $documentoCliente,
                ':horaIngreso' => $horaIngreso,
                ':piso' => $espacioLibre['piso'],
                ':posicion' => $espacioLibre['posicion']
            ]);

            return [
                'success' => true,
                'mensaje' => 'Vehículo registrado exitosamente',
                'piso' => $espacioLibre['piso'],
                'posicion' => $espacioLibre['posicion']
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    public function ingresarSalida($id)
    {
        try {
            $sql = "SELECT * FROM Parqueadero WHERE id = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
            $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$vehiculo) {
                return ['error' => 'Vehículo no encontrado'];
            }

            if (!is_null($vehiculo['horaSalida'])) {
                return ['error' => 'Este vehículo ya ha sido retirado'];
            }

            $horaIngreso = new DateTime($vehiculo['horaIngreso']);
            $horaSalida = new DateTime();
            $intervalo = $horaIngreso->diff($horaSalida);
            $horasEstacionado = ceil($intervalo->h + ($intervalo->days * 24));

            $valorPagar = $horasEstacionado * 2 ;
            $sqlUpdate = "UPDATE Parqueadero 
                          SET horaSalida = :horaSalida, valorPagar = :valorPagar 
                          WHERE id = :id";
            $stmt = $this->conexion->prepare($sqlUpdate);
            $stmt->execute([
                ':horaSalida' => $horaSalida->format('Y-m-d H:i:s'),
                ':valorPagar' => $valorPagar,
                ':id' => $id
            ]);

            $sqlDelete = "DELETE FROM Parqueadero WHERE id = :id";
            $stmt = $this->conexion->prepare($sqlDelete);
            $stmt->execute([':id' => $id]);

            return [
                'success' => true,
                'mensaje' => 'Salida registrada y vehículo eliminado correctamente',
                'valorPagar' => $valorPagar,
                'horasEstacionado' => $horasEstacionado
            ];
        } catch (PDOException $e) {
            return ['error' => 'Error en la base de datos: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['error' => 'Error: ' . $e->getMessage()];
        }
    }

    public function buscarVehiculo($placa)
    {
        try {
            $sql = "SELECT * FROM Parqueadero WHERE placa = :placa ORDER BY id DESC LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':placa' => $placa]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function vehiculosEnParqueadero()
    {
        try {
            $sql = "SELECT * FROM Parqueadero WHERE horaSalida IS NULL ORDER BY piso, posicion";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
