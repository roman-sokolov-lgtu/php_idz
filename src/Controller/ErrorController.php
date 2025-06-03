<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class ErrorController extends AbstractController
{
    public function show(Request $request, Throwable $exception): Response
    {
        // Получаем код ошибки
        $code = method_exists($exception, 'getStatusCode') 
            ? $exception->getStatusCode() 
            : ($exception->getCode() ?: 500);

        // Для 404 ошибок пытаемся найти похожий маршрут
        if ($code === 404) {
            $path = $request->getPathInfo();
            $cleanPath = trim($path, '/');
            
            // Проверяем различные варианты URL
            $variants = [
                'ord' => '/order/',
                'order' => '/order/',
                'orders' => '/order/',
                'history' => '/order/history',
                'orderhistory' => '/order/history',
                'order-history' => '/order/history',
                'orders-history' => '/order/history',
                'admin' => '/admin/',
                'dashboard' => '/admin/',
                'login' => '/login',
                'signin' => '/login',
                'register' => '/register',
                'signup' => '/register'
            ];

            // Проверяем прямые совпадения
            if (isset($variants[$cleanPath])) {
                return $this->redirect($variants[$cleanPath], 301);
            }

            // Проверяем похожие URL с помощью расстояния Левенштейна
            foreach ($variants as $key => $redirect) {
                if (levenshtein(strtolower($cleanPath), $key) <= 2) {
                    return $this->redirect($redirect, 301);
                }
            }

            // Проверяем русский текст
            if (preg_match('/[А-Яа-яЁё]/u', $cleanPath)) {
                $translitPath = $this->translit($cleanPath);
                foreach ($variants as $key => $redirect) {
                    if (levenshtein(strtolower($translitPath), $key) <= 2) {
                        return $this->redirect($redirect, 301);
                    }
                }
            }
        }

        // Определяем, какой шаблон использовать
        $template = sprintf('bundles/TwigBundle/Exception/error%s.html.twig', $code);
        if (!$this->container->get('twig')->getLoader()->exists($template)) {
            $template = 'bundles/TwigBundle/Exception/error.html.twig';
        }

        return $this->render($template, [
            'status_code' => $code,
            'status_text' => Response::$statusTexts[$code] ?? 'Unknown Error',
            'exception' => $exception,
            'current_path' => $path ?? null
        ], new Response('', $code));
    }

    private function translit(string $string): string
    {
        $converter = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
            'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch',
            'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'
        ];

        return strtr($string, $converter);
    }
} 