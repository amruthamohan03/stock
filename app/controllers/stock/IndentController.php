<?php
/**
 * FILE: app/controllers/IndentController.php
 *
 * Fixes applied:
 *  1. institution_id & department_id read from session (not POST)
 *  2. Edit allowed only when status = CREATED (not yet verified)
 *  3. updation now also syncs indent_item_t rows
 *  4. index() list ordered by updated_at DESC; serial number is a PHP counter
 *  5. All session reads use $_SESSION['user_data'] consistently
 */
class IndentController extends Controller
{
    /* ─── Session helper ─────────────────────────────────────── */
    private function _sess(): array
    {
        return $_SESSION['user_data'] ?? [];
    }
    private function _userId(): int   { return (int)($this->_sess()['id']            ?? 1); }
    private function _instId(): int   { return (int)($this->_sess()['institution_id'] ?? 1); }
    private function _deptId(): ?int  { $d = $this->_sess()['department_id'] ?? null; return $d ? (int)$d : null; }

    /* ═══════════════════════════════════════════════════════════
       INDEX  — list + create form
    ═══════════════════════════════════════════════════════════ */
    public function index()
    {
        $db = new Database();

        $items = $db->selectData('item_master_t', 'id, item_name', ['display' => 'Y']);
        $makes = $db->selectData('make_t',        'id, make_name', ['display' => 'Y']);

        /* Institution & dept names for display (from session IDs) */
        $instRow  = $db->selectData('college_t',           'college_name',    ['id' => $this->_instId()]);
        $deptRow  = $this->_deptId()
            ? $db->selectData('department_master_t', 'department_name', ['id' => $this->_deptId()])
            : [];

        /* List ordered by updated_at DESC; sl_no assigned in PHP */
        $result = $db->customQuery("
            SELECT im.*,
                   c.college_name,
                   dm.department_name,
                   u1.full_name AS created_by_name,
                   u2.full_name AS verified_by_name,
                   u3.full_name AS passed_by_name,
                   u4.full_name AS issued_by_name,
                   u5.full_name AS received_by_name
            FROM   indent_master_t im
            LEFT JOIN college_t           c  ON im.institution_id = c.id
            LEFT JOIN department_master_t dm ON im.department_id  = dm.id
            LEFT JOIN users_t u1 ON im.created_by  = u1.id
            LEFT JOIN users_t u2 ON im.verified_by = u2.id
            LEFT JOIN users_t u3 ON im.passed_by   = u3.id
            LEFT JOIN users_t u4 ON im.issued_by   = u4.id
            LEFT JOIN users_t u5 ON im.received_by = u5.id
            WHERE im.display = 'Y'
            ORDER BY im.updated_at DESC
        ");

        $data = [
            'title'         => 'Indent Book Management',
            'items'         => $items,
            'makes'         => $makes,
            'result'        => $result ?: [],
            'inst_name'     => $instRow[0]['college_name']      ?? '',
            'dept_name'     => $deptRow[0]['department_name']   ?? '',
        ];

        $this->viewWithLayout('stock/indent', $data);
    }

    /* ═══════════════════════════════════════════════════════════
       CRUD  — insertion | updation | deletion
    ═══════════════════════════════════════════════════════════ */
    public function crudData($action = 'insertion')
    {
        header('Content-Type: application/json');
        $db = new Database();

        /* ── INSERT ─────────────────────────────────────────── */
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] === 'POST') {

            $book_no     = (int)($_POST['book_no']     ?? 0);
            $indent_no   = trim($_POST['indent_no']    ?? '');
            $item_type   = trim($_POST['item_type']    ?? '');
            $indent_date = trim($_POST['indent_date']  ?? '');
            $purpose     = htmlspecialchars(trim($_POST['purpose'] ?? ''), ENT_QUOTES);

            if ($book_no   <= 0) { echo json_encode(['success'=>false,'message'=>'Book number is required']);  exit; }
            if ($indent_no === '') { echo json_encode(['success'=>false,'message'=>'Indent number is required']); exit; }
            if ($item_type === '') { echo json_encode(['success'=>false,'message'=>'Item type is required']);    exit; }
            if ($indent_date === '') { echo json_encode(['success'=>false,'message'=>'Indent date is required']); exit; }

            /* Duplicate check */
            $existing = $db->selectData('indent_master_t', 'id',
                ['book_no' => $book_no, 'indent_no' => $indent_no]);
            if (!empty($existing)) {
                echo json_encode(['success'=>false,'message'=>'Book No. + Indent No. already exists']);
                exit;
            }

            /* institution & department from session */
            $instId = $this->_instId();
            $deptId = $this->_deptId();

            $indentData = [
                'institution_id' => $instId,
                'department_id'  => ($deptId)?$deptId:1,
                'book_no'        => $book_no,
                'indent_no'      => htmlspecialchars($indent_no, ENT_QUOTES),
                'item_type'      => $item_type,
                'indent_date'    => $indent_date,
                'purpose'        => $purpose,
                'created_by'     => $this->_userId(),
                'status'         => 'CREATED',
            ];

            $indent_id = $db->insertData('indent_master_t', $indentData);

            if ($indent_id) {
                $this->_upsertItems($db, $indent_id, $_POST['items'] ?? [], 'insert');
                echo json_encode(['success'=>true,'message'=>'Indent created successfully','id'=>$indent_id]);
            } else {
                echo json_encode(['success'=>false,'message'=>'Failed to create indent']);
            }
            exit;
        }

