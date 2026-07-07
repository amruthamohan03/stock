<?php
/**
 * FILE: app/controllers/KfcForm13Controller.php
 *
 * Handles KFC FORM 13 — Annual Indent for Stores
 * Tables  : kfc_form_13_t, kfc_form_13_items_t
 * PDF lib : Dompdf  (require_once APP_ROOT.'/vendor/autoload.php')
 *
 * Routes (add to your router):
 *   kfc13/index
 *   kfc13/crudData/<action>
 *   kfc13/viewForm/<id>
 *   kfc13/exportPdf/<id>
 *   kfc13/listPdf
 *   kfc13/changeStatus
 *   kfc13/getFormItems
 */
class KfcForm13Controller extends Controller
{
    /* ─── Session helpers ──────────────────────────────────────── */
    private function _sess(): array  { return $_SESSION['user_data'] ?? []; }
    private function _userId(): int  { return (int)($this->_sess()['id']             ?? 1); }
    private function _instId(): int  { return (int)($this->_sess()['institution_id'] ?? 1); }
    private function _deptId(): ?int { $d = $this->_sess()['department_id'] ?? null; return $d ? (int)$d : null; }

    /* ═══════════════════════════════════════════════════════════
       INDEX — list all forms + create form
    ═══════════════════════════════════════════════════════════ */
    public function index()
    {
        $db = new Database();

        /* Master dropdowns */
        $items   = $db->selectData('item_master_t',  'id, item_name',  ['display' => 'Y'], 'item_name ASC');
        $makes   = $db->selectData('make_t',         'id, make_name',  ['display' => 'Y'], 'make_name ASC');
        $groups  = $db->selectData('group_item_name_master_t', 'id, group_name', ['display' => 'Y'], 'group_name ASC');

        $instRow = $db->selectData('college_t',           'college_name',    ['id' => $this->_instId()]);
        $deptRow = $this->_deptId()
                 ? $db->selectData('department_master_t', 'department_name', ['id' => $this->_deptId()])
                 : [];

        /* List query */
        $result = $db->customQuery("
            SELECT
                f.*,
                c.college_name,
                dm.department_name,
                u1.full_name  AS created_by_name,
                u2.full_name  AS approved_by_name,
                COUNT(fi.id)  AS item_count
            FROM   kfc_form_13_t f
            LEFT JOIN college_t           c  ON f.institution_id = c.id
            LEFT JOIN department_master_t dm ON f.department_id  = dm.id
            LEFT JOIN users_t             u1 ON f.created_by     = u1.id
            LEFT JOIN users_t             u2 ON f.approved_by    = u2.id
            LEFT JOIN kfc_form_13_items_t fi ON fi.form_id = f.id AND fi.display = 'Y'
            WHERE  f.display = 'Y'
            GROUP BY f.id
            ORDER BY f.id DESC
        ");

        $data = [
            'title'     => 'KFC Form 13 — Annual Indent',
            'items'     => $items,
            'makes'     => $makes,
            'groups'    => $groups,
            'result'    => $result ?: [],
            'inst_name' => $instRow[0]['college_name']     ?? '',
            'dept_name' => $deptRow[0]['department_name']  ?? '',
        ];

        $this->viewWithLayout('submissions/kfc_form13', $data);
    }

    /* ═══════════════════════════════════════════════════════════
       CRUD DATA — insertion | updation | deletion
    ═══════════════════════════════════════════════════════════ */
    public function crudData($action = 'insertion')
    {
        header('Content-Type: application/json');
        $db = new Database();

        /* ── INSERT ──────────────────────────────────────────── */
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] === 'POST') {

            $form_no      = trim($_POST['form_no']      ?? '');
            $fin_year     = trim($_POST['financial_year'] ?? '');
            $indent_date  = trim($_POST['indent_date']  ?? '');
            $indent_type  = trim($_POST['indent_type']  ?? 'GENERAL');
            $funds_provided = trim($_POST['funds_provided'] ?? 'N');

            if ($form_no     === '') { echo json_encode(['success'=>false,'message'=>'Form / Indent No is required']);   exit; }
            if ($fin_year    === '') { echo json_encode(['success'=>false,'message'=>'Financial Year is required']);      exit; }
            if ($indent_date === '') { echo json_encode(['success'=>false,'message'=>'Indent Date is required']);         exit; }

            /* Duplicate check */
            $dup = $db->selectData('kfc_form_13_t', 'id', ['form_no'=>$form_no,'financial_year'=>$fin_year,'display'=>'Y']);
            if (!empty($dup)) {
                echo json_encode(['success'=>false,'message'=>'Form No. already exists for this Financial Year']);
                exit;
            }

            $masterData = [
                'form_no'                    => htmlspecialchars($form_no, ENT_QUOTES),
                'financial_year'             => htmlspecialchars($fin_year, ENT_QUOTES),
                'indent_date'                => $indent_date,
                'institution_id'             => $this->_instId(),
                'department_id'              => $this->_deptId(),
                'dept_name_free'             => htmlspecialchars(trim($_POST['dept_name_free'] ?? ''), ENT_QUOTES),
                'indent_type'                => in_array($indent_type, ['GENERAL','DIETARY','SPC']) ? $indent_type : 'GENERAL',
                'prev_correspondence_ref'    => htmlspecialchars(trim($_POST['prev_correspondence_ref'] ?? ''), ENT_QUOTES),
                'funds_provided'             => in_array($funds_provided, ['Y','N','PARTIAL']) ? $funds_provided : 'N',
                'funds_remark'               => htmlspecialchars(trim($_POST['funds_remark'] ?? ''), ENT_QUOTES),
                'delivery_address'           => htmlspecialchars(trim($_POST['delivery_address'] ?? ''), ENT_QUOTES),
                'nearest_railway_station'    => htmlspecialchars(trim($_POST['nearest_railway_station'] ?? ''), ENT_QUOTES),
                'delivery_place'             => htmlspecialchars(trim($_POST['delivery_place'] ?? ''), ENT_QUOTES),
                'inspecting_officer_name'    => htmlspecialchars(trim($_POST['inspecting_officer_name'] ?? ''), ENT_QUOTES),
                'inspecting_officer_desig'   => htmlspecialchars(trim($_POST['inspecting_officer_desig'] ?? ''), ENT_QUOTES),
                'special_instructions'       => htmlspecialchars(trim($_POST['special_instructions'] ?? ''), ENT_QUOTES),
                'sanction_authority'         => htmlspecialchars(trim($_POST['sanction_authority'] ?? ''), ENT_QUOTES),
                'sanction_order_no'          => htmlspecialchars(trim($_POST['sanction_order_no'] ?? ''), ENT_QUOTES),
                'sanction_order_date'        => trim($_POST['sanction_order_date'] ?? '') ?: null,
                'status'                     => 'DRAFT',
                'created_by'                 => $this->_userId(),
                'display'                    => 'Y',
            ];

            $formId = $db->insertData('kfc_form_13_t', $masterData);
            if (!$formId) {
                echo json_encode(['success'=>false,'message'=>'Failed to save Form 13 header']);
                exit;
            }

            /* Save line items */
            $this->_syncItems($db, $formId, $_POST['items'] ?? []);

            echo json_encode(['success'=>true,'message'=>'KFC Form 13 created successfully','id'=>$formId]);
            exit;
        }

