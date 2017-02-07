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
$users = $stmt->fetch(PDO::FETCH_ASSOC);
$current_user = $users['USER'];
$current_connection = $users['CURRENT_CONNECTION'];

$queryInsert = "INSERT INTO SGCONEXAO (NRCONEXAO,CODEMP,CODFILIAL,IDUSU,CODFILIALSEL,CONECTADO) VALUES ($current_connection,99,1,'$current_user',1,1)";
$stmt = $dbCon->prepare($queryInsert);
if ($stmt->execute())
{
    echo "Sessão $current_connection Gravada <br />";
}
else
{
    print_r($dbCon->errorInfo());
    echo "Sessão não pode ser gravada <br />";
}

foreach ($data as $d)
{
    $cpf = somenteNumeros($d['cpf']);
    $id = $d['id'];
    $uniq_id = $d['uniq_id'];
    $user_id = $d['user_id'];
    $createdData = formatoData($d['created']);
    $createdHora = formatoHora($d['created']);
    $nome = $d['nome'];
    $profissao = $d['profissao'];
    $produto = $d['produto'];
    $cpf = somenteNumeros($d['cpf']);
    $estado = $d['estado'];
    $cidade = $d['cidade'];
    $email = $d['email'];
    $telefone = somenteNumeros($d['telefone']);
    $ddd = $d['ddd'];

    if (count(verificaCpfCnpj($cpf)) == 0)
    {
        echo "$cpf Não existe<br />";
        // try
        // {
            $query = "INSERT INTO TKCONTATO (CODEMP, CODFILIAL, CODCTO, RAZCTO, CODEMPVD, CODFILIALVD, CODVEND, CODEMPSR, CODFILIALSR, CODSETOR, NOMECTO, DATACTO, PESSOACTO, ATIVOCTO, CNPJCTO, INSCCTO, CPFCTO, RGCTO, ENDCTO, NUMCTO, COMPLCTO, EDIFICIOCTO, BAIRCTO, CIDCTO, UFCTO, CEPCTO, DDDCTO, DDDCTO2, DDDCTO3, DDDCTO4, FONECTO, FONECTO2, FONECTO3, FONECTO4, FAXCTO, EMAILCTO, CONTCTO, CARGOCONTCTO, OBSCTO, REMOVCTO, CODEMPTI, CODFILIALTI, CODTPIMP, CODEMPSO, CODFILIALSO, CODSETORCTO, NUMEMPCTO, CODMUNIC, SIGLAUF, CODPAIS, CODEMPTC, CODFILIALTC, CODTIPOCLI, CODEMPOC, CODFILIALOC, CODORIGCONT, CODEMPTO, CODFILIALTO, CODTIPOCONT, DDDCELCTO, DDDCELCTO2, CELCTO, CELCTO2, CODCNAE, CODCNAE2, CODCNAE3, CODCNAE4, CODCNAE5, CODCNAE6, CODCNAE7, CODCNAE8, CODCNAE9, CODNJ, REPLICADO, DTFUNDCTO, DTREPL, HREPL, DTINS, HINS, IDUSUINS, DTALT, HALT, IDUSUALT)
                     VALUES (99, 1, $codecli, '$nome', NULL, NULL, NULL, 99, 1, 1, '$nome', '$createdData', 'F', 'S', NULL, NULL, '$cpf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$estado', NULL, '$ddd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$email', NULL, '$profissao', '$produto', 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$estado', 76, NULL, NULL, NULL, 99, 1, 1, 99, 1, 1, NULL, NULL, '$telefone', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', NULL, NULL, NULL, '$createdData', '$createdHora', 'SYSDBA', '$createdData', '$createdHora', 'SYSDBA');";
            $stmt = $dbCon->prepare($query);

            if($stmt->execute())
            {
                echo "$cpf Dados Gravados.<br />";
            }
            else
            {
                echo "Erro ao Gravador dados.";
            }
        // }
        // catch(PDOexception $e)
        // {
        //     echo $e->getMessage()."<br />";
        // }
    }
    else
    {
        echo "CPF $cpf Existe <br />";
    }
}

// var_dump($data);
