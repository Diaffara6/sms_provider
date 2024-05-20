<?php

namespace App\Controller\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard')]
class DashboardController extends AbstractController {
    #[Route('/index', 'dashboard_index', methods: ['GET'])]
    public function index(): Response {
        return $this->render("dashboard/index.html.twig");
    }

    #[Route('/send-msg', 'dashboard_send_msg', methods: ['GET'])]
    public function get_form_send_sms(): Response {
        return $this->render("dashboard/send.html.twig");
    }

    #[Route('/history-msg', 'dashboard_history', methods: ['GET'])]
    public function get_form_history(): Response {
        return $this->render("dashboard/history.html.twig");
    }

    #[Route('/purchase', 'dashboard_purchase', methods: ['GET'])]
    public function get_form_purchase(): Response {
        return $this->render("dashboard/purchase.html.twig");
    }


}