        /* ── UPDATE (only allowed when status = CREATED) ─────── */
        elseif ($action === 'updation' && $_SERVER['REQUEST_METHOD'] === 'POST') {

            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid Indent ID']); exit; }

            /* Guard: must still be CREATED */
            $current = $db->selectData('indent_master_t', 'status', ['id' => $id]);
            if (empty($current) || $current[0]['status'] !== 'CREATED') {
                echo json_encode(['success'=>false,'message'=>'Indent can only be edited before verification']);
                exit;
            }

            $indentData = [
                'book_no'    => (int)($_POST['book_no']    ?? 0),
                'indent_no'  => htmlspecialchars(trim($_POST['indent_no']  ?? ''), ENT_QUOTES),
                'item_type'  => htmlspecialchars(trim($_POST['item_type']  ?? ''), ENT_QUOTES),
                'indent_date'=> trim($_POST['indent_date'] ?? ''),
                'purpose'    => htmlspecialchars(trim($_POST['purpose']    ?? ''), ENT_QUOTES),
            ];

            $update = $db->updateData('indent_master_t', $indentData, ['id' => $id]);

            /* Sync items: soft-delete existing, re-insert */
            if ($update !== false) {
                $db->updateData('indent_item_t', ['display' => 'N'], ['indent_id' => $id]);
                $this->_upsertItems($db, $id, $_POST['items'] ?? [], 'update');
                echo json_encode(['success'=>true,'message'=>'Indent updated successfully']);
            } else {
                echo json_encode(['success'=>false,'message'=>'Update failed']);
            }
            exit;
        }

