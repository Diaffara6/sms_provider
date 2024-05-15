<?php

namespace App\Controller\Admin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController {
    #[Route('/index', 'admin.home.dashboard', methods: ['GET'])]
    public function index(): Response {
        return $this->render("admin/home.html.twig");
    }

    #[Route('/send-msg', 'admin.home.send', methods: ['GET'])]
    public function get_form_send_sms(): Response {
        return $this->render("admin/send.html.twig");
    }

    #[Route('/history-msg', 'admin.home.history', methods: ['GET'])]
    public function get_form_history(): Response {
        return $this->render("admin/history.html.twig");
    }

    #[Route('/purchase', 'admin.home.purchase', methods: ['GET'])]
    public function get_form_purchase(): Response {
        return $this->render("admin/purchase.html.twig");
    }


}