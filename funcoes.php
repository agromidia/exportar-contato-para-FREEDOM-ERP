<?php

// Conexão com BD do site Dietpro
function getConnectionMysql()
{
    try {
        $db_username = "dietprosite";
        $db_password = "hY2nBuyyYH9DmDFG";
        $conn = new PDO('mysql:host=192.168.1.80;dbname=dietprosite;charset=utf8', $db_username, $db_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo 'ERRO:' . $e->getMessage();
    }
    return $conn;
}

// Pega todos os usuários e envia via webservice
function getUsers()
{
    $dbCon = getConnectionMysql();
    try {
        $stmt = $dbCon->query("SELECT * FROM a6nvl_demonstrativo WHERE status=0");
        $users = $stmt->fetchALL(PDO::FETCH_OBJ);
        $dbCon = null;
        $json = json_encode($users, JSON_PRETTY_PRINT);

    } catch (PDOException $e) {
        echo '{"error":{"text":' . $e->getMessage() .'}}';
    }
    return $json;
}

function returnUsersStatus($req, $res, $args)
{
    $cpf = $args['cpf'];
    $id = $args['id'];
    $dbCon = getConnectionMysql();
    $sql = "UPDATE `a6nvl_demonstrativo` SET `status`='1' WHERE `cpf`='$cpf' AND `id`='$id'";
    try
    {
        $result = $dbCon->exec($sql);

        if($result !== false)
        {
            echo "Status alterado";
            $dbCon = null;        // Disconnect
        }
        else
        {
            echo "Erro ao alterar o Status";
        }
    }
    catch (PDOException $e)
    {
        echo '{"error":{"text":' . $e->getMessage() .'}}';
    }
}

function cpfGravadosOuExistente($cpf,$id)
{
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,"http://localhost/api/returnUsers/$cpf/$id");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $output=curl_exec($ch);
    curl_close($ch);
}

// Conexão com BD Firebird
function getConnectionFirebird()
{
    try
    {
        $conn = new PDO('firebird:host=localhost;dbname=C:\\opt\\Firebird\\dados\\freedom.fdb;charset=UTF8', 'SYSDBA', 'masterkey');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (Exception $e)
    {
        echo '<br>ERRO:' . $e->getMessage();
    }
    return $conn;
}

// Remove todos os caracteres deixando somente números
function somenteNumeros($stringSoNumeros)
{
    return preg_replace("/[^0-9]/", "", $stringSoNumeros);
}

// Quantidade de digitos da campo cpfcnpj
function qtdDigitos($stringQtNumero)
{
    $qtNumero = preg_replace("/[^0-9]/", "", $stringQtNumero);
    return strlen($qtNumero);
}

// Recupera o ID anterior e adicionar +1 e salva como se fosse auto-increment do ID
function addID()
{
    $dbCon = getConnectionFirebird();
    $SelectLastID = $dbCon->query("SELECT FIRST 1 CODCTO FROM TKCONTATO ORDER BY CODCTO DESC");
    $LastID = $SelectLastID->fetchAll(PDO::FETCH_OBJ);

    foreach ($LastID as $i) {
        $ultimoId = ++$i->CODCTO;
    }

    return $ultimoId;
}

// Verifica se existe o CPF
function verificaCpfCnpj($qtdcpf)
{
    $dbCon = getConnectionFirebird();
    $sql = "SELECT CPFCTO FROM TKCONTATO WHERE CPFCTO = '$qtdcpf'";
    return $dbCon->query($sql)->fetchAll();
}

function formatoData($datetime)
{
    $dt = new DateTime($datetime);
    return $dt->format('d-M-Y');
}

function formatoHora($datetime)
{
    $dt = new DateTime($datetime);
    return $dt->format('H:i:s');
}

function formatoDataNasc($dateNasc)
{
    $dt = date_create_from_format('d/m/Y', $dateNasc);
    return date_format($dt,'Y.m.d');
}

//
// Envio de eMails
//
function enviaEmailCpfExiste($nome,$email,$ddd,$telefone,$cpf)
{
    // Inclui o arquivo class.phpmailer.php localizado na pasta class
    require_once("PHPMailer/PHPMailerAutoload.php");
    require_once("PHPMailer/class.smtp.php");

    $mail = new PHPMailer;

    $mail->SMTPDebug = 2;                               // Enable verbose debug output

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->CharSet = 'UTF-8';
    $mail->Host = 'mail.dietpro.com.br;smtp.dietpro.com.br';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'suporte@dietpro.com.br';                 // SMTP username
    $mail->Password = 'R45EzuN0';                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465;                                    // TCP port to connect to

    $mail->setFrom('suporte@dietpro.com.br', 'Sistema de exportação de contato FREEDOM ERP');
    $mail->addAddress('webmaster@assistemas.com.br', 'Joe User');     // Add a recipient
    // $mail->addReplyTo('info@example.com', 'Information');


    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = 'Houve Inscrições.';
    $mail->Body    = "
        <p><span style='font-size:18px;'>O sistema identificou inscri&ccedil;&atilde;o, o contato j&aacute; existe no sistema FREEDOM ERP CRM.</span></p>
        <p>Dados Cadastrados</p>
        <table align='left' border='0' cellpadding='1' cellspacing='1' style='width: 500px;'>
            <tbody>
                <tr>
                    <td>Nome:</td>
                    <td>$nome</td>
                </tr>
                <tr>
                    <td>CPF:</td>
                    <td>$cpf</td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>$email</td>
                </tr>
                <tr>
                    <td>Contato:</td>
                    <td>$ddd $telefone</td>
                </tr>
            </tbody>
        </table>
    ";
    // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if(!$mail->send()) {
        echo 'Message could not be sent.<br>';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Message has been sent';
    }
}

function enviaEmailContatoCadastrado($nome,$email,$ddd,$telefone,$cpf)
{

}

function enviaEmailNaoHouveCadastro()
{

    // Inclui o arquivo class.phpmailer.php localizado na pasta class
    require_once("PHPMailer/PHPMailerAutoload.php");
    require_once("PHPMailer/class.smtp.php");

    $mail = new PHPMailer;

    $mail->SMTPDebug = 2;                               // Enable verbose debug output

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->CharSet = 'UTF-8';
    $mail->Host = 'mail.dietpro.com.br;smtp.dietpro.com.br';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'suporte@dietpro.com.br';                 // SMTP username
    $mail->Password = 'R45EzuN0';                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465;                                    // TCP port to connect to

    $mail->setFrom('suporte@dietpro.com.br', 'Sistema de exportação de contato FREEDOM ERP');
    $mail->addAddress('webmaster@assistemas.com.br', 'Joe User');     // Add a recipient
    // $mail->addReplyTo('info@example.com', 'Information');


    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = 'Não Houve Inscrições.';
    $mail->Body    = '<p><span style="font-size:18px;">O sistema n&atilde;o identificou inscri&ccedil;&otilde;es.</span></p>';
    // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if(!$mail->send()) {
        echo 'Message could not be sent.<br>';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Message has been sent';
    }

}
