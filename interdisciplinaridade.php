<?php
$host = '192.168.31.39';        // Endereço do servidor (exemplo: localhost ou IP)
$dbname = 'db231072095';      // Nome do banco de dados
$username = 'usuario';      // Nome de usuário do banco de dados
$password = 'usuario';        // Senha do usuário


try {
    // Criando a conexão PDO
    $dsn = "pgsql:host=$host;dbname=$dbname";  // String de conexão
    $pdo = new PDO($dsn, $username, $password);
   
    // Definir o modo de erro do PDO para exceção
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
    echo "Conectado com sucesso ao PostgreSQL!";
} catch (PDOException $e) {
    // Caso haja erro na conexão, exibe a mensagem
    echo "Erro na conexão: " . $e->getMessage();
}


// Se o ID do cliente foi fornecido pelo formulário
if (isset($_POST['cliente_id'])) {
    $cliente_id = $_POST['cliente_id'];
   
    // 03) Mostrar os dados do cliente na tela
    $query = "SELECT * FROM clientes WHERE cliente_id = :cliente_id";
   
    try {
        // Preparar a consulta SQL
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
       
        // Executar a consulta
        $stmt->execute();
       
        // Recuperar o resultado
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
       
        if ($cliente) {
            // Armazenar dados atuais em variáveis
            $nome_atual = $cliente['nome'];
            $limite_atual = $cliente['limite'];
           
            // Exibir os dados na tela
            echo "<h3>Dados atuais do cliente</h3>";
            echo "Nome: " . htmlspecialchars($nome_atual) . "<br>";
            echo "Limite: " . htmlspecialchars($limite_atual) . "<br>";
           
            // Exibir o formulário para edição
            echo '
                <form method="POST" action="">
                    <label for="nome">Nome:</label><input type="text" id="nome" name="nome" value="' . htmlspecialchars($nome_atual) . '"><br>
                    <label for="limite">Limite:</label><input type="text" id="limite" name="limite" value="' . htmlspecialchars($limite_atual) . '"><br>
                    <input type="hidden" name="cliente_id" value="' . $cliente_id . '">
                    <input type="submit" name="alterar" value="Alterar">
                </form>
            ';
        } else {
            echo "Cliente não encontrado!";
        }
       
    } catch (PDOException $e) {
        echo "Erro ao buscar dados do cliente: " . $e->getMessage();
    }
}


// Se o botão de alteração for pressionado
if (isset($_POST['alterar'])) {
    $cliente_id = $_POST['cliente_id'];
    $nome_novo = $_POST['nome'];
    $limite_novo = $_POST['limite'];
   
    // 04) Iniciar a transação
    $pdo->beginTransaction();
   
    try {
        // 05) Novo SELECT para garantir que os dados estejam atualizados
        $query = "SELECT * FROM clientes WHERE cliente_id = :cliente_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
        $stmt->execute();
       
        $cliente_atualizado = $stmt->fetch(PDO::FETCH_ASSOC);
       
        if ($cliente_atualizado) {
            // 06) Comparar os dados
            if ($cliente_atualizado['nome'] === $nome_novo && $cliente_atualizado['limite'] === $limite_novo) {
                echo "Nenhuma alteração realizada, os dados são iguais aos atuais.";
            } else {
                // 07) Realizar o UPDATE
                $update_query = "UPDATE clientes SET nome = :nome, limite = :limite WHERE cliente_id = :cliente_id";
                $update_stmt = $pdo->prepare($update_query);
                $update_stmt->bindParam(':nome', $nome_novo);
                $update_stmt->bindParam(':limite', $limite_novo);
                $update_stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
                $update_stmt->execute();
               
                // Commit da transação
                $pdo->commit();
               
                echo "Cliente atualizado com sucesso!";
            }
        } else {
            echo "Cliente não encontrado, não foi possível atualizar.";
        }
    } catch (PDOException $e) {
        // Em caso de erro, fazer o rollback
        $pdo->rollBack();
        echo "Erro ao atualizar os dados: " . $e->getMessage();
    }
}
?>


<!-- Formulário para buscar cliente -->
<form method="POST" action="">
    <label for="cliente_id">ID do Cliente:</label><input type="text" name="cliente_id" id="cliente_id">
    <input type="submit" value="Buscar">
</form>
