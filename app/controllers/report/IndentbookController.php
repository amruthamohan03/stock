<?php

class IndentbookController extends Controller
{

    public function index()
    {
        $db = new Database();

        $data = [
            'title' => 'Indent Book Report'
        ];

        $this->viewWithLayout('reports/indent_report', $data);
    }
    public function fetchReport()
    {
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $type = $_GET['type'] ?? null;

        $where = "WHERE im.display='Y'";

        if ($from && $to)
            $where .= " AND im.indent_date BETWEEN '$from' AND '$to'";

        if ($type && $type != 'ALL')
            $where .= " AND im.item_type='$type'";

        $sql = "
        SELECT 
            im.id,
            im.indent_no,
            im.indent_date,
            im.item_type,
            ii.item_description,
            ii.qty_intended,
            ii.qty_passed,
            ii.qty_issued
        
        FROM indent_master_t im
        LEFT JOIN indent_item_t ii ON ii.indent_id = im.id
        
        $where
        ORDER BY im.indent_date DESC
    ";

        $rows = $db->customQuery($sql);

        echo json_encode([
            "success" => true,
            "data" => $rows
        ]);
    }
    public function exportPdf()
    {
        require_once APP_ROOT . '/vendor/autoload.php';

        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $type = $_GET['type'] ?? null;

        $where = "WHERE im.display='Y'";

        if ($from && $to)
            $where .= " AND im.indent_date BETWEEN '$from' AND '$to'";

        if ($type && $type != 'ALL')
            $where .= " AND im.item_type='$type'";

        $rows = $db->customQuery("
        SELECT 
            im.id,
            im.indent_no,
            im.indent_date,
            im.item_type,
            ii.item_description,
            ii.qty_intended,
            ii.qty_passed,
            ii.qty_issued
        FROM indent_master_t im
        LEFT JOIN indent_item_t ii ON ii.indent_id = im.id
        $where
        ORDER BY im.indent_date DESC, im.id DESC, ii.sl_no ASC
    ");

        /* ============================
           GROUP DATA BY INDENT
        ============================ */
        $grouped = [];

        foreach ($rows as $r) {
            $grouped[$r['id']]['header'] = [
                'indent_no' => $r['indent_no'],
                'indent_date' => $r['indent_date'],
                'item_type' => $r['item_type']
            ];
            $grouped[$r['id']]['items'][] = $r;
        }

        ob_start();
        ?>

        <style>
            body {
                font-family: DejaVu Sans;
                font-size: 12px
            }

            h2 {
                text-align: center;
                margin-bottom: 5px
            }

            .header-box {
                border: 1px solid #000;
                padding: 8px;
                margin-top: 15px;
                background: #f2f2f2;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 5px
            }

            th,
            td {
                border: 1px solid #333;
                padding: 5px
            }

            th {
                background: #222;
                color: #fff
            }

            .small {
                font-size: 11px;
                color: #555
            }
        </style>

        <h2>Indent Book </h2>
        <div class="small">Generated: <?= date('d-m-Y H:i') ?></div>

        <?php foreach ($grouped as $g):
            $h = $g['header'];
            ?>

            <!-- INDENT HEADER -->
            <div class="header-box">
                <b>Indent No:</b> <?= $h['indent_no'] ?>
                &nbsp;&nbsp;&nbsp;
                <b>Date:</b> <?= date('d-m-Y', strtotime($h['indent_date'])) ?>
                &nbsp;&nbsp;&nbsp;
                <b>Type:</b> <?= $h['item_type'] == 'C' ? 'Consumable' : 'Non-Consumable' ?>
            </div>

            <!-- ITEMS TABLE -->
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Intended</th>
                        <th>Passed</th>
                        <th>Issued</th>
                    </tr>
                </thead>
                <tbody>

                    <?php $i = 1;
                    foreach ($g['items'] as $it): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $it['item_description'] ?></td>
                            <td><?= $it['qty_intended'] ?></td>
                            <td><?= $it['qty_passed'] ?></td>
                            <td><?= $it['qty_issued'] ?></td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>

        <?php endforeach; ?>

        <?php
        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Indent_Report.pdf", ["Attachment" => false]);
    }
}