        /* ── UPDATE ──────────────────────────────────────────── */
        if ($action === 'updation' && $_SERVER['REQUEST_METHOD'] === 'POST') {

            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid Form ID']); exit; }

            /* Only DRAFT can be edited */
            $cur = $db->selectData('kfc_form_13_t','status',['id'=>$id]);
            if (empty($cur) || $cur[0]['status'] !== 'DRAFT') {
                echo json_encode(['success'=>false,'message'=>'Only DRAFT forms can be edited']);
                exit;
            }

            $funds_provided = trim($_POST['funds_provided'] ?? 'N');
            $indent_type    = trim($_POST['indent_type']    ?? 'GENERAL');

            $updateData = [
                'form_no'                    => htmlspecialchars(trim($_POST['form_no'] ?? ''), ENT_QUOTES),
                'financial_year'             => htmlspecialchars(trim($_POST['financial_year'] ?? ''), ENT_QUOTES),
                'indent_date'                => trim($_POST['indent_date'] ?? ''),
                'dept_name_free'             => htmlspecialchars(trim($_POST['dept_name_free'] ?? ''), ENT_QUOTES),
                'indent_type'                => in_array($indent_type, ['GENERAL','DIETARY','SPC']) ? $indent_type : 'GENERAL',
                'prev_correspondence_ref'    => htmlspecialchars(trim($_POST['prev_correspondence_ref'] ?? ''), ENT_QUOTES),
                'funds_provided'             => in_array($funds_provided, ['Y','N','PARTIAL']) ? $funds_provided : 'N',
                'funds_remark'               => htmlspecialchars(trim($_POST['funds_remark'] ?? ''), ENT_QUOTES),
                'delivery_address'           => htmlspecialchars(trim($_POST['delivery_address'] ?? ''), ENT_QUOTES),
                'nearest_railway_station'    => htmlspecialchars(trim($_POST['nearest_railway_station'] ?? ''), ENT_QUOTES),
                'delivery_place'             => htmlspecialchars(trim($_POST['delivery_place'] ?? ''), ENT_QUOTES),
                'inspecting_officer_name'    => htmlspecialchars(trim($_POST['inspecting_officer_name'] ?? ''), ENT_QUOTES),
                'inspecting_officer_desig'   => htmlspecialchars(trim($_POST['inspecting_officer_desig'] ?? ''), ENT_QUOTES),
                'special_instructions'       => htmlspecialchars(trim($_POST['special_instructions'] ?? ''), ENT_QUOTES),
                'sanction_authority'         => htmlspecialchars(trim($_POST['sanction_authority'] ?? ''), ENT_QUOTES),
                'sanction_order_no'          => htmlspecialchars(trim($_POST['sanction_order_no'] ?? ''), ENT_QUOTES),
                'sanction_order_date'        => trim($_POST['sanction_order_date'] ?? '') ?: null,
            ];

            $ok = $db->updateData('kfc_form_13_t', $updateData, ['id'=>$id]);
            if (!$ok) { echo json_encode(['success'=>false,'message'=>'Update failed']); exit; }

            $this->_syncItems($db, $id, $_POST['items'] ?? []);

            echo json_encode(['success'=>true,'message'=>'Form 13 updated successfully']);
            exit;
        }

