<?php

namespace App\Controller;

use App\Factory\DynamicFormFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @var DynamicFormFactory
     */
    private $dynamicFormFactory;

    public function __construct(DynamicFormFactory $dynamicFormFactory)
    {
        $this->dynamicFormFactory = $dynamicFormFactory;
    }

    /**
     * @Route("/", name="app_home")
     */
    public function index(): Response
    {
         $form = $this->dynamicFormFactory->createForm('user_form');

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView()
        ]);
    }
}
