<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require './funcoes.php';

$json_url = "http://localhost/api/users";
$json = file_get_contents($json_url);
$data = json_decode($json, true);

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

$email = array();
$nome = array();
$cpf = array();
$ddd = array();
$telefone =array();

foreach ($data as $d)
{
    $createdData = formatoData($d['created']);
    $createdHora = formatoHora($d['created']);
    $telefone = somenteNumeros($d['telefone']);
    $cpf = somenteNumeros($d['cpf']);
    $codecli = addID();
    $user_id = $d['id'];
    $nome = $d['nome'];
    $profissao = $d['profissao'];
    $produto = $d['produto'];
    $estado = $d['estado'];
    $cidade = $d['cidade'];
    $email = $d['email'];
    $ddd = $d['ddd'];

    if (count(verificaCpfCnpj($cpf)) == 0)
    {
        echo "<br /> $cpf Não existe";
        $stmt = $dbCon->prepare("INSERT INTO TKCONTATO (CODEMP, CODFILIAL, CODCTO, RAZCTO, CODEMPVD, CODFILIALVD, CODVEND, CODEMPSR, CODFILIALSR, CODSETOR, NOMECTO, DATACTO, PESSOACTO, ATIVOCTO, CNPJCTO, INSCCTO, CPFCTO, RGCTO, ENDCTO, NUMCTO, COMPLCTO, EDIFICIOCTO, BAIRCTO, CIDCTO, UFCTO, CEPCTO, DDDCTO, DDDCTO2, DDDCTO3, DDDCTO4, FONECTO, FONECTO2, FONECTO3, FONECTO4, FAXCTO, EMAILCTO, CONTCTO, CARGOCONTCTO, OBSCTO, REMOVCTO, CODEMPTI, CODFILIALTI, CODTPIMP, CODEMPSO, CODFILIALSO, CODSETORCTO, NUMEMPCTO, CODMUNIC, SIGLAUF, CODPAIS, CODEMPTC, CODFILIALTC, CODTIPOCLI, CODEMPOC, CODFILIALOC, CODORIGCONT, CODEMPTO, CODFILIALTO, CODTIPOCONT, DDDCELCTO, DDDCELCTO2, CELCTO, CELCTO2, CODCNAE, CODCNAE2, CODCNAE3, CODCNAE4, CODCNAE5, CODCNAE6, CODCNAE7, CODCNAE8, CODCNAE9, CODNJ, REPLICADO, DTFUNDCTO, DTREPL, HREPL, DTINS, HINS, IDUSUINS, DTALT, HALT, IDUSUALT) VALUES (99, 1, '$codecli', '$nome', NULL, NULL, NULL, 99, 1, 1, '$nome', '$createdData', 'F', 'S', NULL, NULL, '$cpf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$estado', NULL, '$ddd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$email', NULL, '$profissao', '$produto', 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$estado', 76, NULL, NULL, NULL, 99, 1, 1, 99, 1, 1, NULL, NULL, '$telefone', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', NULL, NULL, NULL, '$createdData', '$createdHora', 'SYSDBA', '$createdData', '$createdHora', 'SYSDBA');");
        $dbCon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if($stmt->execute())
        {
            echo "<br />$cpf Dados Gravados.";

            // função enviara o status do contato se já existe ou não
            // Caso exista, retorna e altera o estatus de 0(zero) para 1(um).
            cpfGravadosOuExistente($cpf,$user_id);

            // Função envia um email com os dados do novo cadastro
            // para o setor MKTVEN.
            enviaEmailContatoCadastrado($nome,$email,$ddd,$telefone,$cpf);
        }
        else
        {
            echo "<br />Erro ao Gravar Dados.";
        }

    }
    else
    {
        echo " <br />CPF $cpf Existe";

        // função enviara o status do contato se já existe ou não
        // Caso exista, retorna e altera o estatus de 0(zero) para 1(um).
        cpfGravadosOuExistente($cpf,$user_id);

        // Função envia um email para o setor MKTVEN informando que
        // o cpf já existe, junto com os dados do contato.
        enviaEmailCpfExiste($nome,$email,$ddd,$telefone,$cpf);
    }
}

if (empty($data)) {
    echo "N&atilde;o houve inscri&ccedil;&atilde;o.<br>";

    // Envia um email informando que não houve inscrição
    // no momento que rodou o script
    enviaEmailNaoHouveCadastro();
}

// var_dump($data);
