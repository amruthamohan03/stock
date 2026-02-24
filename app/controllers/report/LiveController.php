<?php

class LiveController extends Controller
{
    /**
     * Main Live Stock Dashboard view
     */
    public function index()
    {
        $db = new Database();

        // Fetch all active items for the filter dropdown
        $items = $db->selectData('item_master_t', 'id, item_name', ['display' => 'Y']);

        // Fetch all active locations for filter dropdown
        $locations = $db->selectData('issued_to_master_t', 'id, location_name', ['display' => 'Y']);

        // Fetch item-wise live stock summary
        $liveStock = $this->fetchLiveStockData($db);

        // Overall summary totals
        $totals = $this->calcTotals($liveStock);

        $data = [
            'title'      => 'Live Stock Register',
            'items'      => $items,
            'locations'  => $locations,
            'liveStock'  => $liveStock,
            'totals'     => $totals,
        ];

        $this->viewWithLayout('reports/live-stock', $data);
    }

    /**
     * AJAX endpoint – returns JSON for auto-refresh polling
     */
    public function getLiveData()
    {
        header('Content-Type: application/json');

        $db        = new Database();
        $item_id   = isset($_GET['item_id'])   ? (int)$_GET['item_id']   : 0;
        $location  = isset($_GET['location'])  ? trim($_GET['location'])  : '';

        $liveStock = $this->fetchLiveStockData($db, $item_id, $location);
        $totals    = $this->calcTotals($liveStock);

        echo json_encode([
            'success'   => true,
            'data'      => $liveStock,
            'totals'    => $totals,
            'timestamp' => date('d-m-Y H:i:s'),
        ]);
        exit;
    }

    /**
     * Core aggregation query
     *
     * Groups by item and computes:
     *   - total_receipt   : sum of all receipt_qty
     *   - total_issued    : sum of all issue_qty
     *   - working_count   : qty where item_status = 'WORKING'
     *   - not_working     : qty where item_status = 'NOT WORKING'
     *   - deleted_count   : qty where item_status = 'DELETED'
     *   - repaired_count  : qty where item_status = 'REPAIRED'
     *   - pending_count   : qty where item_status = 'PENDING'
     *   - transferred_qty : issue_qty where issued_to_location_id IS NOT NULL (issued to a named location)
     *   - current_balance : latest balance from stock_book_t (sum across locations per item)
     */
    private function fetchLiveStockData(Database $db, int $item_id = 0, string $location = ''): array
    {
        $where = "WHERE i.display = 'Y' AND sb.display = 'Y'";

        if ($item_id > 0) {
            $where .= " AND i.id = " . $item_id;
        }
        if (!empty($location)) {
            $safeLocation = addslashes($location);
            $where .= " AND sb.location = '$safeLocation'";
        }

        $sql = "
            SELECT
                i.id                                                        AS item_id,
                i.item_name,

                /* ── Stock Book totals (across all locations for this item) ── */
                SUM(sb.opening_balance)                                     AS opening_balance,
                SUM(sb.current_balance)                                     AS current_balance,

                /* ── Transaction type aggregates ── */
                COALESCE(SUM(CASE WHEN st.transaction_type = 'RECEIPT'         THEN st.receipt_qty  ELSE 0 END), 0) AS total_receipt,
                COALESCE(SUM(CASE WHEN st.transaction_type = 'ISSUE'           THEN st.issue_qty    ELSE 0 END), 0) AS total_issued,
                COALESCE(SUM(CASE WHEN st.transaction_type = 'BROUGHT_FORWARD' THEN st.receipt_qty  ELSE 0 END), 0) AS brought_forward,
                COALESCE(SUM(CASE WHEN st.transaction_type = 'ADJUSTMENT'      THEN st.receipt_qty - st.issue_qty ELSE 0 END), 0) AS adjustment_qty,

                /* ── Item-status aggregates (by receipt qty in that status) ── */
                COALESCE(SUM(CASE WHEN st.item_status = 'WORKING'     THEN st.receipt_qty ELSE 0 END), 0) AS working_receipt,
                COALESCE(SUM(CASE WHEN st.item_status = 'NOT WORKING' THEN st.receipt_qty ELSE 0 END), 0) AS not_working_receipt,
                COALESCE(SUM(CASE WHEN st.item_status = 'DELETED'     THEN st.receipt_qty ELSE 0 END), 0) AS deleted_receipt,
                COALESCE(SUM(CASE WHEN st.item_status = 'REPAIRED'    THEN st.receipt_qty ELSE 0 END), 0) AS repaired_receipt,
                COALESCE(SUM(CASE WHEN st.item_status = 'PENDING'     THEN st.receipt_qty ELSE 0 END), 0) AS pending_receipt,

                /* ── Transferred = issued to a named location (issued_to_location_id set) ── */
                COALESCE(SUM(CASE WHEN st.transaction_type = 'ISSUE' AND st.issued_to_location_id IS NOT NULL AND st.issued_to_location_id <> 0
                                  THEN st.issue_qty ELSE 0 END), 0)          AS transferred_qty,

                /* ── Transaction & location counts ── */
                COUNT(DISTINCT st.id)                                        AS transaction_count,
                COUNT(DISTINCT sb.location)                                  AS location_count,
                GROUP_CONCAT(DISTINCT sb.location ORDER BY sb.location SEPARATOR ', ') AS locations_list,

                /* ── Latest activity ── */
                MAX(st.transaction_date)                                     AS last_transaction_date

            FROM item_master_t i
            INNER JOIN stock_book_t sb   ON sb.item_id = i.id
            LEFT  JOIN stock_transaction_t st ON st.stock_book_id = sb.id
            $where
            GROUP BY i.id, i.item_name
            ORDER BY i.item_name ASC
        ";

        $result = $db->customQuery($sql);
        return $result ?: [];
    }

    /**
     * Compute overall totals row from the aggregated data
     */
    private function calcTotals(array $liveStock): array
    {
        $keys = [
            'opening_balance', 'current_balance', 'total_receipt', 'total_issued',
            'brought_forward', 'adjustment_qty', 'transferred_qty',
            'working_receipt', 'not_working_receipt', 'deleted_receipt',
            'repaired_receipt', 'pending_receipt', 'transaction_count',
        ];

        $totals = array_fill_keys($keys, 0);

        foreach ($liveStock as $row) {
            foreach ($keys as $k) {
                $totals[$k] += (int)($row[$k] ?? 0);
            }
        }

        return $totals;
    }
}