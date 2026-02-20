<?php
/**
 * FILE: app/controllers/BookController.php
 *
 * Unified Register Report Controller
 * — Institution & Department resolved from DB (college_t, department_master_t)
 * — Academic Year auto-derived from date period (Kerala June–May cycle)
 * — No principal name (removed)
 *
 * Routes:
 *   GET  book/                           → index()
 *   GET  book/getDepartments             → getDepartments()
 *   GET  book/fetchIndentBook            → fetchIndentBook()
 *   GET  book/fetchStockBook             → fetchStockBook()
 *   GET  book/fetchDayBook               → fetchDayBook()
 *   GET  book/exportPdf/{book_type}      → exportPdf($bookType)
 */
class BookController extends Controller
{
    /* ─────────────────────────────────────────────
       INDEX  — render view with institution list
    ───────────────────────────────────────────── */
    public function index()
    {
        $db = new Database();
        $institutions = $db->selectData('college_t', 'id, college_name', ['display' => 'Y']);

        $data = [
            'title' => 'Stock Register — Report Print',
            'institutions' => $institutions ?: [],
        ];

        $this->viewWithLayout('reports/book_view', $data);
    }

    /* ─────────────────────────────────────────────
       GET DEPARTMENTS  (AJAX cascade)
    ───────────────────────────────────────────── */
    public function getDepartments()
    {
        header('Content-Type: application/json');
        $db = new Database();
        $inst = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;

        $rows = $inst
            ? $db->customQuery("
                SELECT id, department_name
                FROM department_master_t
                WHERE display='Y' AND (college_id=$inst OR college_id=0)
                ORDER BY department_name")
            : $db->selectData('department_master_t', 'id, department_name', ['display' => 'Y']);

        echo json_encode(['success' => true, 'data' => $rows ?: []]);
        exit;
    }

    /* ══════════════════════════════════════════════════════════════
       SHARED: build WHERE clause for institution + department
    ══════════════════════════════════════════════════════════════ */

    /**
     * Adds institution/department filters to the WHERE string.
     * For indent_master_t  → alias = 'im'
     * For stock_transaction_t / daybook_master_t  → alias = 'dm' or 'st'
     * The JOIN to college_t is 'col', to department_master_t is 'dep'.
     */
    private function _instDeptWhere(string &$where, string $masterAlias): void
    {
        $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
        $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;

        if ($instId > 0)
            $where .= " AND {$masterAlias}.institution_id = $instId";
        if ($deptId > 0)
            $where .= " AND {$masterAlias}.department_id  = $deptId";
    }

    /**
     * Returns institution & department names from DB given IDs.
     */
    private function _resolveInstDept(Database $db): array
    {
        $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
        $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;

        $college = '';
        $dept = '';

        if ($instId > 0) {
            $r = $db->selectData('college_t', 'college_name', ['id' => $instId]);
            $college = $r[0]['college_name'] ?? '';
        }
        if ($deptId > 0) {
            $r = $db->selectData('department_master_t', 'department_name', ['id' => $deptId]);
            $dept = $r[0]['department_name'] ?? '';
        }

        return [$college, $dept];
    }

    /**
     * Compute academic year from a date string.
     * Kerala polytechnic: June–May cycle.
     * Month >= 6  →  YYYY-YY+1  (e.g. 2024-25)
     * Month < 6   →  YYYY-1-YY  (e.g. 2023-24)
     */
    private function _academicYear(?string $fromDate): string
    {
        if (!$fromDate)
            return '';
        $month = (int) date('n', strtotime($fromDate));
        $year = (int) date('Y', strtotime($fromDate));

        return $month >= 6
            ? $year . '-' . substr((string) ($year + 1), -2)
            : ($year - 1) . '-' . substr((string) $year, -2);
    }

    /* ══════════════════════════════════════════════════════════════
       JSON FETCH APIs
    ══════════════════════════════════════════════════════════════ */

    public function fetchIndentBook()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $item_type = $_GET['item_type'] ?? 'ALL';
        $eq_type = $_GET['eq_type'] ?? 'ALL';
        $book_no = $_GET['book_no'] ?? null;
        $status = $_GET['status'] ?? 'ALL';

        $where = "WHERE im.display='Y'";
        if ($from && $to)
            $where .= " AND im.indent_date BETWEEN '$from' AND '$to'";
        if ($item_type !== 'ALL')
            $where .= " AND im.item_type='$item_type'";
        if ($book_no && is_numeric($book_no))
            $where .= " AND im.book_no=" . (int) $book_no;
        if ($status !== 'ALL')
            $where .= " AND im.status='$status'";
        if ($eq_type === 'FURNITURE')
            $where .= " AND imt.category_id=1";
        elseif ($eq_type === 'ELECTRONIC')
            $where .= " AND imt.category_id=2";
        elseif ($eq_type === 'OTHERS')
            $where .= " AND (imt.category_id NOT IN (1,2) OR imt.category_id IS NULL)";

        $this->_instDeptWhere($where, 'im');

        $rows = $db->customQuery("
            SELECT im.id, im.book_no, im.indent_no,
                   DATE_FORMAT(im.indent_date,'%d-%m-%Y') AS indent_date,
                   im.item_type, im.status, im.purpose,
                   im.institution_id, im.department_id,
                   col.college_name,
                   dep.department_name,
                   ii.sl_no, ii.item_description, ii.item_purpose,
                   ii.qty_intended, ii.qty_passed, ii.qty_issued, ii.remarks,
                   imt.item_name, mk.make_name, md.model_name
            FROM indent_master_t im
            LEFT JOIN college_t           col ON col.id  = im.institution_id
            LEFT JOIN department_master_t dep ON dep.id  = im.department_id
            LEFT JOIN indent_item_t        ii ON ii.indent_id = im.id  AND ii.display='Y'
            LEFT JOIN item_master_t       imt ON imt.id  = ii.item_id
            LEFT JOIN make_t               mk ON mk.id   = ii.make_id
            LEFT JOIN model_t              md ON md.id   = ii.model_id
            $where
            ORDER BY im.indent_date DESC, im.book_no, im.indent_no, ii.sl_no
        ");

        echo json_encode(['success' => true, 'data' => $rows ?: []]);
        exit;
    }

    public function fetchStockBook()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $txn_type = $_GET['txn_type'] ?? 'ALL';
        $eq_type = $_GET['eq_type'] ?? 'ALL';
        $item_type = $_GET['item_type'] ?? 'ALL';

        $where = "WHERE sb.display='Y'";
        if ($from && $to)
            $where .= " AND st.transaction_date BETWEEN '$from' AND '$to'";
        if ($txn_type !== 'ALL')
            $where .= " AND st.transaction_type='$txn_type'";
        if ($eq_type === 'FURNITURE')
            $where .= " AND imt.category_id=1";
        elseif ($eq_type === 'ELECTRONIC')
            $where .= " AND imt.category_id=2";
        elseif ($eq_type === 'OTHERS')
            $where .= " AND (imt.category_id NOT IN (1,2) OR imt.category_id IS NULL)";
        if ($item_type === 'C')
            $where .= " AND imt.tax_not_tax='C'";
        elseif ($item_type === 'N')
            $where .= " AND imt.tax_not_tax='N'";

        $this->_instDeptWhere($where, 'st');

        $rows = $db->customQuery("
            SELECT st.id, st.stock_book_id,
                   DATE_FORMAT(st.transaction_date,'%d-%m-%Y') AS transaction_date,
                   st.transaction_type, st.voucher_no,
                   DATE_FORMAT(st.voucher_date,'%d-%m-%Y') AS voucher_date,
                   st.received_from, st.issued_to,
                   st.receipt_qty, st.issue_qty, st.balance_qty,
                   st.receiver_initial, st.remarks,
                   st.institution_id, st.department_id,
                   col.college_name,
                   dep.department_name,
                   sb.location, sb.opening_balance,
                   imt.item_name, imt.category_id, imt.tax_not_tax,
                   mk.make_name, md.model_name,
                   ii.item_description,
                   im_i.indent_no AS indent_ref
            FROM stock_transaction_t st
            INNER JOIN stock_book_t       sb  ON sb.id  = st.stock_book_id
            INNER JOIN item_master_t      imt ON imt.id = sb.item_id
            LEFT JOIN  college_t          col ON col.id = st.institution_id
            LEFT JOIN  department_master_t dep ON dep.id = st.department_id
            LEFT JOIN  indent_master_t   im_i ON im_i.id = st.indent_id
            LEFT JOIN  indent_item_t       ii ON ii.indent_id = st.indent_id AND ii.item_id = sb.item_id
            LEFT JOIN  make_t              mk ON mk.id  = ii.make_id
            LEFT JOIN  model_t             md ON md.id  = ii.model_id
            $where
            ORDER BY st.stock_book_id ASC, st.transaction_date ASC, st.id ASC
        ");

        foreach ($rows as &$r) {
            $r['page_no'] = 'ST-' . str_pad($r['stock_book_id'], 2, '0', STR_PAD_LEFT);
        }

        echo json_encode(['success' => true, 'data' => $rows ?: []]);
        exit;
    }

    public function fetchDayBook()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $sb_type = $_GET['sb_type'] ?? 'ALL';
        $item_type = $_GET['item_type'] ?? 'ALL';
        $eq_type = $_GET['eq_type'] ?? 'ALL';
        $txn_type = $_GET['txn_type'] ?? 'ALL';

        $where = "WHERE dm.display='Y'";
        if ($from && $to)
            $where .= " AND dm.document_date BETWEEN '$from' AND '$to'";
        if ($sb_type !== 'ALL')
            $where .= " AND sbt.code='$sb_type'";
        if ($item_type === 'CONSUMABLE')
            $where .= " AND dit.name LIKE '%Consumable%'";
        elseif ($item_type === 'NON_CONSUMABLE')
            $where .= " AND dit.name NOT LIKE '%Consumable%'";
        if ($eq_type === 'FURNITURE')
            $where .= " AND dic.name LIKE '%Furniture%'";
        elseif ($eq_type === 'ELECTRONIC')
            $where .= " AND dic.name LIKE '%Electronic%'";
        if ($txn_type === 'RECEIPT')
            $where .= " AND di.transaction_type='RECEIPT'";
        elseif ($txn_type === 'ISSUE')
            $where .= " AND di.transaction_type='ISSUE'";

        $this->_instDeptWhere($where, 'dm');

        $rows = $db->customQuery("
            SELECT dm.id, dm.page_no,
                   DATE_FORMAT(dm.document_date,'%d-%m-%Y') AS document_date,
                   sbt.name AS book_type,
                   dm.receipt_order_no, dm.invoice_ref,
                   DATE_FORMAT(dm.invoice_date,'%d-%m-%Y') AS invoice_date,
                   dm.indent_no, dm.issued_to, dm.cr_voucher_ref, dm.remarks,
                   dm.institution_id, dm.department_id,
                   col.college_name,
                   dep.department_name,
                   sp.provider_name AS provider_name,
                   u.username AS verifier_name,
                   di.sl_no, di.transaction_type, di.item_description,
                   di.receipt_qty_number, di.receipt_qty_weight,
                   di.issue_qty_number,   di.issue_qty_weight,
                   di.balance_qty_number, di.balance_qty_weight,
                   di.receipt_rate, di.receipt_amount_rs, di.receipt_amount_ps,
                   di.issue_rate,   di.issue_amount_rs,   di.issue_amount_ps,
                   di.balance_rate, di.balance_amount_rs, di.balance_amount_ps,
                   imt.item_name,
                   dic.name AS item_category,
                   dit.name AS item_type_name
            FROM daybook_master_t dm
            LEFT JOIN stockbook_type_t        sbt ON sbt.id  = dm.stockbook_type_id
            LEFT JOIN college_t               col ON col.id  = dm.institution_id
            LEFT JOIN department_master_t     dep ON dep.id  = dm.department_id
            LEFT JOIN daybook_item_t           di ON di.daybook_id = dm.id
            LEFT JOIN item_master_t           imt ON imt.id  = di.item_id
            LEFT JOIN daybook_item_category_t dic ON dic.id  = di.item_category_id
            LEFT JOIN daybook_item_type_t     dit ON dit.id  = di.item_type_id
            LEFT JOIN service_providers_t      sp ON sp.id   = dm.service_provider_id
            LEFT JOIN users_t                   u ON u.id    = dm.verifier_id
            $where
            ORDER BY dm.document_date ASC, dm.page_no ASC, di.sl_no ASC
        ");

        echo json_encode(['success' => true, 'data' => $rows ?: []]);
        exit;
    }

    /* ══════════════════════════════════════════════════════════════
       PDF EXPORT DISPATCHER
    ══════════════════════════════════════════════════════════════ */
    public function exportPdf($bookType = 'indent_book')
    {
        require_once APP_ROOT . '/vendor/autoload.php';

        switch ($bookType) {
            case 'stock_book':
                $this->_pdfStockBook();
                break;
            case 'day_book':
                $this->_pdfDayBook();
                break;
            default:
                $this->_pdfIndentBook();
        }
    }

    /* ══════════════════════════════════════════════════════════════
       PDF 1 — INDENT BOOK
    ══════════════════════════════════════════════════════════════ */
    private function _pdfIndentBook()
    {
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $item_type = $_GET['item_type'] ?? 'ALL';
        $eq_type = $_GET['eq_type'] ?? 'ALL';
        $book_no = $_GET['book_no'] ?? null;
        $status = $_GET['status'] ?? 'ALL';
        $acyear = $_GET['acyear'] ?? $this->_academicYear($from);

        [$collegeName, $deptName] = $this->_resolveInstDept($db);

        $where = "WHERE im.display='Y'";
        if ($from && $to)
            $where .= " AND im.indent_date BETWEEN '$from' AND '$to'";
        if ($item_type !== 'ALL')
            $where .= " AND im.item_type='$item_type'";
        if ($book_no && is_numeric($book_no))
            $where .= " AND im.book_no=" . (int) $book_no;
        if ($status !== 'ALL')
            $where .= " AND im.status='$status'";
        if ($eq_type === 'FURNITURE')
            $where .= " AND imt.category_id=1";
        elseif ($eq_type === 'ELECTRONIC')
            $where .= " AND imt.category_id=2";
        $this->_instDeptWhere($where, 'im');

        $rows = $db->customQuery("
            SELECT im.book_no, im.indent_no,
                   DATE_FORMAT(im.indent_date,'%d-%m-%Y') AS indent_date,
                   im.item_type, im.status, im.purpose,
                   col.college_name, dep.department_name,
                   ii.sl_no, ii.item_description, ii.item_purpose,
                   ii.qty_intended, ii.qty_passed, ii.qty_issued, ii.remarks,
                   imt.item_name, mk.make_name, md.model_name,
                   u1.full_name AS verified_by_name,
                   u2.full_name AS passed_by_name,
                   u3.full_name AS issued_by_name,
                   u4.full_name AS received_by_name
            FROM indent_master_t im
            LEFT JOIN college_t           col ON col.id  = im.institution_id
            LEFT JOIN department_master_t dep ON dep.id  = im.department_id
            LEFT JOIN indent_item_t        ii ON ii.indent_id = im.id AND ii.display='Y'
            LEFT JOIN item_master_t       imt ON imt.id  = ii.item_id
            LEFT JOIN make_t               mk ON mk.id   = ii.make_id
            LEFT JOIN model_t              md ON md.id   = ii.model_id
            LEFT JOIN users_t              u1 ON u1.id   = im.verified_by
            LEFT JOIN users_t              u2 ON u2.id   = im.passed_by
            LEFT JOIN users_t              u3 ON u3.id   = im.issued_by
            LEFT JOIN users_t              u4 ON u4.id   = im.received_by
            $where
            ORDER BY im.indent_date DESC, im.book_no, im.indent_no, ii.sl_no
        ");

        /* Group by indent */
        $grouped = [];
        foreach ($rows as $r) {
            $key = $r['book_no'] . '||' . $r['indent_no'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = array_merge($r, ['items' => []]);
            }
            if ($r['sl_no'])
                $grouped[$key]['items'][] = $r;
        }

        $typeFilter = ['C' => 'Consumable', 'N' => 'Non-Consumable', 'ALL' => 'All Types'][$item_type] ?? $item_type;
        $eqFilter = ['FURNITURE' => 'Furniture', 'ELECTRONIC' => 'Electronic Equipment', 'ALL' => 'All Categories'][$eq_type] ?? $eq_type;
        $periodLabel = ($from && $to)
            ? date('d M Y', strtotime($from)) . ' to ' . date('d M Y', strtotime($to))
            : 'All Dates';

        $pdf = $this->_pdfBaseStyles('portrait');
        $pdf .= '<div class="page-wrap">';
        $pdf .= '<style>.ipage{page-break-after:always}.ipage:last-child{page-break-after:avoid}</style>';

        if (empty($grouped)) {
            $pdf .= $this->_noDataPage();
        }

        foreach ($grouped as $g) {
            $typeLabel = $g['item_type'] === 'C' ? 'Consumable' : 'Non-Consumable';
            $instLine = $g['college_name'] ?: ($collegeName ?: 'Government Polytechnic College');
            $deptLine = $g['department_name'] ?: ($deptName ?: '');

            $pdf .= '<div class="ipage">';

            /* ── Institution header ── */
            $pdf .= '
            <table style="width:100%;margin-bottom:5px;border-bottom:2px solid #111;padding-bottom:5px">
                <tr>
                    <td style="width:70%;text-align:center">
                        <div style="font-size:15px;font-weight:bold;letter-spacing:0.5px">' . htmlspecialchars($instLine) . '</div>
                        ' . ($deptLine ? '<div style="font-size:11px;font-weight:bold;color:#333;margin-top:2px">' . htmlspecialchars($deptLine) . '</div>' : '') . '
                        ' . ($acyear ? '<div style="font-size:9px;color:#666;margin-top:1px">Academic Year: ' . htmlspecialchars($acyear) . '</div>' : '') . '
                    </td>
                    <td style="width:30%;text-align:right;vertical-align:top">
                        <div style="font-size:10px;line-height:1.6">
                            <b>Book No.:</b> ' . $g['book_no'] . '<br>
                            <b>Indent No.:</b> ' . $g['indent_no'] . '<br>
                            <b>Date:</b> ' . $g['indent_date'] . '
                        </div>
                    </td>
                </tr>
            </table>

            <div style="text-align:center;font-size:12px;font-weight:bold;letter-spacing:2px;
                        border:1.5px solid #111;padding:5px;margin-bottom:8px;background:#f5f5f5">
                INDENT BOOK
                <span style="font-size:10px;font-weight:normal;letter-spacing:0">
                    &nbsp;–&nbsp; ' . $typeLabel . '
                </span>
            </div>

            <div style="font-size:10px;margin-bottom:10px;border:1px solid #ccc;padding:5px 8px;background:#fafafa">
                Please sanction the issue of the following materials for use in the
                <span style="border-bottom:1px solid #333;padding:0 60px;margin-left:4px">&nbsp;</span>
                ' . ($g['purpose'] ? '&nbsp;<b>' . htmlspecialchars($g['purpose']) . '</b>' : '') . '
            </div>';

            /* ── Items table ── */
            $pdf .= '
            <table class="register-table" style="font-size:10px">
                <thead>
                    <tr>
                        <th style="width:28px;text-align:center">Sl.<br>No.</th>
                        <th>Particulars<br>
                            <small style="font-weight:normal;color:#999">(Item Name / Make / Model / Description)</small>
                        </th>
                        <th style="width:110px">For what Purpose</th>
                        <th style="width:55px;text-align:center">Qnty.<br>Intended</th>
                        <th style="width:55px;text-align:center">Qnty.<br>Passed</th>
                        <th style="width:55px;text-align:center">Qnty.<br>Issued</th>
                        <th style="width:85px">Remarks</th>
                    </tr>
                </thead>
                <tbody>';

            if (empty($g['items'])) {
                $pdf .= '<tr><td colspan="7" style="text-align:center;color:#999;padding:16px">No items recorded</td></tr>';
            }
            foreach ($g['items'] as $item) {
                $parts = array_filter([
                    $item['item_name'] ? '<b>' . htmlspecialchars($item['item_name']) . '</b>' : null,
                    $item['make_name'] ? htmlspecialchars($item['make_name']) : null,
                    $item['model_name'] ? htmlspecialchars($item['model_name']) : null,
                    $item['item_description'] ? '<span style="color:#555;font-size:9px">' . htmlspecialchars($item['item_description']) . '</span>' : null,
                ]);
                $pdf .= '<tr>
                    <td style="text-align:center">' . $item['sl_no'] . '</td>
                    <td>' . implode('<br>', $parts) . '</td>
                    <td>' . htmlspecialchars($item['item_purpose'] ?? '') . '</td>
                    <td style="text-align:center;font-weight:bold">' . ($item['qty_intended'] ?? 0) . '</td>
                    <td style="text-align:center;font-weight:bold">' . ($item['qty_passed'] ?? 0) . '</td>
                    <td style="text-align:center;font-weight:bold">' . ($item['qty_issued'] ?? 0) . '</td>
                    <td style="font-size:9px">' . htmlspecialchars($item['remarks'] ?? '') . '</td>
                </tr>';
            }
            for ($b = 0; $b < max(0, 5 - count($g['items'])); $b++) {
                $pdf .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
            }
            $pdf .= '</tbody></table>';

            /* ── Signature strip ── */
            $verified = $g['verified_by_name'] ?? '................................';
            $passed = $g['passed_by_name'] ?? '................................';
            $issued = $g['issued_by_name'] ?? '................................';
            $received = $g['received_by_name'] ?? '................................';

            $pdf .= '
            <table style="width:100%;margin-top:20px;font-size:10px;border-collapse:collapse">
                <tr>
                    <td style="width:50%;padding-right:20px;vertical-align:top">
                        <div style="margin-bottom:16px">
                            Workshop Instructor / Superintendent<br>
                            <span style="border-bottom:1px solid #333;display:inline-block;min-width:170px;margin-top:5px">'
                . $verified . '</span>
                            <span style="font-size:8.5px;color:#666"> — Verified</span>
                        </div>
                        <div>
                            W/Shop Foreman / Store Keeper<br>
                            <span style="border-bottom:1px solid #333;display:inline-block;min-width:170px;margin-top:5px">'
                . $issued . '</span>
                            <span style="font-size:8.5px;color:#666"> — Issued</span>
                        </div>
                    </td>
                    <td style="width:50%;padding-left:20px;vertical-align:top">
                        <div style="margin-bottom:16px">
                            Superintendent<br>
                            <span style="border-bottom:1px solid #333;display:inline-block;min-width:170px;margin-top:5px">'
                . $passed . '</span>
                            <span style="font-size:8.5px;color:#666"> — Passed</span>
                        </div>
                        <div>
                            Received by<br>
                            <span style="border-bottom:1px solid #333;display:inline-block;min-width:170px;margin-top:5px">'
                . $received . '</span>
                        </div>
                    </td>
                </tr>
            </table>

            <div style="margin-top:10px;font-size:7.5px;color:#888;border-top:1px solid #ddd;padding-top:4px">
                Filters — Period: ' . $periodLabel
                . ' &nbsp;|&nbsp; Type: ' . $typeFilter
                . ' &nbsp;|&nbsp; Category: ' . $eqFilter
                . ' &nbsp;|&nbsp; Printed: ' . date('d M Y, h:i A') . '
            </div>';

            $pdf .= '</div>'; // .ipage
        }
        $pdf .= '</div>';
        $this->_renderPdf($pdf, 'indent_book', 'portrait');
    }

    /* ══════════════════════════════════════════════════════════════
       PDF 2 — STOCK BOOK
    ══════════════════════════════════════════════════════════════ */
    private function _pdfStockBook()
    {
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $txn_type = $_GET['txn_type'] ?? 'ALL';
        $eq_type = $_GET['eq_type'] ?? 'ALL';
        $item_type = $_GET['item_type'] ?? 'ALL';
        $acyear = $_GET['acyear'] ?? $this->_academicYear($from);

        [$collegeName, $deptName] = $this->_resolveInstDept($db);

        $where = "WHERE sb.display='Y'";
        if ($from && $to)
            $where .= " AND st.transaction_date BETWEEN '$from' AND '$to'";
        if ($txn_type !== 'ALL')
            $where .= " AND st.transaction_type='$txn_type'";
        if ($eq_type === 'FURNITURE')
            $where .= " AND imt.category_id=1";
        elseif ($eq_type === 'ELECTRONIC')
            $where .= " AND imt.category_id=2";
        if ($item_type === 'C')
            $where .= " AND imt.tax_not_tax='C'";
        elseif ($item_type === 'N')
            $where .= " AND imt.tax_not_tax='N'";
        $this->_instDeptWhere($where, 'st');

        $rows = $db->customQuery("
            SELECT st.id, st.stock_book_id,
                   DATE_FORMAT(st.transaction_date,'%d-%m-%Y') AS transaction_date,
                   st.transaction_type, st.voucher_no,
                   DATE_FORMAT(st.voucher_date,'%d-%m-%Y') AS voucher_date,
                   st.received_from, st.issued_to,
                   st.receipt_qty, st.issue_qty, st.balance_qty,
                   st.receiver_initial, st.remarks,
                   col.college_name, dep.department_name,
                   sb.location, sb.opening_balance,
                   imt.item_name, imt.category_id,
                   mk.make_name, md.model_name, ii.item_description,
                   im_i.indent_no AS indent_ref
            FROM stock_transaction_t st
            INNER JOIN stock_book_t       sb  ON sb.id  = st.stock_book_id
            INNER JOIN item_master_t      imt ON imt.id = sb.item_id
            LEFT JOIN  college_t          col ON col.id = st.institution_id
            LEFT JOIN  department_master_t dep ON dep.id = st.department_id
            LEFT JOIN  indent_master_t   im_i ON im_i.id = st.indent_id
            LEFT JOIN  indent_item_t       ii ON ii.indent_id = st.indent_id AND ii.item_id = sb.item_id
            LEFT JOIN  make_t              mk ON mk.id  = ii.make_id
            LEFT JOIN  model_t             md ON md.id  = ii.model_id
            $where
            ORDER BY st.stock_book_id ASC, st.transaction_date ASC, st.id ASC
        ");

        /* Group by stock_book_id → one ledger page each */
        $ledgers = [];
        foreach ($rows as $r) {
            $bid = $r['stock_book_id'];
            if (!isset($ledgers[$bid])) {
                $ledgers[$bid] = [
                    'page_no' => 'ST-' . str_pad($bid, 2, '0', STR_PAD_LEFT),
                    'item_name' => $r['item_name'],
                    'make_name' => $r['make_name'],
                    'model_name' => $r['model_name'],
                    'description' => $r['item_description'],
                    'location' => $r['location'],
                    'opening_bal' => $r['opening_balance'],
                    'college' => $r['college_name'] ?: $collegeName,
                    'dept' => $r['department_name'] ?: $deptName,
                    'rows' => [],
                ];
            }
            $ledgers[$bid]['rows'][] = $r;
        }

        $txnColors = [
            'RECEIPT' => '#1a7f37',
            'ISSUE' => '#c62828',
            'BROUGHT_FORWARD' => '#1558b0',
            'ADJUSTMENT' => '#e65100',
        ];

        $pdf = $this->_pdfBaseStyles('portrait');
        $pdf .= '<div class="page-wrap">';
        $pdf .= '<style>.lpage{page-break-after:always}.lpage:last-child{page-break-after:avoid}</style>';

        if (empty($ledgers)) {
            $pdf .= $this->_noDataPage();
        }

        foreach ($ledgers as $bid => $ledger) {
            $articleLine = implode(' / ', array_filter([
                $ledger['item_name'],
                $ledger['make_name'],
                $ledger['model_name'],
                $ledger['description'],
            ]));

            $pdf .= '<div class="lpage">';

            /* ── Header (matches physical ST-77 book) ── */
            $pdf .= '
            <table style="width:100%;margin-bottom:3px">
                <tr>
                    <td style="width:75%">
                        <div style="font-size:13px;font-weight:bold">' . htmlspecialchars($ledger['college'] ?: 'Government Polytechnic College') . '</div>
                        ' . ($ledger['dept'] ? '<div style="font-size:10px;font-weight:bold;color:#333">' . htmlspecialchars($ledger['dept']) . '</div>' : '') . '
                        ' . ($acyear ? '<div style="font-size:9px;color:#666">Academic Year: ' . htmlspecialchars($acyear) . '</div>' : '') . '
                    </td>
                    <td style="text-align:right;vertical-align:top">
                        <div style="font-size:22px;font-weight:bold;color:#444;font-style:italic;letter-spacing:2px">
                            ' . $ledger['page_no'] . '
                        </div>
                    </td>
                </tr>
            </table>

            <div style="text-align:center;font-size:14px;font-weight:bold;letter-spacing:3px;
                        border-top:2px solid #111;border-bottom:2px solid #111;padding:4px 0;margin-bottom:6px">
                STOCK BOOK OF STORES
            </div>

            <table style="width:100%;font-size:10px;margin-bottom:6px">
                <tr>
                    <td>
                        <b>Name of Article:</b>
                        <span style="border-bottom:1px solid #555;display:inline;padding:0 8px;margin-left:6px">'
                . htmlspecialchars($articleLine) . '</span>
                    </td>
                    ' . ($ledger['location'] ? '<td style="text-align:right;font-size:9px;color:#666">Location: <b>' . htmlspecialchars($ledger['location']) . '</b></td>' : '') . '
                </tr>
            </table>';

            /* ── Ledger table ── */
            $pdf .= '
            <table class="register-table" style="font-size:10px">
                <thead>
                    <tr>
                        <th style="width:65px">Date</th>
                        <th style="width:105px">No. and date of<br>voucher or invoice</th>
                        <th>From whom received<br>or to whom issued</th>
                        <th style="width:52px;text-align:center">Receipt</th>
                        <th style="width:48px;text-align:center">Issued</th>
                        <th style="width:60px;text-align:center">Balance after<br>each transaction</th>
                        <th style="width:65px;text-align:center">Initials of<br>Receiver</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                <tr style="background:#f5f5f5">
                    <td></td><td></td>
                    <td style="font-style:italic"><b>Brought forward</b></td>
                    <td style="text-align:center">' . ($ledger['opening_bal'] > 0 ? $ledger['opening_bal'] : '..') . '</td>
                    <td></td>
                    <td style="text-align:center;font-weight:bold">' . ($ledger['opening_bal'] ?: 0) . '</td>
                    <td></td><td></td>
                </tr>';

            foreach ($ledger['rows'] as $r) {
                $col = $txnColors[$r['transaction_type']] ?? '#333';
                $label = str_replace('_', ' ', $r['transaction_type']);
                $voucher = $r['voucher_no'] ?? '';
                if ($r['voucher_date'])
                    $voucher .= "\n" . $r['voucher_date'];
                if ($r['indent_ref'])
                    $voucher .= "\nIndent: " . $r['indent_ref'];
                $whom = $r['received_from'] ?: ($r['issued_to'] ?: '—');

                $pdf .= '<tr>
                    <td>' . $r['transaction_date'] . '</td>
                    <td style="font-size:9px">' . nl2br(htmlspecialchars($voucher)) . '</td>
                    <td>' . htmlspecialchars($whom) . '
                        <br><span style="color:' . $col . ';font-size:8px;font-weight:bold">' . $label . '</span>
                    </td>
                    <td style="text-align:center;font-weight:bold;color:#1a7f37">' . ($r['receipt_qty'] > 0 ? $r['receipt_qty'] : '') . '</td>
                    <td style="text-align:center;font-weight:bold;color:#c62828">' . ($r['issue_qty'] > 0 ? $r['issue_qty'] : '') . '</td>
                    <td style="text-align:center;font-weight:bold">' . $r['balance_qty'] . '</td>
                    <td style="text-align:center;font-size:9px">' . htmlspecialchars($r['receiver_initial'] ?? '') . '</td>
                    <td style="font-size:9px">' . htmlspecialchars($r['remarks'] ?? '') . '</td>
                </tr>';
            }
            for ($b = 0; $b < max(0, 8 - count($ledger['rows'])); $b++) {
                $pdf .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
            }
            $lastBalance = !empty($ledger['rows']) ? end($ledger['rows'])['balance_qty'] : $ledger['opening_bal'];
            $pdf .= '<tr style="background:#f5f5f5">
                <td></td><td></td>
                <td style="font-style:italic"><b>Carried over</b></td>
                <td></td><td></td>
                <td style="text-align:center;font-weight:bold">' . ($lastBalance ?: 0) . '</td>
                <td></td><td></td>
            </tr>';
            $pdf .= '</tbody></table>';

            /* ── Signature strip ── */
            $pdf .= '
            <table style="width:100%;margin-top:18px;font-size:10px;border-collapse:collapse">
                <tr>
                    <td style="width:50%">
                        Store Keeper:<br>
                        <span style="border-bottom:1px solid #555;display:inline-block;min-width:170px;margin-top:6px">
                            ................................
                        </span>
                    </td>
                    <td style="width:50%;text-align:right">
                        Checked by:<br>
                        <span style="border-bottom:1px solid #555;display:inline-block;min-width:170px;margin-top:6px">
                            ................................
                        </span>
                    </td>
                </tr>
            </table>
            <div style="margin-top:8px;font-size:7.5px;color:#888">
                Printed: ' . date('d M Y, h:i A') . '
                ' . ($from && $to ? ' &nbsp;|&nbsp; Period: ' . date('d M Y', strtotime($from)) . ' – ' . date('d M Y', strtotime($to)) : '') . '
            </div>';

            $pdf .= '</div>'; // .lpage
        }
        $pdf .= '</div>';
        $this->_renderPdf($pdf, 'stock_book', 'portrait');
    }

    /* ══════════════════════════════════════════════════════════════
       PDF 3 — DAY BOOK (KFC Form 16)
    ══════════════════════════════════════════════════════════════ */
    private function _pdfDayBook()
    {
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $sb_type = $_GET['sb_type'] ?? 'ALL';
        $item_type = $_GET['item_type'] ?? 'ALL';
        $eq_type = $_GET['eq_type'] ?? 'ALL';
        $txn_type = $_GET['txn_type'] ?? 'ALL';
        $acyear = $_GET['acyear'] ?? $this->_academicYear($from);

        [$collegeName, $deptName] = $this->_resolveInstDept($db);

        $where = "WHERE dm.display='Y'";
        if ($from && $to)
            $where .= " AND dm.document_date BETWEEN '$from' AND '$to'";
        if ($sb_type !== 'ALL')
            $where .= " AND sbt.code='$sb_type'";
        if ($item_type === 'CONSUMABLE')
            $where .= " AND dit.name LIKE '%Consumable%'";
        elseif ($item_type === 'NON_CONSUMABLE')
            $where .= " AND dit.name NOT LIKE '%Consumable%'";
        if ($eq_type === 'FURNITURE')
            $where .= " AND dic.name LIKE '%Furniture%'";
        elseif ($eq_type === 'ELECTRONIC')
            $where .= " AND dic.name LIKE '%Electronic%'";
        if ($txn_type === 'RECEIPT')
            $where .= " AND di.transaction_type='RECEIPT'";
        elseif ($txn_type === 'ISSUE')
            $where .= " AND di.transaction_type='ISSUE'";
        $this->_instDeptWhere($where, 'dm');

        $rows = $db->customQuery("
            SELECT dm.id, dm.page_no,
                   DATE_FORMAT(dm.document_date,'%d %b %Y') AS document_date,
                   sbt.name AS book_type,
                   dm.receipt_order_no, dm.invoice_ref,
                   DATE_FORMAT(dm.invoice_date,'%d-%m-%Y') AS invoice_date,
                   dm.indent_no, dm.issued_to, dm.cr_voucher_ref, dm.remarks,
                   dm.class, dm.unit_label,
                   col.college_name, dep.department_name,
                   sp.provider_name AS provider_name,
                   u.full_name AS verifier_name,
                   di.sl_no, di.transaction_type, di.item_description,
                   di.receipt_qty_number, di.receipt_qty_weight,
                   di.issue_qty_number,   di.issue_qty_weight,
                   di.balance_qty_number, di.balance_qty_weight,
                   di.receipt_rate, di.receipt_amount_rs, di.receipt_amount_ps,
                   di.issue_rate,   di.issue_amount_rs,   di.issue_amount_ps,
                   di.balance_rate, di.balance_amount_rs, di.balance_amount_ps,
                   imt.item_name,
                   dic.name AS item_category,
                   dit.name AS item_type_name
            FROM daybook_master_t dm
            LEFT JOIN stockbook_type_t        sbt ON sbt.id  = dm.stockbook_type_id
            LEFT JOIN college_t               col ON col.id  = dm.institution_id
            LEFT JOIN department_master_t     dep ON dep.id  = dm.department_id
            LEFT JOIN daybook_item_t           di ON di.daybook_id = dm.id
            LEFT JOIN item_master_t           imt ON imt.id  = di.item_id
            LEFT JOIN daybook_item_category_t dic ON dic.id  = di.item_category_id
            LEFT JOIN daybook_item_type_t     dit ON dit.id  = di.item_type_id
            LEFT JOIN service_providers_t      sp ON sp.id   = dm.service_provider_id
            LEFT JOIN users_t                   u ON u.id    = dm.verifier_id
            $where
            ORDER BY dm.document_date ASC, dm.page_no ASC, di.sl_no ASC
        ");

        $instLine = ($rows[0]['college_name'] ?? '') ?: $collegeName ?: 'Government Polytechnic College';
        $deptLine = ($rows[0]['department_name'] ?? '') ?: $deptName ?: '';
        $classVal = $rows[0]['class'] ?? '';
        $unitVal = $rows[0]['unit_label'] ?? '';
        $periodLabel = ($from && $to)
            ? date('d M Y', strtotime($from)) . ' – ' . date('d M Y', strtotime($to))
            : 'All Dates';

        $pdf = $this->_pdfBaseStyles('landscape');
        $pdf .= '<div class="pdf-wrap">';
        $pdf .= '<style>.dbpage{font-size:9px}</style>';
        $pdf .= '<div class="dbpage">';

        /* ── KFC Form 16 header ── */
        $pdf .= '
        <table style="width:100%;margin-bottom:4px">
            <tr>
                <td style="text-align:center;width:75%">
                    <div style="font-size:8.5px">K.F.C. &nbsp;FORM 16 &nbsp;&nbsp; [See Chapter VII, Article 161(a)]</div>
                    <div style="font-size:16px;font-weight:bold;letter-spacing:3px">DAY BOOK OF STORES</div>
                    <div style="font-size:12px;font-weight:bold;margin-top:2px">' . htmlspecialchars($instLine) . '</div>
                    ' . ($deptLine ? '<div style="font-size:10px;color:#333;font-weight:bold">' . htmlspecialchars($deptLine) . '</div>' : '') . '
                </td>
                <td style="text-align:right;vertical-align:top;width:25%">
                    <div style="font-size:9px">
                        ' . ($acyear ? 'Academic Year: <b>' . htmlspecialchars($acyear) . '</b><br>' : '') . '
                        Period: ' . $periodLabel . '<br>
                        Printed: ' . date('d M Y') . '
                    </div>
                </td>
            </tr>
        </table>

        <table style="width:100%;font-size:10px;margin-bottom:5px;border-collapse:collapse">
            <tr>
                <td>
                    Class &nbsp;
                    <span style="border-bottom:1px solid #555;display:inline-block;min-width:130px;padding:0 4px">'
            . htmlspecialchars($classVal) . '</span>
                    &nbsp;&nbsp;&nbsp;
                    Unit &nbsp;
                    <span style="border-bottom:1px solid #555;display:inline-block;min-width:130px;padding:0 4px">'
            . htmlspecialchars($unitVal) . '</span>
                </td>
            </tr>
        </table>';

        /* ── Main Day Book table (matching KFC Form 16 column layout) ── */
        $pdf .= '
        <table class="register-table" style="font-size:8.5px">
            <thead>
                <tr style="background:#1a1a2e;color:#fff">
                    <th rowspan="3" style="width:52px;text-align:center;vertical-align:middle">Month<br>&amp;<br>Date</th>
                    <th rowspan="3" style="vertical-align:middle">By whom received<br>or to whom issued</th>
                    <th rowspan="3" style="width:95px;vertical-align:middle">No. of receipt order<br>or issue note</th>
                    <th rowspan="3" style="vertical-align:middle">Item Name &amp;<br>Description</th>
                    <th colspan="6" style="text-align:center">Quantities</th>
                    <th rowspan="3" style="width:38px;text-align:center;vertical-align:middle">Initials<br>Verifier</th>
                    <th colspan="6" style="text-align:center">Value</th>
                    <th rowspan="3" style="width:38px;text-align:center;vertical-align:middle">Initials<br>Verifier</th>
                    <th rowspan="3" style="vertical-align:middle">Remarks</th>
                </tr>
                <tr style="background:#2c3e50;color:#fff">
                    <th colspan="2" style="text-align:center">Receipts</th>
                    <th colspan="2" style="text-align:center">Issues</th>
                    <th colspan="2" style="text-align:center">Balance</th>
                    <th colspan="2" style="text-align:center">Receipts</th>
                    <th colspan="2" style="text-align:center">Issues</th>
                    <th colspan="2" style="text-align:center">Balance</th>
                </tr>
                <tr style="background:#34495e;color:#eee">
                    <th style="text-align:center;width:30px">No.</th>
                    <th style="text-align:center;width:38px">Wt/Msr</th>
                    <th style="text-align:center;width:30px">No.</th>
                    <th style="text-align:center;width:38px">Wt/Msr</th>
                    <th style="text-align:center;width:30px">No.</th>
                    <th style="text-align:center;width:38px">Wt/Msr</th>
                    <th style="text-align:center;width:28px">Rate</th>
                    <th style="text-align:center;width:48px">Rs. P.</th>
                    <th style="text-align:center;width:28px">Rate</th>
                    <th style="text-align:center;width:48px">Rs. P.</th>
                    <th style="text-align:center;width:28px">Rate</th>
                    <th style="text-align:center;width:48px">Rs. P.</th>
                </tr>
            </thead>
            <tbody>';

        if (empty($rows)) {
            $pdf .= '<tr><td colspan="19" style="text-align:center;padding:20px;color:#999">No records found</td></tr>';
        }

        $prevDate = null;
        $fmtAmt = fn($rs, $ps) => ($rs === null && $ps === null) ? ''
            : number_format((float) $rs, 0) . '.' . str_pad((int) $ps, 2, '0', STR_PAD_LEFT);

        foreach ($rows as $r) {
            $isReceipt = $r['transaction_type'] === 'RECEIPT';
            $dateCell = $r['document_date'] !== $prevDate ? $r['document_date'] : '';
            $prevDate = $r['document_date'];

            $whomCell = htmlspecialchars($r['provider_name'] ?: '—');
            if ($r['issued_to'])
                $whomCell .= '<br><small style="color:#555">To: ' . htmlspecialchars($r['issued_to']) . '</small>';

            $orderCell = htmlspecialchars($r['receipt_order_no'] ?: '—');
            if ($r['invoice_ref'])
                $orderCell .= '<br><small>Inv: ' . htmlspecialchars($r['invoice_ref']) . '</small>';
            if ($r['indent_no'])
                $orderCell .= '<br><small>Indent: ' . htmlspecialchars($r['indent_no']) . '</small>';
            if ($r['cr_voucher_ref'])
                $orderCell .= '<br><small>' . htmlspecialchars($r['cr_voucher_ref']) . '</small>';

            $itemCell = '<b>' . htmlspecialchars($r['item_name'] ?? '—') . '</b>';
            if ($r['item_description'])
                $itemCell .= '<br><span style="color:#555;font-size:7.5px">' . htmlspecialchars($r['item_description']) . '</span>';
            if ($r['item_category'])
                $itemCell .= '<br><span style="color:#888;font-size:7px">[' . htmlspecialchars($r['item_category']) . ']</span>';

            $rC = $isReceipt ? 'color:#1a7f37;font-weight:bold' : 'color:#ccc';
            $iC = !$isReceipt ? 'color:#c62828;font-weight:bold' : 'color:#ccc';

            $pdf .= '<tr>
                <td style="text-align:center">' . $dateCell . '</td>
                <td>' . $whomCell . '</td>
                <td style="font-size:8px">' . $orderCell . '</td>
                <td>' . $itemCell . '</td>
                <td style="text-align:center;' . $rC . '">' . ($isReceipt ? ($r['receipt_qty_number'] ?? '') : '') . '</td>
                <td style="text-align:center;' . $rC . '">' . ($isReceipt ? ($r['receipt_qty_weight'] ?? '') : '') . '</td>
                <td style="text-align:center;' . $iC . '">' . (!$isReceipt ? ($r['issue_qty_number'] ?? '') : '') . '</td>
                <td style="text-align:center;' . $iC . '">' . (!$isReceipt ? ($r['issue_qty_weight'] ?? '') : '') . '</td>
                <td style="text-align:center;font-weight:bold">' . ($r['balance_qty_number'] ?? '') . '</td>
                <td style="text-align:center">' . ($r['balance_qty_weight'] ?? '') . '</td>
                <td style="text-align:center;font-size:8px">' . htmlspecialchars($r['verifier_name'] ?? '') . '</td>
                <td style="text-align:right;color:#888">' . ($r['receipt_rate'] ?? '') . '</td>
                <td style="text-align:right;' . $rC . '">' . $fmtAmt($r['receipt_amount_rs'] ?? null, $r['receipt_amount_ps'] ?? null) . '</td>
                <td style="text-align:right;color:#888">' . ($r['issue_rate'] ?? '') . '</td>
                <td style="text-align:right;' . $iC . '">' . $fmtAmt($r['issue_amount_rs'] ?? null, $r['issue_amount_ps'] ?? null) . '</td>
                <td style="text-align:right;color:#888">' . ($r['balance_rate'] ?? '') . '</td>
                <td style="text-align:right;font-weight:bold">' . $fmtAmt($r['balance_amount_rs'] ?? null, $r['balance_amount_ps'] ?? null) . '</td>
                <td style="text-align:center;font-size:8px">' . htmlspecialchars($r['verifier_name'] ?? '') . '</td>
                <td style="font-size:8px">' . htmlspecialchars($r['remarks'] ?? '') . '</td>
            </tr>';
        }

        $pdf .= '</tbody></table>';

        /* ── Signature strip ── */
        $pdf .= '
        <table style="width:100%;margin-top:18px;font-size:9.5px;border-collapse:collapse">
            <tr>
                <td style="width:33%;padding-right:10px">
                    Workshop Instructor / Superintendent<br>
                    <span style="border-bottom:1px solid #555;display:inline-block;min-width:160px;margin-top:7px">
                        ................................
                    </span>
                    <span style="font-size:8px;color:#666"> &mdash; Verified</span>
                </td>
                <td style="width:34%;text-align:center">
                    Superintendent / HOD<br>
                    <span style="border-bottom:1px solid #555;display:inline-block;min-width:160px;margin-top:7px">
                        ................................
                    </span>
                </td>
                <td style="width:33%;text-align:right">
                    Store Keeper<br>
                    <span style="border-bottom:1px solid #555;display:inline-block;min-width:160px;margin-top:7px">
                        ................................
                    </span>
                </td>
            </tr>
        </table>';

        $pdf .= '</div>'; // .dbpage
        $pdf .= '</div>';
        $this->_renderPdf($pdf, 'day_book', 'landscape');
    }


    private function _noDataPage(): string
    {
        return '<div style="text-align:center;padding:60px;color:#aaa;font-size:14px">No records found for the selected filters.</div>';
    }

    /* ══════════════════════════════════════════════════════════════
       BASE STYLES  — page size + margins + register table styles
       Orientation: 'portrait' | 'landscape'
    ══════════════════════════════════════════════════════════════ */
    private function _pdfBaseStyles(string $orientation): string
    {
        /*
         * Margin guide (all in mm):
         *   Portrait  → top 15 | right 12 | bottom 18 | left 18  (wider left = binding)
         *   Landscape → top 12 | right 10 | bottom 16 | left 14
         */
        $pageSize = $orientation === 'landscape' ? 'A4 landscape' : 'A4 portrait';

        $margin = $orientation === 'landscape'
            ? '12mm 10mm 16mm 14mm'   // top right bottom left
            : '15mm 12mm 18mm 18mm';  // top right bottom left (18mm left for binding)

        return '<!DOCTYPE html>
        <html>
        <head>
        <meta charset="UTF-8">
        <style>

        /* ── Reset ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* ── Body ── */
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            color: #111;
            line-height: 1.5;
        }

        /* ── Section spacing ── */
        .section          { margin-bottom: 10px; }
        .section-sm       { margin-bottom: 6px;  }
        .section-lg       { margin-bottom: 16px; }

        /* ── Text helpers ── */
        .text-center  { text-align: center; }
        .text-right   { text-align: right;  }
        .text-muted   { color: #666; }
        .fw-bold      { font-weight: bold; }
        .fs-8         { font-size: 8px; }
        .fs-9         { font-size: 9px; }
        .fs-11        { font-size: 11px; }

        /* ── Dividers ── */
        .rule-heavy { border-top: 2px solid #111; margin: 5px 0; }
        .rule-light { border-top: 1px solid #bbb; margin: 4px 0; }

        /* ─────────────────────────────────────────────────────────
        GOVERNMENT REGISTER TABLE
        Mimics physical ledger: thick outer border, ruled rows,
        dark header with white text, alternating row tint
        ───────────────────────────────────────────────────────── */
        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #111;       /* thick outer frame */
            margin-top: 6px;
            margin-bottom: 6px;
        }

        /* Header rows */
        .register-table thead th {
            border: 1px solid #444;
            padding: 5px 6px;
            font-size: 9px;
            font-weight: bold;
            vertical-align: middle;
            text-align: left;
            line-height: 1.35;
            letter-spacing: 0.2px;
        }

        /* Data rows */
        .register-table tbody td {
            border: 1px solid #777;
            padding: 5px 6px;               /* comfortable cell padding */
            vertical-align: top;
            line-height: 1.45;
        }

        /* Zebra */
        .register-table tbody tr:nth-child(even) td { background: #f7f7f7; }

        /* Brought-forward / Carried-over special rows */
        .register-table tbody tr.ledger-meta td {
            background: #f0f0f0;
            font-style: italic;
            color: #333;
        }

        /* Quantity / amount cells */
        .register-table td.qty,
        .register-table th.qty {
            text-align: center;
            width: 48px;
        }
        .register-table td.amt  { text-align: right;  }
        .register-table td.date { white-space: nowrap; }

        /* ─────────────────────────────────────────────────────────
        INSTITUTION HEADER BLOCK
        ───────────────────────────────────────────────────────── */
        .inst-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            padding-bottom: 5px;
            border-bottom: 2px solid #111;
        }
        .inst-header td { vertical-align: top; }
        .inst-name  { font-size: 15px; font-weight: bold; letter-spacing: 0.4px; }
        .inst-dept  { font-size: 11px; font-weight: bold; color: #333; margin-top: 2px; }
        .inst-year  { font-size: 9px;  color: #666;       margin-top: 1px; }
        .inst-meta  { font-size: 9px;  text-align: right; line-height: 1.6; }

        /* ─────────────────────────────────────────────────────────
        BOOK TITLE BANNER  (e.g. "INDENT BOOK", "STOCK BOOK OF STORES")
        ───────────────────────────────────────────────────────── */
        .book-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2.5px;
            border: 1.5px solid #111;
            padding: 5px 8px;
            margin-bottom: 8px;
            background: #f5f5f5;
        }
        .book-title small {
            font-size: 10px;
            font-weight: normal;
            letter-spacing: 0;
        }

        /* ─────────────────────────────────────────────────────────
        PURPOSE / SANCTION LINE
        ───────────────────────────────────────────────────────── */
        .sanction-line {
            font-size: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            padding: 6px 10px;
            background: #fafafa;
            border-radius: 2px;
            line-height: 1.6;
        }

        /* ─────────────────────────────────────────────────────────
        FILTER LEGEND  (small grey bar at foot of each page)
        ───────────────────────────────────────────────────────── */
        .filter-legend {
            margin-top: 10px;
            padding: 4px 8px;
            font-size: 7.5px;
            color: #888;
            border-top: 1px solid #ddd;
        }

        /* ─────────────────────────────────────────────────────────
        SIGNATURE STRIP
        ───────────────────────────────────────────────────────── */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        .sig-table td { vertical-align: top; padding: 0 10px; }
        .sig-table td:first-child { padding-left: 0; }
        .sig-table td:last-child  { padding-right: 0; text-align: right; }
        .sig-line {
            border-bottom: 1px solid #444;
            display: inline-block;
            min-width: 170px;
            margin-top: 6px;
        }
        .sig-label { font-size: 8.5px; color: #666; margin-left: 4px; }

        /* ─────────────────────────────────────────────────────────
        PAGE BREAKS
        ───────────────────────────────────────────────────────── */
        .page-break        { page-break-after: always; }
        .page-break-before { page-break-before: always; }
        .no-break          { page-break-inside: avoid; }

        /* ─────────────────────────────────────────────────────────
        PAGE SETUP
        ───────────────────────────────────────────────────────── */
        @page {
            size: ' . $pageSize . ';
            margin: ' . $margin . ';
        }
        .page-wrap{
            padding: 12px 18px 16px 14px;
        }

        .dbpage{
            padding-bottom: 6px;
        }

        table{
            margin-top:6px;
        }

        .register-table{
            margin-top:8px;
        }
        .pdf-wrap{}
        td,th{padding:7px}
        </style>
        </head>
        <body>';
    }


    /* ══════════════════════════════════════════════════════════════
       RENDER PDF
       — Sets paper, renders HTML, stamps page numbers top-right
         AND bottom-right, then streams to browser.
    ══════════════════════════════════════════════════════════════ */
    private function _renderPdf(string $html, string $filename, string $orientation)
    {
        $html .= '</body></html>';

        /* ── Dompdf options ── */
        $opt = new \Dompdf\Options();
        $opt->set('isRemoteEnabled', false);
        $opt->set('defaultFont', 'DejaVu Sans');
        $opt->set('isPhpEnabled', false);
        $opt->set('isHtml5ParserEnabled', true);    // better CSS parsing
        $opt->set('isFontSubsettingEnabled', true);  // smaller file size

        /* ── Build & render ── */
        $dompdf = new \Dompdf\Dompdf($opt);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        /* ── Canvas helpers ── */
        $canvas = $dompdf->getCanvas();
        $W = $canvas->get_width();   // points (1 pt = 1/72 inch)
        $H = $canvas->get_height();

        /*
         * Dompdf canvas coordinates:
         *   Origin (0,0) is TOP-LEFT.
         *   page_text(x, y, text, font, size, color, word_spacing, char_spacing, angle)
         *
         * Margins in points (1 mm ≈ 2.835 pt):
         *   Portrait  left=18mm→51pt  right=12mm→34pt  top=15mm→43pt  bottom=18mm→51pt
         *   Landscape left=14mm→40pt  right=10mm→28pt  top=12mm→34pt  bottom=16mm→45pt
         */
        $isLandscape = strtolower($orientation) === 'landscape';

        $marginRight = $isLandscape ? 28 : 34;   // pt from right edge
        $marginBottom = $isLandscape ? 45 : 51;   // pt from bottom edge
        $marginLeft = $isLandscape ? 40 : 51;   // pt from left edge
        $marginTop = $isLandscape ? 34 : 43;   // pt from top edge

        $grey = [0.45, 0.45, 0.45];
        $fntSize = 7;

        /* ── Page number — bottom right ── */
        $canvas->page_text(
            $W - $marginRight - 55,   // x  (leave room for "Page 99 / 99")
            $H - $marginBottom + 10,  // y  (just inside bottom margin)
            'Page {PAGE_NUM} / {PAGE_COUNT}',
            null,
            $fntSize,
            $grey
        );

        /* ── Institution stamp — bottom left (filename used as fallback label) ── */
        $canvas->page_text(
            $marginLeft,
            $H - $marginBottom + 10,
            strtoupper(str_replace('_', ' ', $filename)),
            null,
            $fntSize,
            $grey
        );

        /* ── Print timestamp — bottom centre ── */
        $canvas->page_text(
            $W / 2 - 40,              // rough centre
            $H - $marginBottom + 10,
            'Printed: ' . date('d M Y, h:i A'),
            null,
            $fntSize,
            $grey
        );

        /* ── Stream to browser (inline, not download) ── */
        $dompdf->stream(
            $filename . '_' . date('Ymd_His') . '.pdf',
            ['Attachment' => false]
        );
        exit;
    }
}