        /* ── DELETE (soft) ───────────────────────────────────── */
        if ($action === 'deletion' && $_SERVER['REQUEST_METHOD'] === 'POST') {

            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid ID']); exit; }

            $db->updateData('kfc_form_13_t',       ['display'=>'N'], ['id'=>$id]);
            $db->updateData('kfc_form_13_items_t', ['display'=>'N'], ['form_id'=>$id]);

            echo json_encode(['success'=>true,'message'=>'Form 13 deleted successfully']);
            exit;
        }

        echo json_encode(['success'=>false,'message'=>'Invalid request']);
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       GET FORM ITEMS — for edit-modal population
    ═══════════════════════════════════════════════════════════ */
    public function getFormItems()
    {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid ID']); exit; }

        $db = new Database();

        $form = $db->customQuery("
            SELECT f.*, c.college_name, dm.department_name
            FROM   kfc_form_13_t f
            LEFT JOIN college_t           c  ON f.institution_id = c.id
            LEFT JOIN department_master_t dm ON f.department_id  = dm.id
            WHERE  f.id = $id AND f.display = 'Y'
        ");
        if (empty($form)) { echo json_encode(['success'=>false,'message'=>'Form not found']); exit; }

        $items = $db->customQuery("
            SELECT fi.*, i.item_name, g.group_name, mk.make_name, md.model_name
            FROM   kfc_form_13_items_t fi
            LEFT JOIN item_master_t              i  ON fi.item_id  = i.id
            LEFT JOIN group_item_name_master_t   g  ON fi.group_id = g.id
            LEFT JOIN make_t                     mk ON fi.make_id  = mk.id
            LEFT JOIN model_t                    md ON fi.model_id = md.id
            WHERE  fi.form_id = $id AND fi.display = 'Y'
            ORDER BY fi.sl_no
        ");

        echo json_encode([
            'success' => true,
            'form'    => $form[0],
            'items'   => $items ?? [],
        ]);
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       CHANGE STATUS — DRAFT → SUBMITTED → APPROVED / REJECTED
    ═══════════════════════════════════════════════════════════ */
    public function changeStatus()
    {
        header('Content-Type: application/json');
        $db     = new Database();
        $id     = (int)($_POST['id']     ?? 0);
        $action = trim($_POST['action']  ?? '');

        if ($id <= 0 || $action === '') {
            echo json_encode(['success'=>false,'message'=>'Invalid parameters']); exit;
        }

        $cur = $db->selectData('kfc_form_13_t','status',['id'=>$id]);
        if (empty($cur)) { echo json_encode(['success'=>false,'message'=>'Form not found']); exit; }

        $curStatus = $cur[0]['status'];
        $allowed   = [
            'submit'  => ['DRAFT',      'SUBMITTED'],
            'approve' => ['SUBMITTED',  'APPROVED'],
            'reject'  => ['SUBMITTED',  'REJECTED'],
            'reopen'  => ['REJECTED',   'DRAFT'],
            'close'   => ['APPROVED',   'CLOSED'],
        ];

        if (!isset($allowed[$action])) {
            echo json_encode(['success'=>false,'message'=>'Unknown action']); exit;
        }
        [$reqStatus, $newStatus] = $allowed[$action];
        if ($curStatus !== $reqStatus) {
            echo json_encode(['success'=>false,'message'=>"Form must be $reqStatus to $action"]); exit;
        }

        $update = ['status' => $newStatus];
        $now    = date('Y-m-d H:i:s');
        if ($action === 'submit')  { $update['submitted_on'] = $now; }
        if ($action === 'approve') { $update['approved_on'] = $now; $update['approved_by'] = $this->_userId(); }
        if ($action === 'reject')  {
            $update['rejected_on']     = $now;
            $update['rejected_by']     = $this->_userId();
            $update['rejection_reason'] = htmlspecialchars(trim($_POST['rejection_reason'] ?? ''), ENT_QUOTES);
        }

        $ok = $db->updateData('kfc_form_13_t', $update, ['id'=>$id]);
        echo json_encode($ok
            ? ['success'=>true, 'message'=>"Form $newStatus successfully"]
            : ['success'=>false,'message'=>'Status update failed']);
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       VIEW FORM DETAIL PAGE
    ═══════════════════════════════════════════════════════════ */
    public function viewForm($id = null)
    {
        if (empty($id)) { $this->redirect('kfc13'); return; }

        $db = new Database();

        $form = $db->customQuery("
            SELECT f.*, c.college_name, dm.department_name,
                   u1.full_name AS created_by_name,
                   u2.full_name AS approved_by_name,
                   u3.full_name AS rejected_by_name
            FROM   kfc_form_13_t f
            LEFT JOIN college_t           c  ON f.institution_id = c.id
            LEFT JOIN department_master_t dm ON f.department_id  = dm.id
            LEFT JOIN users_t u1 ON f.created_by   = u1.id
            LEFT JOIN users_t u2 ON f.approved_by  = u2.id
            LEFT JOIN users_t u3 ON f.rejected_by  = u3.id
            WHERE  f.id = " . (int)$id . " AND f.display = 'Y'
        ");

        if (empty($form)) { $this->redirect('kfc13'); return; }

        $items = $db->customQuery("
            SELECT fi.*, i.item_name, g.group_name, mk.make_name, md.model_name
            FROM   kfc_form_13_items_t fi
            LEFT JOIN item_master_t            i  ON fi.item_id  = i.id
            LEFT JOIN group_item_name_master_t g  ON fi.group_id = g.id
            LEFT JOIN make_t                   mk ON fi.make_id  = mk.id
            LEFT JOIN model_t                  md ON fi.model_id = md.id
            WHERE  fi.form_id = " . (int)$id . " AND fi.display = 'Y'
            ORDER BY fi.sl_no
        ");

        $this->viewWithLayout('submissions/kfc_form13_view', [
            'title' => 'KFC Form 13 — ' . $form[0]['form_no'],
            'form'  => $form[0],
            'items' => $items ?? [],
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       EXPORT PDF — single form as official KFC Form 13
    ═══════════════════════════════════════════════════════════ */
    public function exportPdf($id = null)
    {
        require_once APP_ROOT . '/vendor/autoload.php';

        $id = (int)($id ?? $_GET['id'] ?? 0);
        if ($id <= 0) { echo 'Invalid ID'; exit; }

        $db = new Database();

        $form = $db->customQuery("
            SELECT f.*, c.college_name, dm.department_name,
                   u1.full_name AS created_by_name,
                   u2.full_name AS approved_by_name
            FROM   kfc_form_13_t f
            LEFT JOIN college_t           c  ON f.institution_id = c.id
            LEFT JOIN department_master_t dm ON f.department_id  = dm.id
            LEFT JOIN users_t u1 ON f.created_by  = u1.id
            LEFT JOIN users_t u2 ON f.approved_by = u2.id
            WHERE  f.id = $id AND f.display = 'Y'
        ");
        if (empty($form)) { echo 'Form not found'; exit; }
        $f = $form[0];

        $items = $db->customQuery("
            SELECT fi.*, i.item_name, g.group_name, mk.make_name
            FROM   kfc_form_13_items_t fi
            LEFT JOIN item_master_t            i  ON fi.item_id  = i.id
            LEFT JOIN group_item_name_master_t g  ON fi.group_id = g.id
            LEFT JOIN make_t                   mk ON fi.make_id  = mk.id
            WHERE  fi.form_id = $id AND fi.display = 'Y'
            ORDER BY fi.sl_no
        ");

        $funds_label = ['Y'=>'Yes','N'=>'No','PARTIAL'=>'Partial'][$f['funds_provided']] ?? '';
        $type_label  = ['GENERAL'=>'General','DIETARY'=>'Dietary','SPC'=>'SPC (Stores Purchase Committee)'][$f['indent_type']] ?? $f['indent_type'];

        ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: "DejaVu Sans", Arial, sans-serif;
        font-size: 9.5px;
        color: #000;
        padding: 14mm 12mm;
    }
    /* ── HEADER ── */
    .kfc-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 8px; }
    .kfc-header .form-ref { font-size: 8px; color: #444; }
    .kfc-header h2 { font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin: 4px 0 2px; }
    .kfc-header h3 { font-size: 10.5px; margin-bottom: 3px; }
    .kfc-header p  { font-size: 9px; margin: 0; }
    /* ── META TABLE ── */
    .meta-section { width: 100%; margin: 8px 0; }
    .meta-section td { padding: 2px 6px; vertical-align: top; font-size: 9px; }
    .meta-section .label { font-weight: bold; white-space: nowrap; width: 38%; }
    .meta-box {
        border: 1px solid #888;
        padding: 5px 8px;
        margin-bottom: 6px;
        font-size: 9px;
    }
    .meta-box strong { display: block; font-size: 8px; text-transform: uppercase;
                       color: #555; margin-bottom: 2px; letter-spacing: 0.5px; }
    .two-col { display: table; width: 100%; margin-bottom: 6px; }
    .two-col .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 6px; }
    .two-col .col:last-child { padding-right: 0; padding-left: 6px; }
    /* ── ITEMS TABLE ── */
    .items-table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 8.5px; }
    .items-table th, .items-table td { border: 1px solid #000; padding: 4px 5px; vertical-align: top; }
    .items-table thead tr:first-child th { background: #1a1a2e; color: #fff; text-align: center; font-size: 8px; }
    .items-table thead tr:last-child  th { background: #f0f0f0; text-align: center; font-size: 8px; }
    .items-table tbody td { color: #111; }
    .items-table .num { text-align: right; }
    .items-table .ctr { text-align: center; }
    .items-table .sl  { text-align: center; width: 24px; }
    /* totals row */
    .items-table tfoot td { border-top: 2px solid #000; font-weight: bold; background: #f9f9f9; font-size: 8.5px; }
    /* ── CERTIFICATION ── */
    .cert-box {
        border: 1px solid #aaa;
        padding: 6px 10px;
        margin: 8px 0;
        font-size: 8.5px;
        font-style: italic;
        background: #fafafa;
    }
    /* ── SIGNATURES ── */
    .sig-row { display: table; width: 100%; margin-top: 18px; }
    .sig-cell { display: table-cell; text-align: center; border-top: 1px solid #000;
                padding-top: 4px; font-size: 8.5px; width: 33%; }
    .sig-cell .role { font-size: 8px; color: #444; margin-top: 2px; }
    /* ── STAMP BOX ── */
    .stamp-area { border: 1px dashed #aaa; height: 50px; text-align: center;
                  color: #ccc; line-height: 50px; font-size: 8px; margin: 8px 0; }
    /* ── FOOTER ── */
    .page-footer { border-top: 1px solid #ccc; margin-top: 10px; padding-top: 4px;
                   font-size: 7.5px; color: #777; text-align: center; }
    .badge {
        display: inline-block; padding: 2px 7px; border-radius: 3px;
        font-size: 8px; font-weight: bold; color: #fff;
    }
    .badge-draft     { background: #6c757d; }
    .badge-submitted { background: #0d6efd; }
    .badge-approved  { background: #198754; }
    .badge-rejected  { background: #dc3545; }
    .badge-closed    { background: #343a40; }
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════
     HEADER
═══════════════════════════════════════════════════════════ -->
<div class="kfc-header">
    <div class="form-ref">Form printed from www.finance.kerala.gov.in &nbsp;|&nbsp; K.F.C. FORM 13</div>
    <p style="font-size:8.5px; margin-top:3px;">GOVERNMENT OF KERALA</p>
    <h2>K.F.C. Form 13</h2>
    <h3>Annual Indent for Stores</h3>
    <p>
        <?= htmlspecialchars($f['college_name'] ?? '') ?>
        <?php if (!empty($f['department_name'])): ?>
            &mdash; <?= htmlspecialchars($f['department_name']) ?>
        <?php elseif (!empty($f['dept_name_free'])): ?>
            &mdash; <?= htmlspecialchars($f['dept_name_free']) ?>
        <?php endif; ?>
    </p>
    <p style="margin-top:3px;">
        <strong>Indent No:</strong> <?= htmlspecialchars($f['form_no']) ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Financial Year:</strong> <?= htmlspecialchars($f['financial_year']) ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Date:</strong> <?= date('d-m-Y', strtotime($f['indent_date'])) ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <span class="badge badge-<?= strtolower($f['status']) ?>"><?= $f['status'] ?></span>
    </p>
</div>

<!-- ═══════════════════════════════════════════════════════════
     FORM DETAILS (2-column layout)
═══════════════════════════════════════════════════════════ -->
<div class="two-col">
    <div class="col">
        <div class="meta-box">
            <strong>Indent Type</strong>
            <?= htmlspecialchars($type_label) ?>
        </div>
        <div class="meta-box">
            <strong>Reference to Previous Correspondence</strong>
            <?= !empty($f['prev_correspondence_ref']) ? htmlspecialchars($f['prev_correspondence_ref']) : '<em style="color:#aaa">None</em>' ?>
        </div>
        <div class="meta-box">
            <strong>Funds Provided in Budget?</strong>
            <?= $funds_label ?>
            <?php if (!empty($f['funds_remark'])): ?>
                &nbsp; — <?= htmlspecialchars($f['funds_remark']) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="col">
        <div class="meta-box">
            <strong>Address for Delivery (Nearest Railway Station)</strong>
            <?= !empty($f['delivery_address']) ? nl2br(htmlspecialchars($f['delivery_address'])) : '<em style="color:#aaa">—</em>' ?>
            <?php if (!empty($f['nearest_railway_station'])): ?>
                <br><em>Rly. Station: <?= htmlspecialchars($f['nearest_railway_station']) ?></em>
            <?php endif; ?>
        </div>
        <div class="meta-box">
            <strong>Inspecting Officer</strong>
            <?= htmlspecialchars($f['inspecting_officer_name'] ?? '—') ?>
            <?php if (!empty($f['inspecting_officer_desig'])): ?>
                , <?= htmlspecialchars($f['inspecting_officer_desig']) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($f['special_instructions'])): ?>
<div class="meta-box">
    <strong>Special Instructions</strong>
    <?= nl2br(htmlspecialchars($f['special_instructions'])) ?>
</div>
<?php endif; ?>

<?php if (!empty($f['sanction_order_no']) || !empty($f['sanction_authority'])): ?>
<div class="meta-box">
    <strong>Sanction Details</strong>
    Authority: <?= htmlspecialchars($f['sanction_authority'] ?? '—') ?>
    &nbsp;|&nbsp; Order No: <?= htmlspecialchars($f['sanction_order_no'] ?? '—') ?>
    <?php if (!empty($f['sanction_order_date'])): ?>
        &nbsp;|&nbsp; Date: <?= date('d-m-Y', strtotime($f['sanction_order_date'])) ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     ITEMS TABLE  (Reverse of KFC Form 13 columns 1-9)
═══════════════════════════════════════════════════════════ -->
<table class="items-table">
    <thead>
        <tr>
            <th rowspan="2" class="sl">Sl.<br>No.<br>(1)</th>
            <th rowspan="2" style="width:22%">Articles with Full Description &amp; Specification<br>(2)</th>
            <th rowspan="2" class="ctr" style="width:8%">Stock on<br>Hand after<br>Verification<br>(3)</th>
            <th rowspan="2" class="ctr" style="width:8%">Purchase<br>of the Year<br>incl. On Order<br>(4)</th>
            <th colspan="4" class="ctr">Qty Required for the Year (5)</th>
            <th rowspan="2" class="ctr" style="width:5%">Unit<br>(6)</th>
            <th rowspan="2" class="ctr" style="width:7%">Last Rate / Est. Cost<br>(7)</th>
            <th rowspan="2" class="ctr" style="width:7%">Amount<br>Rs.<br>(8)</th>
            <th rowspan="2" style="width:11%">Last Supplier<br>(9)</th>
            <th rowspan="2" style="width:10%">Purpose</th>
        </tr>
        <tr>
            <th class="ctr">Total</th>
            <th class="ctr">By Wt.</th>
            <th class="ctr">By No.</th>
            <th class="ctr">By Vol.</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($items)):
            $totalAmt = 0;
            foreach ($items as $row):
                $totalAmt += (float)($row['amount'] ?? 0);
                $desc      = htmlspecialchars($row['article_description'] ?? $row['item_name'] ?? '');
                $extras    = [];
                if (!empty($row['trade_name']))  $extras[] = 'Trade: ' . htmlspecialchars($row['trade_name']);
                if (!empty($row['size_spec']))   $extras[] = 'Size: '  . htmlspecialchars($row['size_spec']);
                if (!empty($row['make_name']))   $extras[] = 'Make: '  . htmlspecialchars($row['make_name']);
                if (!empty($row['brand_remark'])) $extras[] = htmlspecialchars($row['brand_remark']);
        ?>
        <tr>
            <td class="sl"><?= $row['sl_no'] ?></td>
            <td>
                <?= $desc ?>
                <?php if ($extras): ?>
                    <br><span style="font-size:7.5px;color:#555"><?= implode(' &nbsp;|&nbsp; ', $extras) ?></span>
                <?php endif; ?>
                <?php if (!empty($row['group_name'])): ?>
                    <br><span style="font-size:7px;color:#888">Group: <?= htmlspecialchars($row['group_name']) ?></span>
                <?php endif; ?>
            </td>
            <td class="num"><?= $row['stock_on_hand']      ?: '—' ?> <?= htmlspecialchars($row['stock_unit'] ?? '') ?></td>
            <td class="num"><?= $row['purchases_this_year'] ?: '—' ?></td>
            <td class="num"><?= $row['qty_required']        ?: '—' ?></td>
            <td class="num"><?= $row['qty_required_by_weight'] ?: '—' ?></td>
            <td class="num"><?= $row['qty_required_by_number'] ?: '—' ?></td>
            <td class="num"><?= $row['qty_required_by_volume'] ?: '—' ?></td>
            <td class="ctr"><?= htmlspecialchars($row['unit'] ?? '') ?></td>
            <td class="num">
                <?php if (!empty($row['last_purchase_rate'])): ?>
                    <?= number_format($row['last_purchase_rate'], 2) ?>
                <?php elseif (!empty($row['estimated_cost'])): ?>
                    ~<?= number_format($row['estimated_cost'], 2) ?>
                <?php else: echo '—'; endif; ?>
            </td>
            <td class="num">
                <?= !empty($row['amount']) ? number_format($row['amount'], 2) : '—' ?>
            </td>
            <td><?= htmlspecialchars($row['last_supplier_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['purpose'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <tfoot>
            <td colspan="10" style="text-align:right; font-weight:bold;">Total Estimated Amount (Rs.)</td>
            <td class="num"><strong><?= number_format($totalAmt, 2) ?></strong></td>
            <td colspan="2"></td>
            </tfoot>
        </tr>
        <?php else: ?>
        <tr>
            <td colspan="13" class="ctr" style="padding:12px; color:#777; font-style:italic;">No items added to this indent.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- ═══════════════════════════════════════════════════════════
     CERTIFICATION BLOCK
═══════════════════════════════════════════════════════════ -->
<div class="cert-box">
    "I hereby certify that the purchase of the stores has been sanctioned by the competent authority
    <?php if (!empty($f['sanction_order_no'])): ?>
        vide sanction order no. <strong><?= htmlspecialchars($f['sanction_order_no']) ?></strong>
    <?php endif; ?>
    and that the funds required for the expenditure involved have been provided in the budget."
</div>

<!-- ═══════════════════════════════════════════════════════════
     SIGNATURES
═══════════════════════════════════════════════════════════ -->
<div class="sig-row">
    <div class="sig-cell">
        <?= htmlspecialchars($f['created_by_name'] ?? '') ?>
        <div class="role">Prepared By</div>
        <div class="role"><?= date('d-m-Y', strtotime($f['created_at'])) ?></div>
    </div>
    <div class="sig-cell" style="border:none"></div>
    <?php if (!empty($f['approved_by_name'])): ?>
    <div class="sig-cell">
        <?= htmlspecialchars($f['approved_by_name']) ?>
        <div class="role">Approved By</div>
        <div class="role"><?= $f['approved_on'] ? date('d-m-Y', strtotime($f['approved_on'])) : '' ?></div>
    </div>
    <?php else: ?>
    <div class="sig-cell">
        &nbsp;
        <div class="role">Authorised Signatory</div>
        <div class="role">Date:</div>
    </div>
    <?php endif; ?>
</div>

<div class="stamp-area">Office Seal</div>

<!-- ═══════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════ -->
<div class="page-footer">
    Generated on <?= date('d-m-Y H:i') ?> &nbsp;|&nbsp;
    Station: <?= htmlspecialchars($f['delivery_place'] ?? $f['college_name'] ?? '') ?> &nbsp;|&nbsp;
    Form printed from www.finance.kerala.gov.in
</div>

</body>
</html>
        <?php
        $html = ob_get_clean();

        $opt = new \Dompdf\Options();
        $opt->set('defaultFont', 'DejaVu Sans');
        $opt->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($opt);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A3', 'landscape');   // A3 landscape for wide table
        $dompdf->render();
        $dompdf->stream('KFC_Form13_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $f['form_no']) . '.pdf',
                        ['Attachment' => false]);
        exit;
    }

    /* ═══════════════════════════════════════════════════════════
       PRIVATE — Sync items for a form
    ═══════════════════════════════════════════════════════════ */
    private function _syncItems(Database $db, int $formId, array $items): void
    {
        if (empty($items)) return;

        /* Soft-delete all existing items first, then re-insert */
        $db->updateData('kfc_form_13_items_t', ['display'=>'N'], ['form_id'=>$formId]);

        $slNo = 1;
        foreach ($items as $item) {
            $qty   = (float)($item['qty_required']   ?? 0);
            $amt   = !empty($item['amount']) ? (float)$item['amount']
                   : ($qty * (float)($item['last_purchase_rate'] ?? $item['estimated_cost'] ?? 0));

            $row = [
                'form_id'                  => $formId,
                'sl_no'                    => $slNo++,
                'group_id'                 => (int)($item['group_id']  ?? 0) ?: null,
                'item_id'                  => (int)($item['item_id']   ?? 0) ?: null,
                'article_description'      => htmlspecialchars(trim($item['article_description'] ?? $item['item_name'] ?? ''), ENT_QUOTES),
                'trade_name'               => htmlspecialchars(trim($item['trade_name']  ?? ''), ENT_QUOTES),
                'size_spec'                => htmlspecialchars(trim($item['size_spec']   ?? ''), ENT_QUOTES),
                'make_id'                  => (int)($item['make_id']   ?? 0) ?: null,
                'model_id'                 => (int)($item['model_id']  ?? 0) ?: null,
                'brand_remark'             => htmlspecialchars(trim($item['brand_remark'] ?? ''), ENT_QUOTES),
                'stock_on_hand'            => (float)($item['stock_on_hand']            ?? 0),
                'stock_unit'               => htmlspecialchars(trim($item['stock_unit'] ?? ''), ENT_QUOTES),
                'purchases_this_year'      => (float)($item['purchases_this_year']      ?? 0),
                'goods_on_order'           => (float)($item['goods_on_order']           ?? 0),
                'qty_required'             => $qty,
                'qty_required_by_weight'   => (float)($item['qty_required_by_weight']   ?? 0) ?: null,
                'qty_required_by_number'   => (float)($item['qty_required_by_number']   ?? 0) ?: null,
                'qty_required_by_volume'   => (float)($item['qty_required_by_volume']   ?? 0) ?: null,
                'unit'                     => htmlspecialchars(trim($item['unit'] ?? ''), ENT_QUOTES),
                'last_purchase_rate'       => (float)($item['last_purchase_rate'] ?? 0) ?: null,
                'estimated_cost'           => (float)($item['estimated_cost']     ?? 0) ?: null,
                'is_fresh_purchase'        => ($item['is_fresh_purchase'] ?? 'N') === 'Y' ? 'Y' : 'N',
                'amount'                   => $amt ?: null,
                'last_supplier_name'       => htmlspecialchars(trim($item['last_supplier_name']    ?? ''), ENT_QUOTES),
                'last_supplier_address'    => htmlspecialchars(trim($item['last_supplier_address'] ?? ''), ENT_QUOTES),
                'purpose'                  => htmlspecialchars(trim($item['purpose']               ?? ''), ENT_QUOTES),
                'place_of_delivery'        => htmlspecialchars(trim($item['place_of_delivery']     ?? ''), ENT_QUOTES),
                'display'                  => 'Y',
                'created_by'               => $this->_userId(),
            ];

            $db->insertData('kfc_form_13_items_t', $row);
        }
    }
}