<?php

// Conexão com BD do site Dietpro
function getConnectionMysql()
{
    try {
        $db_username = "root";
        $db_password = "123456";
        $conn = new PDO('mysql:host=localhost;dbname=dietprosite;charset=utf8', $db_username, $db_password);
    } catch (Exception $e) {
        echo 'ERRO:' . $e->getMessage();
    }
    return $conn;
}

// Pega todos os usuários e envia via webservice
function getUsers()
{
    try {
        $dbCon = getConnectionMysql();
        $stmt = $dbCon->query("SELECT * FROM a6nvl_demonstrativo");
        $users = $stmt->fetchALL(PDO::FETCH_OBJ);
        $dbCon = null;
        $json = json_encode($users, JSON_PRETTY_PRINT);

    } catch (PDOException $e) {
        echo '{"error":{"text":' . $e->getMessage() .'}}';
    }
    return $json;
}

// Conexão com BD Firebird
function getConnectionFirebird()
{
    try
    {
        $db_username = "SYSDBA";
        $db_password = "masterkey";
        $conn = new PDO('firebird:host=localhost;dbname=C:\\opt\\Firebird\\dados\\freedom.fdb;charset=UTF8', $db_username, $db_password);
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
    $SelectLastID = $dbCon->query("SELECT FIRST 1 CODCLI FROM VDCLIENTE ORDER BY CODCLI DESC");
    $LastID = $SelectLastID->fetchAll(PDO::FETCH_OBJ);
    foreach ($LastID as $i) {
        $ultimoId = ++$i->CODCLI;
    }
    return $ultimoId;
}

// Verifica se existe o CPF ou CNPJ
function verificaCpfCnpj($vercpfcnpj)
{
    $dbCon = getConnectionFirebird();
    $sql = "SELECT CNPJCLI, CPFCLI FROM VDCLIENTE WHERE CNPJCLI = '$vercpfcnpj' OR CPFCLI = '$vercpfcnpj'";
    return $dbCon->query($sql)->fetchAll();
}

function formatoData($datetime)
{
    $dt = new DateTime($datetime);
    return $dt->format('Y.m.d');
}

function formatoDataNasc($dateNasc)
{
    $dt = date_create_from_format('d/m/Y', $dateNasc);
    return date_format($dt,'Y.m.d');
}
