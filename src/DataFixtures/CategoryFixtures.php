<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;


/*
* Les Fixtures sont un jeu de données de base.
* Elles servent à remplire la BDD juste aprés la création de la BDD juste aprés la création de la BDD,
* pour pouvoir manipuler des données dans mon code => des entités
*/
class CategoryFixtures extends Fixture
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    public function load(ObjectManager $manager): void
    {
       $categories = [
           'politique',
           'Société', 
           'Economie',
            'Santé',
         'Environnement',
         'Sport',
         'Culture'];


        foreach($categories as $category){ 

        $cat = new Category();
    
        $cat->setName($category);
        $cat->setAlias($this->slugger->slug($category));
       
        $manager->persist($cat);

        //on nettoye avec le flush 
        $manager->flush();
    }
}
}
