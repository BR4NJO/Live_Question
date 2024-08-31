<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AnswerFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Liste des réponses possibles
        $possibleAnswers = [
            "Bien", "Mauvais", "Bof", "Excellent", "Pas terrible", 
            "Moyen", "Très bon", "Décevant", "Correct", "Inacceptable"
        ];

        // On récupère toutes les questions existantes
        $questions = $manager->getRepository(Question::class)->findAll();

        foreach ($questions as $question) {
            // Générer un nombre aléatoire de réponses pour chaque question
            $numberOfAnswers = rand(3, 10);

            for ($i = 0; $i < $numberOfAnswers; $i++) {
                $answer = new Answer();
                
                // Sélectionner une réponse aléatoire
                $randomAnswer = $possibleAnswers[array_rand($possibleAnswers)];
                $answer->setContent($randomAnswer . ' - Ceci est la réponse ' . ($i + 1) . ' pour la question ' . $question->getId());
                
                $answer->setDate(new \DateTime());
                $answer->setQuestion($question);
                $answer->setUser($question->getUser()); // Suppose que la réponse est ajoutée par l'utilisateur qui a posé la question

                $manager->persist($answer);
            }
        }

        // Sauvegarder toutes les entités persistées dans la base de données
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            QuestionFixtures::class,
        ];
    }
}
