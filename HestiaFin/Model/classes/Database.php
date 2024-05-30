<?php

class Database
{
    private $pdo;

    public function __construct($dbFilePath)
    {
        $absolutePath = realpath($dbFilePath);
        if ($absolutePath === false) {
            throw new Exception("Unable to resolve the path to the database file: " . $dbFilePath);
        }

        $this->pdo = new PDO('sqlite:' . $absolutePath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getRecipes($sortCriteria = 'name', $searchTerm = '')
    {
        $orderBy = $sortCriteria === 'rating' ? 'ingredients_count' : 'recipe_name';
        $searchQuery = $searchTerm ? "WHERE r.recipe_name LIKE :searchTerm OR r.recipe_description LIKE :searchTerm" : "";
        $query = "
            SELECT 
                r.recipe_id, 
                r.recipe_name, 
                r.recipe_description, 
                r.foto, 
                (SELECT COUNT(*) FROM Ingredient i WHERE i.recipe_id = r.recipe_id) as ingredients_count
            FROM Recipe r
            $searchQuery
            ORDER BY $orderBy
        ";
        $stmt = $this->pdo->prepare($query);
        if ($searchTerm) {
            $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPDO()
    {
        return $this->pdo;
    }
}
