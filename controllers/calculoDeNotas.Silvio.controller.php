<?php

declare(strict_types=1);

if (isset($_POST['enviar'])) {
    $data['errores'] = checkNotas($_POST);
    $data['input'] = filter_var_array($_POST);
    if (count($data['errores']) === 0) {
        $jsonArray = json_decode($_POST['json_notas'], true);
    }
}

function checkNotas(array $post): array {
    $errores = [];
    if (empty($post['json_notas'])) {
        $errores['json_notas'] = 'Este campo es obligatorio';
    } else {
        $json = json_decode($post['json_notas'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errores['json_notas'] = 'El formato no es correcto';
        } else {
            foreach ($json as $modulo => $valorModulo) {
                if (is_array($valorModulo)) {
                    if (empty($modulo)) {
                        $errores['json_notas'] = 'El módulo no puede estar vacio';
                    } else {
                        foreach ($valorModulo as $nombre => $notas) {
                            foreach ($notas as $nota) {
                                if (empty($nombre)) {
                                    $errores['json_notas'] = 'El nombre del alumno no puede estar vacio';
                                } else {
                                    if (!is_numeric($nota) || $nota < 0 || $nota > 10) {
                                        $errores['json_notas'] = 'Las notas deben ser numeros entre cero y diez, inclusive ';                                        
                                    }
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

include 'views/templates/header.php';
include 'views/calculoDeNotas.Silvio.view.php';
include 'views/templates/footer.php';