        /* ── DELETE (only CREATED) ───────────────────────────── */
        elseif ($action === 'deletion') {

            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid Indent ID']); exit; }

            $current = $db->selectData('indent_master_t', 'status', ['id' => $id]);
            if (empty($current) || $current[0]['status'] !== 'CREATED') {
                echo json_encode(['success'=>false,'message'=>'Only CREATED indents can be deleted']);
                exit;
            }

            /* Soft-delete items first */
            $db->updateData('indent_item_t', ['display' => 'N'], ['indent_id' => $id]);
            $delete = $db->updateData('indent_master_t', ['display' => 'N'], ['id' => $id]);

            if ($delete) {
                echo json_encode(['success'=>true,'message'=>'Indent deleted successfully']);
            } else {
                echo json_encode(['success'=>false,'message'=>'Delete failed']);
            }
            exit;
        }
    }

    /* ─── Private: insert / re-insert items ─────────────────── */
    private function _upsertItems(Database $db, int $indentId, array $items, string $mode): void
    {
        if (empty($items)) return;

        foreach ($items as $item) {
            $row = [
                'indent_id'        => $indentId,
                'sl_no'            => (int)($item['sl_no'] ?? 0),
                'item_id'          => (int)($item['item_id'] ?? 0),
                'make_id'          => !empty($item['make_id'])  ? (int)$item['make_id']  : null,
                'model_id'         => !empty($item['model_id']) ? (int)$item['model_id'] : null,
                'item_description' => htmlspecialchars(trim($item['item_description'] ?? ''), ENT_QUOTES),
                'item_purpose'     => htmlspecialchars(trim($item['item_purpose']     ?? ''), ENT_QUOTES),
                'qty_intended'     => (int)($item['qty_intended'] ?? 0),
                'qty_passed'       => 0,
                'qty_issued'       => 0,
                'remarks'          => htmlspecialchars(trim($item['remarks'] ?? ''), ENT_QUOTES),
                'display'          => 'Y',
            ];
            $db->insertData('indent_item_t', $row);
        }
    }

    /* ═══════════════════════════════════════════════════════════
       GET INDENT BY ID  (AJAX — for edit & workflow modals)
    ═══════════════════════════════════════════════════════════ */
    public function getIndentById()
    {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid ID']); exit; }

        $db     = new Database();
        $indent = $db->selectData('indent_master_t', '*', ['id' => $id]);

        if (empty($indent)) { echo json_encode(['success'=>false,'message'=>'Indent not found']); exit; }

        /* Items with names */
        $items = $db->customQuery("
            SELECT ii.*, i.item_name, mk.make_name, md.model_name
            FROM   indent_item_t ii
            LEFT JOIN item_master_t i  ON ii.item_id  = i.id
            LEFT JOIN make_t        mk ON ii.make_id  = mk.id
            LEFT JOIN model_t       md ON ii.model_id = md.id
            WHERE  ii.indent_id = $id AND ii.display = 'Y'
            ORDER BY ii.sl_no
        ");

        echo json_encode(['success'=>true,'data'=>['indent'=>$indent[0],'items'=>$items??[]]]);
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       GET MODELS BY MAKE  (cascading dropdown)
    ═══════════════════════════════════════════════════════════ */
    public function getModelsByMake()
    {
        header('Content-Type: application/json');
        $make_id = (int)($_GET['make_id'] ?? 0);
        if ($make_id <= 0) { echo json_encode(['success'=>false,'data'=>[]]); exit; }

        $db     = new Database();
        $models = $db->selectData('model_t', 'id, model_name', ['make_id' => $make_id, 'display' => 'Y']);

        echo json_encode(['success'=>true,'data'=>$models??[]]);
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       WORKFLOW ACTIONS
    ═══════════════════════════════════════════════════════════ */

    /* VERIFY */
    public function verifyIndent()
    {
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid Indent ID']); exit; }

        $db  = new Database();

        /* Guard: must be CREATED */
        $cur = $db->selectData('indent_master_t', 'status', ['id' => $id]);
        if (empty($cur) || $cur[0]['status'] !== 'CREATED') {
            echo json_encode(['success'=>false,'message'=>'Indent is not in CREATED status']);
            exit;
        }

        $ok = $db->updateData('indent_master_t',
            ['verified_by' => $this->_userId(), 'status' => 'VERIFIED'],
            ['id' => $id]);

        echo json_encode($ok
            ? ['success'=>true, 'message'=>'Indent verified successfully']
            : ['success'=>false,'message'=>'Verification failed']);
        exit;
    }

    /* PASS */
    public function passIndent()
    {
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid Indent ID']); exit; }

        $db   = new Database();
        $cur  = $db->selectData('indent_master_t', 'status', ['id' => $id]);
        if (empty($cur) || $cur[0]['status'] !== 'VERIFIED') {
            echo json_encode(['success'=>false,'message'=>'Indent must be VERIFIED before passing']);
            exit;
        }

        foreach ($_POST['items'] ?? [] as $item) {
            $db->updateData('indent_item_t',
                ['qty_passed' => (int)($item['qty_passed'] ?? 0)],
                ['id' => (int)($item['id'] ?? 0)]);
        }

        $ok = $db->updateData('indent_master_t',
            ['passed_by' => $this->_userId(), 'status' => 'PASSED'],
            ['id' => $id]);

        echo json_encode($ok
            ? ['success'=>true, 'message'=>'Indent passed successfully']
            : ['success'=>false,'message'=>'Pass failed']);
        exit;
    }

    /* ISSUE */
    public function issueIndent()
    {
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid Indent ID']); exit; }

        $db  = new Database();
        $cur = $db->selectData('indent_master_t', 'status', ['id' => $id]);
        if (empty($cur) || $cur[0]['status'] !== 'PASSED') {
            echo json_encode(['success'=>false,'message'=>'Indent must be PASSED before issuing']);
            exit;
        }

        foreach ($_POST['items'] ?? [] as $item) {
            $db->updateData('indent_item_t',
                ['qty_issued' => (int)($item['qty_issued'] ?? 0)],
                ['id' => (int)($item['id'] ?? 0)]);
        }

        $ok = $db->updateData('indent_master_t',
            ['issued_by' => $this->_userId(), 'status' => 'ISSUED'],
            ['id' => $id]);

        echo json_encode($ok
            ? ['success'=>true, 'message'=>'Indent issued successfully']
            : ['success'=>false,'message'=>'Issue failed']);
        exit;
    }

    /* RECEIVE */
    public function receiveIndent()
    {
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid Indent ID']); exit; }

        $db  = new Database();
        $cur = $db->selectData('indent_master_t', 'status', ['id' => $id]);
        if (empty($cur) || $cur[0]['status'] !== 'ISSUED') {
            echo json_encode(['success'=>false,'message'=>'Indent must be ISSUED before receiving']);
            exit;
        }

        $ok = $db->updateData('indent_master_t',
            ['received_by' => $this->_userId(), 'status' => 'RECEIVED'],
            ['id' => $id]);

        echo json_encode($ok
            ? ['success'=>true, 'message'=>'Indent received successfully']
            : ['success'=>false,'message'=>'Receive failed']);
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       VIEW INDENT DETAIL PAGE
    ═══════════════════════════════════════════════════════════ */
    public function viewIndent($id = null)
    {
        if (empty($id)) { $this->redirect('indent'); return; }

        $db = new Database();

        $indent = $db->customQuery("
            SELECT im.*,
                   c.college_name, dm.department_name,
                   u1.full_name AS created_by_name,
                   u2.full_name AS verified_by_name,
                   u3.full_name AS passed_by_name,
                   u4.full_name AS issued_by_name,
                   u5.full_name AS received_by_name
            FROM   indent_master_t im
            LEFT JOIN college_t           c  ON im.institution_id = c.id
            LEFT JOIN department_master_t dm ON im.department_id  = dm.id
            LEFT JOIN users_t u1 ON im.created_by  = u1.id
            LEFT JOIN users_t u2 ON im.verified_by = u2.id
            LEFT JOIN users_t u3 ON im.passed_by   = u3.id
            LEFT JOIN users_t u4 ON im.issued_by   = u4.id
            LEFT JOIN users_t u5 ON im.received_by = u5.id
            WHERE im.id = " . (int)$id . " AND im.display = 'Y'
        ");

        if (empty($indent)) { $this->redirect('indent'); return; }

        $items = $db->customQuery("
            SELECT ii.*, i.item_name, mk.make_name, md.model_name
            FROM   indent_item_t ii
            LEFT JOIN item_master_t i  ON ii.item_id  = i.id
            LEFT JOIN make_t        mk ON ii.make_id  = mk.id
            LEFT JOIN model_t       md ON ii.model_id = md.id
            WHERE  ii.indent_id = " . (int)$id . " AND ii.display = 'Y'
            ORDER BY ii.sl_no
        ");

        $this->viewWithLayout('stock/indentview', [
            'title'  => 'Indent — ' . $indent[0]['indent_no'],
            'indent' => $indent[0],
            'items'  => $items ?? [],
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       REPORT PAGE
    ═══════════════════════════════════════════════════════════ */
    public function report()
    {
        $this->viewWithLayout('stock/indent_report', ['title' => 'Indent Book Report']);
    }

    public function fetchReport()
    {
        header('Content-Type: application/json');
        $db   = new Database();
        $from = $_GET['from'] ?? null;
        $to   = $_GET['to']   ?? null;
        $type = $_GET['type'] ?? null;

        $where = "WHERE im.display='Y'";
        if ($from && $to) $where .= " AND im.indent_date BETWEEN '$from' AND '$to'";
        if ($type && $type !== 'ALL') $where .= " AND im.item_type='$type'";

        $rows = $db->customQuery("
            SELECT im.id, im.indent_no, im.indent_date, im.item_type,
                   ii.item_description, ii.qty_intended, ii.qty_passed, ii.qty_issued
            FROM   indent_master_t im
            LEFT JOIN indent_item_t ii ON ii.indent_id = im.id
            $where
            ORDER BY im.indent_date DESC
        ");

        echo json_encode(['success'=>true,'data'=>$rows??[]]);
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       EXPORT PDF
    ═══════════════════════════════════════════════════════════ */
    public function exportPdf()
    {
        require_once APP_ROOT . '/vendor/autoload.php';
        $db   = new Database();
        $from = $_GET['from'] ?? null;
        $to   = $_GET['to']   ?? null;
        $type = $_GET['type'] ?? null;

        $where = "WHERE im.display='Y'";
        if ($from && $to) $where .= " AND im.indent_date BETWEEN '$from' AND '$to'";
        if ($type && $type !== 'ALL') $where .= " AND im.item_type='$type'";

        $rows = $db->customQuery("
            SELECT im.indent_no, im.indent_date, im.item_type,
                   ii.item_description, ii.qty_intended, ii.qty_passed, ii.qty_issued
            FROM   indent_master_t im
            LEFT JOIN indent_item_t ii ON ii.indent_id = im.id
            $where
            ORDER BY im.indent_date DESC
        ");

        ob_start(); ?>
        <!DOCTYPE html><html><head><meta charset="UTF-8">
        <style>
            body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; }
            h2   { text-align: center; margin-bottom: 4px; }
            small { color: #555; }
            table { width: 100%; border-collapse: collapse; margin-top: 12px; }
            th, td { border: 1px solid #333; padding: 5px 6px; }
            th { background: #2c3e50; color: #fff; }
            tr:nth-child(even) td { background: #f7f7f7; }
        </style>
        </head><body>
        <h2>Indent Book Report</h2>
        <small>Generated: <?= date('d-m-Y H:i') ?></small>
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Indent No</th><th>Date</th><th>Type</th>
                    <th>Description</th><th>Intended</th><th>Passed</th><th>Issued</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($rows as $r): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($r['indent_no']) ?></td>
                    <td><?= date('d-m-Y', strtotime($r['indent_date'])) ?></td>
                    <td><?= $r['item_type'] === 'C' ? 'Consumable' : 'Non-Consumable' ?></td>
                    <td><?= htmlspecialchars($r['item_description'] ?? '') ?></td>
                    <td style="text-align:center"><?= $r['qty_intended'] ?></td>
                    <td style="text-align:center"><?= $r['qty_passed'] ?></td>
                    <td style="text-align:center"><?= $r['qty_issued'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </body></html>
        <?php
        $html = ob_get_clean();

        $opt = new \Dompdf\Options();
        $opt->set('defaultFont', 'DejaVu Sans');
        $dompdf = new \Dompdf\Dompdf($opt);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('indent_report_' . date('Ymd') . '.pdf', ['Attachment' => false]);
        exit;
    }
}