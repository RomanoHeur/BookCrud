<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{

    #[Route('/', name: 'crud_books', methods: ['GET', 'POST'])]
    function index(Request $request, EntityManagerInterface $entityManager, BookRepository $bookRepository): Response
    {
        //form for creating a book
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('crud_books');
        }

        //showing all the books in the table
        $books = $bookRepository->findAll();

        //Form for editing the modal
        $editForm = [];

        foreach ($books as $book) {
            $editForm[$book->getId()] = $this->createForm(BookType::class, $book)->createView();
        }

        return $this->render('index.html.twig', [
            'books' => $books,
            'form' => $form->createView(),
            'editForm' => $editForm,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit_book', methods: ['POST'])]
    public function editBook(Request $request, EntityManagerInterface $entityManager, Book $book): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            return $this->redirectToRoute('crud_books');
        }

        // Omgaan met fouten, mogelijk terugkeren naar een pagina met formuliergegevens
        return $this->redirectToRoute('crud_books');
    }


    #[Route('/{id}', name: 'delete_book', methods: ['POST'])]
    function deleteBook(EntityManagerInterface $entityManager, int $id): Response
    {
        $foundBook = $entityManager->getRepository(Book::class)->find($id);


        if(!$foundBook) {
            $this->addFlash('error', 'Het boek kon niet worden gevonden.');

            return $this->redirectToRoute('crud_books');
        }

        $entityManager->remove($foundBook);
        $entityManager->flush();

        return $this->redirectToRoute('crud_books');
    }

}