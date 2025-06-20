<?php
// ====================================
// CONTROLADOR PARA LA ENTIDAD "ROL"
// ====================================

include_once 'C:\xampp\htdocs\inventario\Model\Rol\RolModel.php';

class RolController
{
    private $model;

    // ====================================
    // CONSTRUCTOR
    // ====================================
    public function __construct()
    {
        $this->model = new RolModel();
    }

    // ====================================
    // MOSTRAR FORMULARIO DE REGISTRO
    // ====================================
    public function getInsert()
    {
        $estados = $this->model->obtenerEstados();
        require_once 'C:\xampp\htdocs\inventario\Views\Rol\insert.php';
    }

    // ====================================
    // GUARDAR UN NUEVO ROL
    // ====================================
    public function postInsert()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['rol_nombre'] ?? '';
            $estado_id = $_POST['estado_id'] ?? '';

            if ($nombre !== '' && $estado_id !== '') {
                $resultado = $this->model->insertarRol($nombre, $estado_id);
                if ($resultado) {
                    header('Location: index.php?modulo=rol&controlador=rol&funcion=consult');
                    exit();
                } else {
                    echo "Error al insertar el rol.";
                }
            } else {
                echo "Todos los campos son obligatorios.";
            }
        }
    }

    // ====================================
    // CONSULTAR TODOS LOS ROLES
    // ====================================
    public function consult()
    {
        $resultado = $this->model->consultarRoles();

        $roles = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $roles[] = $fila;
            }
        }

        require_once 'C:\xampp\htdocs\inventario\Views\Rol\consult.php';
    }

    // ====================================
    // CAMBIAR ESTADO DEL ROL (Activo/Inactivo)
    // ====================================
    public function cambiarEstado()
    {
        ob_start();

        $id = $_GET['id'] ?? null;
        $nuevoEstadoNombre = $_GET['estado'] ?? null;

        if ($id && $nuevoEstadoNombre) {
            $estado = $this->model->buscarEstadoPorNombre($nuevoEstadoNombre);
            if ($estado) {
                $this->model->actualizarEstadoRol($id, $estado['estado_id']);
            }
        }

        ob_end_clean();

        header('Location: index.php?modulo=rol&controlador=rol&funcion=consult');
        exit();
    }

    // ====================================
    // FORMULARIO PARA EDITAR UN ROL
    // ====================================
    public function getEdit()
    {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $rol = $this->model->obtenerRolPorId($id);
            $estados = $this->model->obtenerEstados();
            require_once 'C:\xampp\htdocs\inventario\Views\Rol\update.php';
        } else {
            echo "ID no válido.";
        }
    }

    // ====================================
    // ACTUALIZAR UN ROL
    // ====================================
    public function postEdit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['rol_id'] ?? '';
            $nombre = $_POST['rol_nombre'] ?? '';
            $estado_id = $_POST['estado_id'] ?? '';

            if ($id !== '' && $nombre !== '' && $estado_id !== '') {
                $resultado = $this->model->actualizarRol($id, $nombre, $estado_id);

                if ($resultado) {
                    header('Location: index.php?modulo=rol&controlador=rol&funcion=consult');
                    exit();
                } else {
                    echo "Error al actualizar el rol.";
                }
            } else {
                echo "Todos los campos son obligatorios.";
            }
        }
    }

    // ====================================
    // ELIMINAR UN ROL
    // ====================================
    public function delete()
    {
        $rol_id = $_GET['id'] ?? null;

        if ($rol_id) {
            $this->model->eliminarRol($rol_id);
        }

        header('Location: index.php?modulo=rol&controlador=rol&funcion=consult');
        exit();
    }

    // ====================================
    // FORMULARIO DE ASIGNACIÓN DE PERMISOS
    // ====================================
    public function getPermisos()
    {
        $rol_id = $_GET['id'] ?? null;

        if (!$rol_id) {
            exit('No se recibió el ID del rol.');
        }

        // Obtener datos del rol
        $rol = $this->model->obtenerRolPorId($rol_id);
        if (!$rol) {
            exit('Rol no encontrado.');
        }

        $modulos = $this->model->consultarModulos();
        $permisos = $this->model->consultarPermisos();

        // Obtener permisos activos asignados al rol (array con claves moduloId_permisoId)
        $rolPermisos = $this->model->obtenerPermisosPorRol($rol_id);

        $rol_id = $rol['rol_id'];
        $rol_nombre = $rol['rol_nombre'];
        require_once 'C:\xampp\htdocs\inventario\Views\Rol\permisos.php';
    }

    // ====================================
    // OBTENER TODOS LOS MÓDULOS
    // ====================================
    public function obtenerModulos()
    {
        $resultado = $this->model->consultarModulos();
        $modulos = [];

        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $modulos[] = $fila;
            }
        }

        return $modulos;
    }

    // ====================================
    // OBTENER TODOS LOS PERMISOS
    // ====================================
    public function obtenerPermisos()
    {
        $resultado = $this->model->consultarPermisos();
        $permisos = [];

        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $permisos[] = $fila;
            }
        }

        return $permisos;
    }

    // ====================================
    // GUARDAR PERMISOS ASIGNADOS A UN ROL
    // ====================================
    public function guardarPermisos()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rol_id = $_GET['id'] ?? null;

            if (!$rol_id) {
                echo "ID de rol no válido.";
                return;
            }

            $rol_id = (int)$rol_id;
            $permisos = $_POST['permisos'] ?? [];

            // Eliminar todos los permisos anteriores para este rol
            $this->model->eliminarPermisosPorRol($rol_id);

            // Insertar los permisos enviados
            foreach ($permisos as $modulo_id => $permisosModulo) {
                foreach ($permisosModulo as $permiso_id => $valor) {
                    if ($valor == 1) {
                        $this->model->insertarPermisoRol($rol_id, (int)$permiso_id, (int)$modulo_id);
                    }
                }
            }

            header('Location: index.php?modulo=rol&controlador=rol&funcion=consult');
            exit();
        }
    }
}
