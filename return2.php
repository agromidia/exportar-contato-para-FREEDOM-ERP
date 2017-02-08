<?php

require './funcoes.php';
    $dbCon = getConnectionFirebird();
    $SelectLastID = $dbCon->query("SELECT FIRST 1 CODCTO FROM TKCONTATO ORDER BY CODCTO DESC");
    $LastID = $SelectLastID->fetchAll(PDO::FETCH_OBJ);

    foreach ($LastID as $i) {
        $ultimoId = ++$i->CODCTO;
    }
print($ultimoId);
