<?php
require_once 'Model/classes/WebPage.php';
require_once 'Model/classes/Database.php';

class RecipePage extends WebPage
{
    private $db;

    public function __construct($dbFilePath)
    {
        $this->db = new Database($dbFilePath);
        parent::__construct("");
    }

    public function displayBody($sortCriteria = 'name', $searchTerm = '')
    {
        $recipes = $this->db->getRecipes($sortCriteria, $searchTerm);
        $this->body = "
            <h1>Recipes</h1>
            <div class='sort-by'>
                <span>SORT BY</span>
                <select id='sortCriteria' onchange='sortRecipes(this.value)'>
                    <option value='name' " . ($sortCriteria == 'name' ? 'selected' : '') . ">Імені</option>
                    <option value='rating' " . ($sortCriteria == 'rating' ? 'selected' : '') . ">К-ті інгредієнтів</option>
                </select>
            </div>
            <div class='recipe-container'>
        ";
        foreach ($recipes as $recipe) {
            $photoPath = str_replace('../', '', $recipe['foto']);
            $this->body .= "
                <div class='recipe-tile'>
                    <a href='View/recipe.php?id={$recipe['recipe_id']}'>
                        <img src='{$photoPath}' alt='Recipe Image'>
                        <h2>{$recipe['recipe_name']}</h2>
                        <p>{$recipe['recipe_description']}</p>
                    </a>
                </div>
            ";
        }
        $this->body .= "</div>";
        parent::displayBody();
    }
}
