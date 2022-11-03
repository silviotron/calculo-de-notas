<?php

declare(strict_types=1);
if (isset($_POST['enviar'])) {
    $data['input'] = filter_var_array($_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    $data['errores'] = checkNotas($_POST);
    if (count($data['errores']) === 0) {
        $jsonArray = json_decode($_POST['json_notas'], true);
        $data['resultado'] = datosModulos($jsonArray);
    }
}

function checkNotas(array $post): array {
    $errores = [];
    if (empty($post['json_notas'])) {
        $errores['json_notas'][] = 'Este campo es obligatorio';
    } else {
        $json = json_decode($post['json_notas'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errores['json_notas'][] = 'El formato no es correcto';
        } else {
            foreach ($json as $modulo => $valorModulo) {
                if (is_array($valorModulo)) {
                    if (empty($modulo)) {
                        $errores['json_notas'][] = 'El módulo no puede estar vacio';
                    } else {
                        foreach ($valorModulo as $nombre => $notas) {
                            if (empty($nombre)) {
                                $errores['json_notas'][] = "'$modulo' => VACIO. El nombre del alumno no puede estar vacio";
                            }
                            foreach ($notas as $nota) {
                                if ($nota == null) {
                                    $errores['json_notas'][] = "'$modulo' => '$nombre' => NULL. Las notas deben ser numeros entre cero y diez, inclusive";                                    
                                }                                
                                else if (!is_numeric($nota) || $nota < 0 || $nota > 10) {
                                    $errores['json_notas'][] = "'$modulo' => '$nombre' => '$nota'. Las notas deben ser numeros entre cero y diez, inclusive";
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $errores;
}

function datosModulos(array $modulos): array {
    $resultado = [];
    $alumnos = [];
    foreach ($modulos as $nombreModulo => $notas) {
        $resultado[$nombreModulo] = [];
        $suspensos = 0;
        $aprobados = 0;
        $max = [
            'alumno' => '',
            'nota' => -1
        ];
        $min = [
            'alumno' => '',
            'nota' => 11
        ];
        $notaAcumulada = 0;
        $contarAlumnos = 0;

        foreach ($notas as $alumno => $notasAlumno) {
            $nota = calcMedia($notasAlumno);
            if (!isset($alumnos[$alumno])) {
                $alumnos[$alumno] = ['suspensos' => 0, 'aprobados' => 0];
            }
            $contarAlumnos++;
            $notaAcumulada += $nota;
            if ($nota < 5) {
                $suspensos++;
                $alumnos[$alumno]['suspensos']++;
            } else {
                $aprobados++;
                $alumnos[$alumno]['aprobados']++;
            }
            if ($nota > $max['nota']) {
                $max['alumno'] = $alumno;
                $max['nota'] = $nota;
            }
            if ($nota < $min['nota']) {
                $min['alumno'] = $alumno;
                $min['nota'] = $nota;
            }
        }
        if ($contarAlumnos > 0) {
            $max['nota'] = number_format($max['nota']);
            $min['nota'] = number_format($min['nota']);
            $resultado[$nombreModulo]['media'] = number_format($notaAcumulada / $contarAlumnos,2);
            $resultado[$nombreModulo]['max'] = $max;
            $resultado[$nombreModulo]['min'] = $min;
        } else {
            $resultado[$nombreModulo]['media'] = 0;
        }
        $resultado[$nombreModulo]['suspensos'] = $suspensos;
        $resultado[$nombreModulo]['aprobados'] = $aprobados;
    }
    return array('modulo' => $resultado, 'alumnos' => $alumnos);
}

function calcMedia($valores) {
    if (is_array($valores)) {
        $media = array_sum($valores) / count($valores);
    } else {
        $media = $valores;
    }
    return $media;
}
//number_format($numero, 1)

include 'views/templates/header.php';
include 'views/calculoDeNotas.Silvio.view.php';
include 'views/templates/footer.php';
