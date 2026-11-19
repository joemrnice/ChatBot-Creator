<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\SubscriptionService;
use App\Core\Database;

class HomeController
{
    private SubscriptionService $subscriptionService;

    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService();
    }

    public function index(Request $request): Response
    {
        $plans = $this->subscriptionService->getAllActivePlans();
        $templates = Database::fetchAll("SELECT * FROM chatbot_templates WHERE is_featured = 1 LIMIT 4");

        return Response::view('public/home', [
            'title' => 'ChatBot Creator Studio - Create Powerful Chatbots Without Coding',
            'plans' => $plans,
            'templates' => $templates,
        ], 'layouts/public');
    }

    public function features(Request $request): Response
    {
        return Response::view('public/features', [
            'title' => 'Features - ChatBot Creator Studio',
        ], 'layouts/public');
    }

    public function howItWorks(Request $request): Response
    {
        return Response::view('public/how-it-works', [
            'title' => 'How It Works - ChatBot Creator Studio',
        ], 'layouts/public');
    }

    public function pricing(Request $request): Response
    {
        $plans = $this->subscriptionService->getAllActivePlans();
        return Response::view('public/pricing', [
            'title' => 'Subscription Pricing - ChatBot Creator Studio',
            'plans' => $plans,
        ], 'layouts/public');
    }

    public function about(Request $request): Response
    {
        return Response::view('public/about', [
            'title' => 'About Us - ChatBot Creator Studio',
        ], 'layouts/public');
    }

    public function contact(Request $request): Response
    {
        return Response::view('public/contact', [
            'title' => 'Contact Us - ChatBot Creator Studio',
        ], 'layouts/public');
    }

    public function faq(Request $request): Response
    {
        return Response::view('public/faq', [
            'title' => 'Frequently Asked Questions - ChatBot Creator Studio',
        ], 'layouts/public');
    }

    public function privacy(Request $request): Response
    {
        return Response::view('public/privacy', [
            'title' => 'Privacy Policy - ChatBot Creator Studio',
        ], 'layouts/public');
    }

    public function terms(Request $request): Response
    {
        return Response::view('public/terms', [
            'title' => 'Terms of Service - ChatBot Creator Studio',
        ], 'layouts/public');
    }
}
