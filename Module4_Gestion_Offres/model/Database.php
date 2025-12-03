<?php
// model/Database.php

class Database {
    private static $host = "localhost";
   
    private static $db_name = "gestion_offres_db"; // Mettez le nom de votre BDD
    private static $username = "root";             // Laissez "root" par défaut
    private static $password = "";                 // Laissez vide "" par défaut sur XAMPP
    private static $conn;

    public static function getConnection() {
        self::$conn = null;
        try {
            self::$conn = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$db_name, self::$username, self::$password);
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Erreur de connexion à la base de données : " . $exception->getMessage();
        }
        return self::$conn;
    }
}
?>