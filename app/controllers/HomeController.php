<?php

class HomeController extends Controller
{
    private $db;
    private $Dashboard;

    public function __construct()
    {
        $this->db = new Database();
        $this->Dashboard = $this->model('Dashboard');
    }

    // Home page - Dashboard
    public function index()
    {
        // Get current user
        $user = $this->getCurrentUser(); 
        // Get role_id from session or user object
        // Adjust this based on how you store role_id in your app
        $roleId = $user['role_id'];
        // Get cards based on user's role
        if($roleId) { 
            $cards = $this->Dashboard->getCardsByRole($roleId,1);
        } else {
            // Fallback: Get all active cards if no role found
            $cards = $this->Dashboard->getAllActiveCards();
        }

        // Get data (counts) for each card
        $cardData = $this->Dashboard->getCardData($cards);

        // Legacy counts for backward compatibility
        $counts = $this->Dashboard->getCounts();

        $data = [
            'title'       => 'Welcome to ' . APP_NAME,
            'description' => 'This is the home page',
            'user'        => $user,
            'cards'       => $cards,      // Dynamic cards based on role
            'cardData'    => $cardData,   // Card counts/data
            'counts'      => $counts,     // Legacy support
        ];
        $this->viewWithLayout('dashboard', $data);
    }

    // About page
    public function about()
    {
        $data = [
            'title'   => 'About Us',
            'version' => APP_VERSION
        ];

        $this->viewWithLayout('home/about', $data);
    }

    // Contact page
    public function contact()
    {
        if ($this->isPost()) {
            // Verify CSRF token
            $token = $this->getPost('csrf_token');
            if (!$this->verifyCsrfToken($token)) {
                $this->setFlash('contact', 'Invalid request', 'alert alert-danger');
                $this->redirect('home/contact');
                return;
            }

            // Sanitize inputs
            $name    = $this->sanitize($this->getPost('name'));
            $email   = $this->sanitize($this->getPost('email'));
            $message = $this->sanitize($this->getPost('message'));

            // Validate
            $errors = [];
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            if (!$this->validateEmail($email)) {
                $errors[] = 'Valid email is required';
            }
            if (empty($message)) {
                $errors[] = 'Message is required';
            }

            if (empty($errors)) {
                // Process form (send email, save to database, etc.)
                $this->setFlash('contact', 'Thank you! Your message has been sent.', 'alert alert-success');
                $this->redirect('home/contact');
            } else {
                $data = [
                    'title'      => 'Contact Us',
                    'errors'     => $errors,
                    'name'       => $name,
                    'email'      => $email,
                    'message'    => $message,
                    'csrf_token' => $this->generateCsrfToken()
                ];
                $this->viewWithLayout('home/contact', $data);
            }
        } else {
            $data = [
                'title'      => 'Contact Us',
                'csrf_token' => $this->generateCsrfToken()
            ];
            $this->viewWithLayout('home/contact', $data);
        }
    }
}