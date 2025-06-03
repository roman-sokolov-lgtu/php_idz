<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DriverRepository;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(OrderRepository $orderRepository, UserRepository $userRepository): Response
    {
        // Проверяем, что пользователь имеет роль админа
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/dashboard.html.twig', [
            'orders' => $orderRepository->findAllOrders(),
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/orders', name: 'admin_orders')]
    public function orders(OrderRepository $orderRepository, DriverRepository $driverRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/orders.html.twig', [
            'orders' => $orderRepository->findAllOrders(),
            'drivers' => $driverRepository->findBy(['is_active' => true]),
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/{id}/toggle-role', name: 'admin_user_toggle_role', methods: ['POST'])]
    public function toggleUserRole(
        int $id, 
        UserRepository $userRepository, 
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'Пользователь не найден');
            return $this->redirectToRoute('admin_users');
        }

        // Проверяем, не пытается ли админ понизить свою роль
        if ($user === $this->getUser() && $user->getRole() === 'admin') {
            $this->addFlash('error', 'Вы не можете понизить роль своего собственного аккаунта');
            return $this->redirectToRoute('admin_users');
        }

        // Если пытаемся повысить до админа, проверяем наличие заказов
        if ($user->getRole() === 'user') {
            $userOrders = $orderRepository->findByUserId($user->getId());
            if (count($userOrders) > 0) {
                $this->addFlash('error', 'Невозможно повысить пользователя до администратора, так как у него есть заказы');
                return $this->redirectToRoute('admin_users');
            }
        }

        // Переключаем роль пользователя
        $user->setRole($user->getRole() === 'admin' ? 'user' : 'admin');
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Роль пользователя успешно изменена');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'Пользователь не найден');
            return $this->redirectToRoute('admin_users');
        }

        // Проверяем, не пытается ли админ удалить сам себя
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Вы не можете удалить свой собственный аккаунт');
            return $this->redirectToRoute('admin_users');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Пользователь успешно удален');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/orders/{id}/delete', name: 'admin_order_delete', methods: ['POST'])]
    public function deleteOrder(int $id, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $order = $orderRepository->find($id);
        if (!$order) {
            $this->addFlash('error', 'Заказ не найден');
            return $this->redirectToRoute('admin_orders');
        }

        $entityManager->remove($order);
        $entityManager->flush();

        $this->addFlash('success', 'Заказ успешно удален');
        return $this->redirectToRoute('admin_orders');
    }

    #[Route('/orders/{id}/assign-driver', name: 'admin_order_assign_driver', methods: ['POST'])]
    public function assignDriver(
        int $id, 
        Request $request,
        OrderRepository $orderRepository, 
        EntityManagerInterface $entityManager,
        DriverRepository $driverRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $order = $orderRepository->find($id);
        if (!$order) {
            $this->addFlash('error', 'Заказ не найден');
            return $this->redirectToRoute('admin_orders');
        }

        $driverId = $request->request->get('driver_id');
        if ($driverId) {
            $driver = $driverRepository->find($driverId);
            if ($driver && $driver->isActive()) {
                $order->setDriver($driver);
                $entityManager->flush();
                $this->addFlash('success', 'Водитель успешно назначен');
            } else {
                $this->addFlash('error', 'Выбранный водитель не найден или неактивен');
            }
        } else {
            $order->setDriver(null);
            $entityManager->flush();
            $this->addFlash('success', 'Водитель успешно отменен');
        }

        return $this->redirectToRoute('admin_orders');
    }
} 