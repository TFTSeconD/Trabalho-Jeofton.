
<?php
// db.php
// Configuração simples para XAMPP padrão
$host = 'localhost';
$dbname = 'estoque_simples';
$username = 'root';
$password = ''; // No XAMPP a senha padrão é vazia

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Configurar para mostrar erros (importante para debugar se der problema)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
