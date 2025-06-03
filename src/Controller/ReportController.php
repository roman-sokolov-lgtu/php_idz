<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use TCPDF;

#[Route('/report')]
#[IsGranted('ROLE_ADMIN')]
class ReportController extends AbstractController
{
    #[Route('/', name: 'app_report_form', methods: ['GET'])]
    public function form(): Response
    {
        return $this->render('report/form.html.twig');
    }

    #[Route('/generate', name: 'app_report_generate', methods: ['POST'])]
    public function generate(Request $request, Connection $connection): Response
    {
        $from = $request->request->get('from_date');
        $to = $request->request->get('to_date');

        if (!$from || !$to) {
            $this->addFlash('error', 'Неверные данные');
            return $this->redirectToRoute('app_report_form');
        }

        $results = $connection->createQueryBuilder()
            ->select('u.username', 'SUM(o.price) as total')
            ->from('users', 'u')
            ->leftJoin('u', 'orders', 'o', 'u.id = o.user_id')
            ->where('o.created_at BETWEEN :from AND :to')
            ->andWhere('u.role != :admin_role')
            ->groupBy('u.id', 'u.username')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('admin_role', 'admin')
            ->executeQuery()
            ->fetchAllAssociative();

        $totalSpent = 0;
        foreach ($results as $row) {
            $totalSpent += (float) $row['total'];
        }

        $totalClients = $connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('users')
            ->where('role != :admin_role')
            ->setParameter('admin_role', 'admin')
            ->executeQuery()
            ->fetchOne();

        return $this->render('report/result.html.twig', [
            'results' => $results,
            'totalClients' => $totalClients,
            'from_date' => $from,
            'to_date' => $to,
            'totalSpent' => $totalSpent
        ]);
    }

    #[Route('/download', name: 'app_report_download', methods: ['GET'])]
    public function download(Request $request, Connection $connection): Response
    {
        $from = $request->query->get('from');
        $to = $request->query->get('to');

        if (!$from || !$to) {
            $this->addFlash('error', 'Не указан период для отчёта');
            return $this->redirectToRoute('app_report_form');
        }

        $results = $connection->createQueryBuilder()
            ->select('u.username', 'IFNULL(SUM(o.price), 0) as total')
            ->from('users', 'u')
            ->leftJoin('u', 'orders', 'o', 'u.id = o.user_id')
            ->where('u.role != :admin_role')
            ->andWhere('o.created_at BETWEEN :from AND :to')
            ->groupBy('u.id', 'u.username')
            ->orderBy('total', 'DESC')
            ->setParameter('admin_role', 'admin')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->executeQuery()
            ->fetchAllAssociative();

        $totalSpent = 0;
        foreach ($results as $row) {
            $totalSpent += (float) $row['total'];
        }

        $totalClients = $connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('users')
            ->where('role != :admin_role')
            ->setParameter('admin_role', 'admin')
            ->executeQuery()
            ->fetchOne();

        $pdf = new TCPDF();
        $pdf->SetCreator('MyApp');
        $pdf->SetAuthor('Отчёт');
        $pdf->SetTitle('Отчёт по клиентам');
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        $pdf->SetFont('dejavusans', '', 14);
        $pdf->Cell(0, 15, 'Отчёт по клиентам', 0, 1, 'C');
        $pdf->SetFont('', '', 12);
        $pdf->Cell(0, 10, 'Период: ' . $from . ' - ' . $to, 0, 1);

        $pdf->SetFont('', 'B', 12);
        $pdf->Cell(15, 10, '№', 1, 0, 'C');
        $pdf->Cell(90, 10, 'Имя пользователя', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Потрачено (р)', 1, 1, 'C');

        $pdf->SetFont('', '', 12);
        $i = 1;
        foreach ($results as $row) {
            $pdf->Cell(15, 10, $i++, 1, 0, 'C');
            $pdf->Cell(90, 10, $row['username'], 1, 0);
            $pdf->Cell(40, 10, $row['total'], 1, 1, 'R');
        }

        $pdf->Ln(5);
        $pdf->SetFont('', 'B', 12);
        $pdf->Cell(0, 10, "Всего клиентов в базе: $totalClients", 0, 1);
        $pdf->Cell(0, 10, "Суммарные траты всех пользователей: $totalSpent р", 0, 1);

        return new Response(
            $pdf->Output('report.pdf', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="report.pdf"'
            ]
        );
    }
} 