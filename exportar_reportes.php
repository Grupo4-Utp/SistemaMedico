<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$params = [];
$where = '';

if ($fecha_inicio && $fecha_fin) {
    $where = "WHERE c.Fecha_Consulta BETWEEN :fecha_inicio AND :fecha_fin";
    $params = [
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin,
    ];
}

$query = "
    SELECT m.Nombre AS Medico, COUNT(c.ID_Consulta) AS TotalConsultas
    FROM Consulta_Programada c
    JOIN Medico m ON c.ID_Medico = m.ID_Medico
    $where
    GROUP BY m.ID_Medico
    ORDER BY TotalConsultas DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte Consultas');

$sheet->setCellValue('A1', 'Médico');
$sheet->setCellValue('B1', 'Total Consultas Programadas');

$row = 2;
foreach ($reportes as $rep) {
    $sheet->setCellValue('A' . $row, $rep['Medico']);
    $sheet->setCellValue('B' . $row, $rep['TotalConsultas']);
    $row++;
}

$sheet->getStyle('A1:B1')->getFont()->setBold(true);
$sheet->getColumnDimension('A')->setAutoSize(true);
$sheet->getColumnDimension('B')->setAutoSize(true);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_consultas.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
