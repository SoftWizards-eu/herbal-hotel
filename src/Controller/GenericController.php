<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller obsługujący panel konta użytkownika
 */
class GenericController extends AbstractController
{
    /**
     * @Route("/konto", name="app_account")
     */
    public function index(Request $request): Response
    {
        
        
        return $this->render('account/dashboard.html.twig', [
            // Tutaj przekazywanie danych do widoku
        ]);
    }
    
    /**
     * @Route("/konto/zamowienia", name="app_account_orders")
     */
    public function orders(Request $request): Response
    {
        // Tutaj w przyszłości będzie pobieranie zamówień użytkownika
        
        return $this->render('account/orders.html.twig', [
            // Tutaj przekazywanie danych do widoku
        ]);
    }
    
    /**
     * @Route("/konto/szkolenia", name="app_account_trainings")
     */
    public function trainings(Request $request): Response
    {
        // Tutaj w przyszłości będzie pobieranie szkoleń użytkownika
        
        return $this->render('account/trainings.html.twig', [
            // Tutaj przekazywanie danych do widoku
        ]);
    }

    /**
     * @Route("/zakup", name="purchase")
     */
    public function purchase(Request $request): Response
    {
        // Tutaj w przyszłości będzie obsługa zakupu
        return $this->render('nodes/training_purchase.html.twig', [
            // Tutaj przekazywanie danych do widoku
        ]);
    }
    /**
     * @Route("/test", name="test")
     */
    public function test(Request $request): Response
    {
        // Tutaj w przyszłości będzie obsługa zakupu
        return $this->render('nodes/test.html.twig', [
            // Tutaj przekazywanie danych do widoku
        ]);
    }
}