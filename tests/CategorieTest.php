<?php
namespace App\Tests\Entity;

use App\Entity\Categorie;
use PHPUnit\Framework\TestCase;

class CategorieTest extends TestCase
{
    public function testCategorieEntity()
    {
        // Création d'un objet Categorie
        $categorie = new Categorie();

        // Définition des valeurs
        $categorie->setDescription('Science-Fiction');

        // Vérification avec les getters
        $this->assertEquals('Science-Fiction', $categorie->getDescription());
    }
}


