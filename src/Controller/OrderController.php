<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\DriverRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'app_order_index', methods: ['GET'])]
    public function index(DriverRepository $driverRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Администраторы не могут создавать заказы.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('order/index.html.twig', [
            'drivers' => $driverRepository->findAllActive(),
        ]);
    }

    #[Route('/list', name: 'app_order_list', methods: ['GET'])]
    public function listAll(OrderRepository $orderRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Администраторы не могут просматривать список заказов здесь. Используйте панель администратора.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('order/list.html.twig', [
            'orders' => $orderRepository->findAllOrders(),
        ]);
    }

    #[Route('/create', name: 'app_order_create', methods: ['POST'])]
    public function create(
        Request $request, 
        OrderRepository $orderRepository, 
        DriverRepository $driverRepository
    ): Response {
        try {
        if ($this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Администраторы не могут создавать заказы.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $data = $request->request->all();
            
            // Логируем полученные данные
            error_log('Received order data: ' . print_r($data, true));
        
        // Проверяем наличие всех обязательных полей
        $requiredFields = ['delivery_type', 'client_name', 'city', 'street', 'house', 'phone', 'price'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                    $this->addFlash('error', sprintf('Поле "%s" обязательно для заполнения', $field));
                return $this->redirectToRoute('app_order_index');
            }
        }

        // Проверяем тип доставки
        if (!in_array($data['delivery_type'], ['standard', 'express'], true)) {
            $this->addFlash('error', 'Некорректный тип доставки');
            return $this->redirectToRoute('app_order_index');
        }

        // Проверяем формат цены
        $price = filter_var($data['price'], FILTER_VALIDATE_FLOAT);
        if ($price === false || $price <= 0 || $price >= 1000000) {
            $this->addFlash('error', 'Некорректная цена');
            return $this->redirectToRoute('app_order_index');
        }

        // Проверяем формат телефона
        $phone = preg_replace('/\D/', '', $data['phone']);
        if (!preg_match('/^7\d{10}$/', $phone)) {
            $this->addFlash('error', 'Некорректный формат телефона');
            return $this->redirectToRoute('app_order_index');
        }

        // Проверяем длину и формат имени клиента
        if (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s-]{2,50}$/u', $data['client_name'])) {
            $this->addFlash('error', 'Некорректное имя клиента');
            return $this->redirectToRoute('app_order_index');
        }

        // Проверяем город
        if (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s-]{2,50}$/u', $data['city'])) {
            $this->addFlash('error', 'Некорректное название города');
            return $this->redirectToRoute('app_order_index');
        }

        // Проверяем улицу
        if (!preg_match('/^[а-яА-ЯёЁa-zA-Z0-9\s-]{2,100}$/u', $data['street'])) {
            $this->addFlash('error', 'Некорректное название улицы');
            return $this->redirectToRoute('app_order_index');
        }

        // Проверяем номер дома
            if (!preg_match('/^(?=.*[0-9])[а-яА-ЯёЁa-zA-Z0-9\/\-]{1,10}$/u', $data['house'])) {
            $this->addFlash('error', 'Некорректный номер дома');
            return $this->redirectToRoute('app_order_index');
        }

        // Проверяем номер квартиры, если указан
        if (isset($data['apartment']) && $data['apartment'] !== '') {
            if (!preg_match('/^[0-9]{1,10}$/', $data['apartment'])) {
                $this->addFlash('error', 'Некорректный номер квартиры');
                return $this->redirectToRoute('app_order_index');
            }
        }

        $order = new Order();
        $order->setDeliveryType($data['delivery_type'])
            ->setClientName($data['client_name'])
            ->setCity($data['city'])
            ->setStreet($data['street'])
            ->setHouse($data['house'])
            ->setApartment($data['apartment'] ?? null)
            ->setPhone('+' . $phone)
            ->setPrice((string)$price)
            ->setUserId($user->getId());

        try {
            $orderRepository->save($order, true);
            $this->addFlash('success', 'Заказ успешно создан');
            return $this->redirectToRoute('app_order_success');
        } catch (\Exception $e) {
                error_log('Error saving order: ' . $e->getMessage());
            $this->addFlash('error', 'Произошла ошибка при создании заказа. Пожалуйста, попробуйте снова.');
                return $this->redirectToRoute('app_order_index');
            }
        } catch (\Exception $e) {
            error_log('Unexpected error in order creation: ' . $e->getMessage());
            $this->addFlash('error', 'Произошла неожиданная ошибка. Пожалуйста, попробуйте снова.');
            return $this->redirectToRoute('app_order_index');
        }
    }

    #[Route('/success', name: 'app_order_success', methods: ['GET'])]
    public function success(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('order/success.html.twig');
    }

    #[Route('/history', name: 'app_order_history', methods: ['GET'])]
    public function history(OrderRepository $orderRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Администраторы не могут просматривать историю заказов здесь. Используйте панель администратора.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('order/history.html.twig', [
            'orders' => $orderRepository->findByUserId($user->getId()),
        ]);
    }
} 