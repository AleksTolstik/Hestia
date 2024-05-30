document.addEventListener('DOMContentLoaded', function() {
    const favoriteButton = document.querySelector('.favorite-button');
    favoriteButton.addEventListener('click', function() {
        const recipeId = this.getAttribute('data-recipe-id');
        fetch('../Control/add_to_favorites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    recipe_id: recipeId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Recipe added to favorites!');
                    favoriteButton.style.color = 'red';
                } else {
                    alert(data.message || 'Failed to add recipe to favorites.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
});
