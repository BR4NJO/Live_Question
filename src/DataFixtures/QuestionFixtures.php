<?php

namespace App\DataFixtures;

use App\Entity\Question;
use App\Entity\Answer;
use App\Entity\User;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class QuestionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        $users = $manager->getRepository(User::class)->findAll();
        
        if (count($users) === 0) {
            echo "Aucun utilisateur trouvé. Veuillez ajouter des utilisateurs à la base de données avant de charger les fixtures.";
            return;
        }
        
        $categories = $manager->getRepository(Category::class)->findAll();
        
        if (count($categories) === 0) {
            echo "Aucune catégorie trouvée. Veuillez ajouter des catégories à la base de données avant de charger les fixtures.";
            return;
        }

        foreach ($users as $user) {
            $numberOfQuestions = rand(1, 5);

            for ($i = 0; $i < $numberOfQuestions; $i++) {
                $question = new Question();
                $question->setTitle($faker->sentence(6, true));
                $question->setContent($faker->paragraph(3, true));
                $question->setDate($faker->dateTimeBetween('-1 year', 'now'));
                $question->setPicture($faker->imageUrl(640, 480, 'abstract', true, 'Faker'));

                $numberOfCategories = rand(1, 3);
                $randomCategories = (array)array_rand($categories, $numberOfCategories);
                
                foreach ($randomCategories as $index) {
                    $question->addCategory($categories[$index]);
                }

                $question->setUser($user);

                $manager->persist($question);

                $possibleAnswers = [
                    "Bien", "Mauvais", "Bof", "Excellent", "Pas terrible", 
                    "Moyen", "Très bon", "Décevant", "Correct", "Inacceptable"
                ];
                
                $numberOfAnswers = rand(3, 10);
                for ($j = 0; $j < $numberOfAnswers; $j++) {
                    $answer = new Answer();
                    $randomAnswer = $possibleAnswers[array_rand($possibleAnswers)];
                    $answer->setContent($randomAnswer . ' - Ceci est la réponse ' . ($j + 1) . ' pour la question ' . $question->getId());
                    $answer->setDate($faker->dateTimeBetween('-1 year', 'now'));
                    $answer->setQuestion($question);
                    $answer->setUser($user);

                    $manager->persist($answer);
                }
            }
        }

        $manager->flush();
    }
}
