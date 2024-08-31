<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\Category;
use App\Form\QuestionType;
use App\Entity\Answer;
use App\Form\AnswerType;
use App\Repository\QuestionRepository;
use App\Repository\UserRepository;

class HomeController extends AbstractController
{#[Route('/', name: 'home')]
    public function home(EntityManagerInterface $manager, QuestionRepository $questionRepository, UserRepository $userRepository): Response
    {
        // La question la plus populaire
        $query = $manager->createQuery(
            'SELECT q, c, COUNT(a.id) AS answer_count
             FROM App\Entity\Question q
             LEFT JOIN q.answers a
             LEFT JOIN q.categories c
             GROUP BY q.id
             ORDER BY answer_count DESC'
        )
        ->setMaxResults(1);

        $result = $query->getOneOrNullResult();
        $mostAnsweredQuestion = $result ? $result[0] : null;
        $answerCount = $result ? $result['answer_count'] : 0;

        // Les 3 dernières questions
        $latestQuestions = $questionRepository->createQueryBuilder('q')
            ->leftJoin('q.answers', 'a')
            ->leftJoin('q.categories', 'c')
            ->addSelect('c')
            ->addSelect('COUNT(a.id) AS answer_count')
            ->groupBy('q.id')
            ->orderBy('q.date', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        // Les auteurs les plus actifs
        $topUsers = $manager->createQuery(
            'SELECT u.pseudo, COUNT(q.id) AS question_count
             FROM App\Entity\User u
             LEFT JOIN u.questions q
             GROUP BY u.id
             ORDER BY question_count DESC'
        )
        ->setMaxResults(5)
        ->getResult();

        // Questions dans la catégorie Film
        $filmsCategory = $manager->getRepository(Category::class)->findOneBy(['name' => 'Film']);
        $questionsInFilmsCategory = $questionRepository->createQueryBuilder('q')
            ->leftJoin('q.categories', 'c')
            ->where('c.id = :categoryId')
            ->setParameter('categoryId', $filmsCategory ? $filmsCategory->getId() : 0)
            ->orderBy('q.date', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('home/index.html.twig', [
            'mostAnsweredQuestion' => $mostAnsweredQuestion,
            'answerCount' => $answerCount,
            'latestQuestions' => $latestQuestions,
            'topUsers' => $topUsers,
            'questionsInFilmsCategory' => $questionsInFilmsCategory,
        ]);
    }
    
    

    #[Route('/question', name: 'question')]
    public function question(Request $request, EntityManagerInterface $manager): Response
    {
        
        $selectedUser = $request->query->get('user');
        $selectedCategory = $request->query->get('category');

        
        $queryBuilder = $manager->getRepository(Question::class)->createQueryBuilder('q');

        
        if ($selectedUser) {
            $queryBuilder->andWhere('q.user = :user')
                         ->setParameter('user', $selectedUser);
        }

        
        if ($selectedCategory) {
            $queryBuilder->join('q.categories', 'c')
                         ->andWhere('c.id = :category')
                         ->setParameter('category', $selectedCategory);
        }

        
        $questions = $queryBuilder->getQuery()->getResult();

        
        $users = $manager->getRepository(User::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();

        return $this->render('home/question.html.twig', [
            'questions' => $questions,
            'users' => $users,
            'categories' => $categories,
            'selectedUser' => $selectedUser,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    #[Route('/question/{id}', name: 'view_question')]
    public function viewQuestion(int $id, Request $request, EntityManagerInterface $manager): Response
    {
        $question = $manager->getRepository(Question::class)->find($id);
        if (!$question) {
            throw $this->createNotFoundException('La question n\'existe pas.');
        }

        // Création du formulaire de réponse
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if ($user) {
                $answer->setUser($user);
                $answer->setQuestion($question);

                $manager->persist($answer);
                $manager->flush();

                $this->addFlash('success', 'Votre réponse a été ajoutée.');
                return $this->redirectToRoute('question');
            } else {
                $this->addFlash('error', 'Vous devez être connecté pour répondre.');
            }
        }

        return $this->render('home/answer.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/author', name: 'author')]
    public function author(EntityManagerInterface $entityManager): Response
    {
        // Obtenir tous les utilisateurs qui ont posté une question
        $authors = $entityManager->createQuery(
            'SELECT DISTINCT u 
             FROM App\Entity\User u 
             JOIN u.questions q'
        )->getResult();

        return $this->render('home/author.html.twig', [
            'authors' => $authors,
        ]);
    }

    #[Route('/author/{id}', name: 'author_questions')]
    public function questions(int $id, EntityManagerInterface $entityManager): Response
    {

        $author = $entityManager->getRepository(User::class)->find($id);


        if (!$author) {
            throw $this->createNotFoundException('Auteur non trouvé');
        }

        // Toutes les questions de l'utilisateur
        $questions = $entityManager->createQuery(
            'SELECT q
             FROM App\Entity\Question q
             WHERE q.user = :author'
        )->setParameter('author', $author)
         ->getResult();

        return $this->render('home/author_question.html.twig', [
            'author' => $author,
            'questions' => $questions,
        ]);
    }

    #[Route('/new_question', name: 'new_question')]
    public function new_question(Request $request, EntityManagerInterface $manager): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if ($user) {
                $question->setUser($user);
            }

            $file = $form->get('picture')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads',
                    $fileName
                );
                $question->setPicture($fileName);
            }

            $manager->persist($question);
            $manager->flush();

            $this->addFlash('success', 'Question envoyée avec succès.');

            return $this->redirectToRoute('home');
        }

        return $this->render('home/new_question.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    
}
