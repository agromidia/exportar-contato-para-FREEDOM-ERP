<?php
require './funcoes.php';

$json_url = "http://localhost/api/users";
$json = file_get_contents($json_url);
$data = json_decode($json, true);

$horaAtual = date('H:i:s');
$dataAtual = date('d.m.Y');
$codecli = addID();

$dbCon = getConnectionFirebird();

// Conexão com o Firebird, pega o usuário e a sessão e salva no BD SGCONEXAO
$querySelect = "SELECT USER, CURRENT_CONNECTION FROM SGEMPRESA WHERE CODEMP='99'";
$stmt = $dbCon->query($querySelect);
$users = $stmt->fetchALL(PDO::FETCH_OBJ);

foreach ($users as $u)
{
    $queryInsert = "INSERT INTO SGCONEXAO (NRCONEXAO,CODEMP,CODFILIAL,IDUSU,CODFILIALSEL,CONECTADO) VALUES ($u->CURRENT_CONNECTION,99,1,'$u->USER',1,1)";
    $stmt = $dbCon->prepare($queryInsert);

    if ($stmt->execute())
    {
        echo "Sessão $u->CURRENT_CONNECTION Gravada <br />";
    }
    else
    {
        echo "ERRO";
    }
}

foreach ($data as $i) {
    $cpfcnpj2 = somenteNumeros($i['cpfcnpj']);
}

if (count(verificaCpfCnpj($cpfcnpj2)) == 0)
{
    echo "$cpfcnpj2 Não existe<br />";
    // CPF e/ou CNPJ não existe
    foreach ($data as $d)
    {
        $id = $d['id'];
        $uniq_id = $d['uniq_id'];
        $user_id = $d['user_id'];
        $created = formatoData($d['created']);
        $modified = $d['modified'];
        $tipopessoa = $d['tipopessoa'];
        $razaosocialnome = $d['razaosocialnome'];
        $cpfcnpj = somenteNumeros($d['cpfcnpj']);
        $rgie = somenteNumeros($d['rgie']);
        $datadenascimento = formatoDataNasc($d['datadenascimento']);
        $cepcodigopostal = somenteNumeros($d['cepcodigopostal']);
        $estado = $d['estado'];
        $cidade = $d['cidade'];
        $bairro = $d['bairro'];
        $rua = $d['rua'];
        $numero = $d['numero'];
        $complemento = $d['complemento'];
        $email = $d['email'];
        $telefone = somenteNumeros($d['telefone']);
        $dddcli = $d['dddcli'];
        $celular = somenteNumeros($d['celular']);
        $dddcelcli = somenteNumeros($d['dddcelcli']);
        $termo = $d['termo'];
        $ativo = $d['ativo'];
        $codmunicipio = $d['cod'];
        $municipio = $d['municipio'];
        try
        {
            // Trata os campo cpfcnpj retirando caracteres e contando digitos, respectivamente.
            $soNumeros = somenteNumeros($cpfcnpj);
            $qtdDigitos = qtdDigitos($soNumeros);
            $dbCon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Começo da query para inserir os usuarios
            $query = "INSERT INTO VDCLIENTE (CODEMP,CODFILIAL,CODCLI,RAZCLI,NOMECLI,CODEMPCC,CODFILIALCC,CODCLASCLI,CODEMPTI,CODFILIALTI,CODTIPOCLI,DATACLI,PESSOACLI,CONSUMIDORCLI,ATIVOCLI,CNPJCLI,INSCCLI,CPFCLI,RGCLI,SSPCLI,ENDCLI,NUMCLI,COMPLCLI,EDIFICIOCLI,BAIRCLI,CIDCLI,UFCLI,CEPCLI,DDDCLI,FONECLI,EMAILCLI,CONTCLI,CODEMPPQ,CODFILIALPQ,CODPESQ,SIMPLESCLI,DDDCELCLI,CELCLI,CODMUNIC,SIGLAUF,CODPAIS,PRODRURALCLI,CTOCLI,DESCIPI,IDENTCLIBCO,DTNASCCLI,PEDCLIOBRIG,SITREVCLI,VENDA3CLI,ENVIAIMNFSE,EMMANUT,DTINS,HINS,IDUSUINS,DTALT,HALT,IDUSUALT) ";
            // Se for CPF
            if ($qtdDigitos === 11)
            {
                $query .= "VALUES (99,1,$codecli,'$razaosocialnome','$razaosocialnome',99,1,2,99,1,1,'$created','F','N','N',NULL,NULL,'$cpfcnpj','$rgie','SSP','$rua',$numero,'$complemento',NULL,'$bairro','$cidade','$estado','$cepcodigopostal','$dddcli','$telefone','$email','$razaosocialnome',99,1,$codecli,'N','$dddcelcli','$celular','$codmunicipio','$estado',76,'N','C','N','D','$datadenascimento','N','N','N','N','N','$created','$horaAtual','SYSDBA','$dataAtual','$horaAtual','SYSDBA')";
            }
            // Se for CNPJ
            else
            {
                $query .= "VALUES (99,1,$codecli,'$razaosocialnome','$razaosocialnome',99,1,2,99,1,1,'$created','J','N','N','$cpfcnpj','$rgie',NULL,NULL,NULL,'$rua',$numero,'$complemento',NULL,'$bairro','$cidade','$estado','$cepcodigopostal','$dddcli','$telefone','$email','$razaosocialnome',99,1,$codecli,'N','$dddcelcli','$celular','$codmunicipio','$estado',76,'N','C','N','D','$datadenascimento','N','N','N','N','N','$created','$horaAtual','SYSDBA','$dataAtual','$horaAtual','SYSDBA')";
            };
            $stmt = $dbCon->prepare($query);
            if($stmt->execute())
            {
                echo "$cpfcnpj Dados Gravados.<br />";
            }
            else
            {
                echo "Erro ao Gravador dados.";
            }
        }
        catch(PDOexception $e)
        {
            echo $e->getMessage();
        }
    }
}
else
{
    echo "CPF $cpfcnpj2 Existe";
}

