<?php
    $apitoken = "YOUR_BOT_API_TOKEN"; //Vaya a @BotFather en telegram y cree un nuevo bot 
    $website = "https://api.telegram.org/bot".$apitoken;
    error_reporting(0);
    date_default_timezone_set("America/Lima");
    //Data from webhook
    $content = file_get_contents('php://input');
    $update = json_decode($content, true);
    #$print = print_r($update);
    $chat_id = $update['message']['chat']['id'];
    $message = $update['message']['text'];
    $id = $update['message']['from']['id'];
    $username = $update['message']['from']['username'];
    $firstname = $update['message']['from']['first_name'];
    $lastname = $update['message']['from']['last_name'];
    $message_id = $update['message']['message_id'];

$mention_user = "<a href='tg://user?id=".$id."'>".$firstname."</a>";

// Inicio del bot
if ($message == '/start' xor $message == '!start') {
    sendMessage($chat_id, "Hola ".$mention_user.", para saber como usar este bot escribe <code>/cmds</code>", $message_id);
}
// !cmds comando
if ($message == '/cmds' xor $message == '!cmds') {
    sendMessage($chat_id, "Todos los comandos funcionan con (/) o (!)\n-» <b>!dni</b> - Búsqueda de información de un <b>DNI</b> de Perú\n-» <b>!ruc</b> - Búsqueda de información de una <b>RUC</b> de Perú\nBot creado por @caspercardergil", $message_id);
}
// !dni comando
if ((strpos($message, "!dni") === 0) xor (strpos($message, "/dni") === 0)) {
    $dni_cmd = substr($message, 5);
    if (empty($dni_cmd)) {
        $dni_msg = "<u><b>Busqueda de DNI</b></u>\n-» <b>Formato:</b> <i>!dni DNI</i>";
    } else {
        $dnidata = json_decode(file_get_contents('https://www.consultarucsunat.com/api_empresas.php?op=consulta&documento='.$dni_cmd.'&tipo=D&origen=1'),true);
        $dni = $dnidata['dni'];
        $apellidoP = $dnidata['paterno'];
        $apellidoM = $dnidata['materno'];
        $nombres = $dnidata['nombre'];
        if (empty($nombres)) {
            $dni_msg = "<b>DNI NO ENCONTRADO</b>";
        } else {
            $dni_msg = "-» <b>DB:</b> <i>CONSULTA RUC 🇵🇪</i>\n-» <b><u>DNI:</u></b> ".$dni."\n-» <u><b>Apellidos:</b></u> ".$apellidoP." ".$apellidoM."\n-» <b><u>Nombres:</u></b> ".$nombres."\nBot creado por @caspercardergil";
        }
    }
    sendMessage($chat_id, $dni_msg, $message_id);
}
// !ruc comando
if ((strpos($message, "!ruc") === 0) xor (strpos($message, "/ruc") === 0)) {
    $ruc_cmd = substr($message, 5);
    if (empty($ruc_cmd)) {
        $ruc_msg = "<u><b>Busqueda de RUC</b></u>\n-» <b>Formato:</b> <i>!ruc RUC</i>";
    } else {
        $ch = curl_init('http://api.sunat.binvoice.net/consulta.php');//Inicia la sesión cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);//Devuelve el resultado como cadena
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//Configura cURL para que no verifique el peer del certificado
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'nruc='.$ruc_cmd.'');
        $data = curl_exec($ch);// Asigna la información a la variable $rucdata
        curl_close($ch);//Cierra la sesión cURL
        $rucdata = json_decode($data, true);
        $ruc = $rucdata['result']['ruc'];
        $razon_social = $rucdata['result']['razon_social'];
        $nombre_comercial = $rucdata['result']['nombre_comercial'];
        $tipo = $rucdata['result']['tipo'];
        $fecha_inscripcion = $rucdata['result']['fecha_inscripcion'];
        $estado = $rucdata['result']['estado'];
        $direccion = $rucdata['result']['direccion'];
        $ple = $rucdata['result']['ple'];
        $actividad_economica_ciiu = $rucdata['result']['actividad_economica']['0']['ciiu'];
        $actividad_economica_descripcion = $rucdata['result']['actividad_economica']['0']['descripcion'];
        $mensajeruc = $rucdata['message'];
        $successruc = $rucdata['success'];
        if (empty($ple)) {
            $ruc_msg = "-» <b>ERROR:</b> ".$mensajeruc."";
        } else {
            $ruc_msg = "-» <b><u>DB:</u></b> <i>Binvoice.Net 🇵🇪</i>\n-» <b><u>RUC:</u></b> ".$ruc."\n-» <b><u>Razón social:</u></b> ".$razon_social."\n-» <b><u>Nombre comercial:</u></b> ".$nombre_comercial."\n-» <b><u>Tipo:</u></b> ".$tipo."\n-» <b><u>Fecha de inscripción:</u></b> ".$fecha_inscripcion."\n-» <b><u>Estado:</u></b> ".$estado."\n-» <b><u>Dirección:</u></b> ".$direccion."\n-» <b><u>Ple</u></b> ".$ple."\n-» <b><u>Actividad Económica:</u></b>\n    •<b>Ciiu:</b> ".$actividad_economica_ciiu."\n   •<b>Descripción:</b> ".$actividad_economica_descripcion."\nBot creado por @caspercardergil";
        }
    }
    sendMessage($chat_id, $ruc_msg, $message_id);
}

//Enviar mensajes, funcion principal
function sendMessage($chat_id, $message, $message_id){
    $text = urlencode($message);
    $url = $GLOBALS['website'].'/sendMessage?chat_id='.$chat_id.'&text='.$text.'&reply_to_message_id='.$message_id.'&parse_mode=Html';
    file_get_contents($url);
}
