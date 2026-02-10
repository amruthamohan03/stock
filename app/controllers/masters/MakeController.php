<?php

class MakeController extends Controller{
    public function index()
    {
        $db         = new Database();
        $result     = $db->selectData('make_t', '*', []);
        $data       = [
            'title'  => 'Make Management',
            'result' => $result
        ];
        $this->viewWithLayout('masters/make', $data);
    }

    public function crudData($action = 'insertion')
    {
        $db     = new Database();
        $table  = 'make_t';

        // Helper function to sanitize
        function sanitize($value)
        {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }

        // 🔹 INSERTION (Add new make)
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'make_name'   => sanitize($_POST['make_name'] ?? ''),
                'display'     => isset($_POST['display']) && in_array($_POST['display'], ['Y','N']) ? $_POST['display'] : 'Y',
                'created_by'  => 1, // Replace with actual user ID from session
                'updated_by'  => 1,
            ];

            if (empty($data['make_name'])) {
                echo json_encode(['success'=>false,'message'=>'Make Name is required.']);
                exit;
            }

            $insertId = $db->insertData($table, $data);

            if ($insertId) {
                echo json_encode(['success'=>true,'message'=>'Make inserted successfully.','id'=>$insertId]);
            } else {
                echo json_encode(['success'=>false,'message'=>'Insert failed.']);
            }
            exit;
        }


        // 🔹 UPDATION (Edit existing make)
        elseif ($action === 'updation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid Make ID']);
                exit;
            }

            $data = [
                'make_name'  => htmlspecialchars(trim($_POST['make_name']), ENT_QUOTES),
                'display'    => in_array($_POST['display'], ['Y','N']) ? $_POST['display'] : 'Y',
                'updated_by' => 1 // Replace with actual user ID from session
            ];

            if (empty($data['make_name'])) {
                echo json_encode(['success'=>false,'message'=>'Make Name is required.']);
                exit;
            }

            $update = $db->updateData($table, $data, ['id' => $id]);

            if($update){
                echo json_encode(['success'=>true,'message'=>'Make updated successfully']);
            } else {
                echo json_encode(['success'=>false,'message'=>'Update failed']);
            }
            exit;
        }


        // 🔹 DELETION (Delete by ID)
        elseif($action === 'deletion'){
            header('Content-Type: application/json');
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            if($id <= 0){
                echo json_encode(['success'=>false,'message'=>'Invalid Make ID']);
                exit;
            }

            $delete = $db->deleteData($table, ['id'=>$id]);
            if($delete){
                echo json_encode(['success'=>true,'message'=>'Make deleted successfully']);
            } else {
                echo json_encode(['success'=>false,'message'=>'Delete failed']);
            }
            exit;
        }
    }

    public function getMakeById() {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if($id <= 0){
            echo json_encode(['success'=>false,'message'=>'Invalid ID']);
            exit;
        }

        $db = new Database();
        $make = $db->selectData('make_t','*',['id'=>$id]);
        if(!empty($make)){
            echo json_encode(['success'=>true,'data'=>$make[0]]);
        } else {
            echo json_encode(['success'=>false,'message'=>'Make not found']);
        }
        exit;
    }

}