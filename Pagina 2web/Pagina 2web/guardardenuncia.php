<?php

header(
    "Content-Type: application/json; charset=UTF-8"
);


/* ==========================================
   COMPROBAR MÉTODO
========================================== */

if($_SERVER["REQUEST_METHOD"] !== "POST"){

    http_response_code(405);

    echo json_encode([
        "ok" => false,
        "message" => "Método no permitido."
    ]);

    exit;

}


/* ==========================================
   LIMPIAR DATOS
========================================== */

function limpiar($dato){

    return trim(
        strip_tags(
            $dato ?? ""
        )
    );

}


$nombre =
    limpiar($_POST["nombre"]);

$correo =
    filter_var(
        trim($_POST["correo"] ?? ""),
        FILTER_VALIDATE_EMAIL
    );

$telefono =
    limpiar($_POST["telefono"]);

$tipo =
    limpiar($_POST["tipo"]);

$provincia =
    limpiar($_POST["provincia"]);

$municipio =
    limpiar($_POST["municipio"]);

$lugar =
    limpiar($_POST["lugar"]);

$fecha =
    limpiar($_POST["fecha_hecho"]);

$hora =
    limpiar($_POST["hora_hecho"]);

$descripcion =
    limpiar($_POST["descripcion"]);


/* ==========================================
   VALIDAR
========================================== */

if(

    !$nombre ||
    !$correo ||
    !$tipo ||
    !$provincia ||
    !$municipio ||
    !$lugar ||
    !$fecha ||
    strlen($descripcion) < 20 ||
    empty($_POST["confirmacion"])

){

    http_response_code(422);

    echo json_encode([

        "ok" => false,

        "message" =>
        "Completa todos los campos obligatorios."

    ]);

    exit;

}


/* ==========================================
   CREAR CARPETAS
========================================== */

$carpetaDatos =
    __DIR__ .
    DIRECTORY_SEPARATOR .
    "data";


$carpetaEvidencias =
    __DIR__ .
    DIRECTORY_SEPARATOR .
    "uploads";


if(!is_dir($carpetaDatos)){

    mkdir(
        $carpetaDatos,
        0755,
        true
    );

}


if(!is_dir($carpetaEvidencias)){

    mkdir(
        $carpetaEvidencias,
        0755,
        true
    );

}


/* ==========================================
   EVIDENCIA
========================================== */

$evidencia = "";


if(

    isset($_FILES["evidencia"]) &&
    $_FILES["evidencia"]["error"]
    === UPLOAD_ERR_OK

){

    if(
        $_FILES["evidencia"]["size"]
        > 5 * 1024 * 1024
    ){

        http_response_code(422);

        echo json_encode([

            "ok" => false,

            "message" =>
            "La evidencia supera los 5 MB."

        ]);

        exit;

    }


    $tiposPermitidos = [

        "image/jpeg" => "jpg",

        "image/png" => "png",

        "application/pdf" => "pdf"

    ];


    $tipoArchivo =
        mime_content_type(
            $_FILES["evidencia"]["tmp_name"]
        );


    if(
        !isset(
            $tiposPermitidos[$tipoArchivo]
        )
    ){

        http_response_code(422);

        echo json_encode([

            "ok" => false,

            "message" =>
            "Tipo de archivo no permitido."

        ]);

        exit;

    }


    $nombreArchivo =

        "evidencia_" .

        date("Ymd_His") .

        "_" .

        bin2hex(
            random_bytes(4)
        ) .

        "." .

        $tiposPermitidos[$tipoArchivo];


    move_uploaded_file(

        $_FILES["evidencia"]["tmp_name"],

        $carpetaEvidencias .
        DIRECTORY_SEPARATOR .
        $nombreArchivo

    );


    $evidencia =
        $nombreArchivo;

}


/* ==========================================
   GENERAR ID
========================================== */

$id =

    "ECO-" .

    date("Ymd-His") .

    "-" .

    strtoupper(
        bin2hex(
            random_bytes(2)
        )
    );


/* ==========================================
   GUARDAR CSV
========================================== */

$archivo =

    $carpetaDatos .
    DIRECTORY_SEPARATOR .
    "denuncias.csv";


$archivoNuevo =
    !file_exists($archivo);


$fp =
    fopen(
        $archivo,
        "a"
    );


if(!$fp){

    http_response_code(500);

    echo json_encode([

        "ok" => false,

        "message" =>
        "No se pudo guardar la denuncia."

    ]);

    exit;

}


if($archivoNuevo){

    fputcsv(
        $fp,
        [
            "ID",
            "Fecha registro",
            "Nombre",
            "Correo",
            "Telefono",
            "Tipo",
            "Provincia",
            "Municipio",
            "Lugar",
            "Fecha hecho",
            "Hora",
            "Descripcion",
            "Evidencia"
        ]
    );

}


fputcsv(

    $fp,

    [

        $id,

        date("Y-m-d H:i:s"),

        $nombre,

        $correo,

        $telefono,

        $tipo,

        $provincia,

        $municipio,

        $lugar,

        $fecha,

        $hora,

        $descripcion,

        $evidencia

    ]

);


fclose($fp);


/* ==========================================
   CORREO
========================================== */

/*
   CAMBIA ESTA DIRECCIÓN POR EL CORREO
   QUE DEBE RECIBIR LAS DENUNCIAS.

   IMPORTANTE:
   mail() depende de la configuración
   del servidor.

*/

$destinatario =
    "TU_CORREO@ejemplo.com";


$asunto =
    "Nueva denuncia ambiental - " .
    $id;


$mensaje =

"ID DE DENUNCIA: $id

Nombre: $nombre

Correo: $correo

Teléfono: $telefono

Tipo: $tipo

Provincia: $provincia

Municipio: $municipio

Lugar: $lugar

Fecha: $fecha

Hora: $hora


DESCRIPCIÓN:

$descripcion
";


$headers =

"From: no-reply@ecodistrito.com\r\n" .

"Reply-To: $correo\r\n";


$correoEnviado = false;


if(
    filter_var(
        $destinatario,
        FILTER_VALIDATE_EMAIL
    )
){

    $correoEnviado =
        @mail(
            $destinatario,
            $asunto,
            $mensaje,
            $headers
        );

}


/* ==========================================
   RESPUESTA
========================================== */

echo json_encode([

    "ok" => true,

    "message" =>

        "Denuncia registrada correctamente. " .

        "Código: " .

        $id

]);

?>