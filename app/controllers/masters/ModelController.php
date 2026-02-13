<?php

class ModelController extends Controller{
    public function index()
    {
        $db         = new Database();
        
        // Get all makes for dropdown
        $makes      = $db->selectData('make_t', 'id, make_name', ['display' => 'Y']);
        
        // Get all models with make information (JOIN)
        $query = "SELECT m.*, mk.make_name 
                  FROM model_t m 
                  LEFT JOIN make_t mk ON m.make_id = mk.id 
                  ORDER BY m.id DESC";
        $result = $db->customQuery($query);
        $data       = [
            'title'  => 'Model Management',
            'makes'  => $makes,
            'result' => $result
        ];
        $this->viewWithLayout('masters/model', $data);
    }

    public function crudData($action = 'insertion')
    {
        $db     = new Database();
        $table  = 'model_t';

        // Helper function to sanitize
        function sanitize($value)
        {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }

        // 🔹 INSERTION (Add new model)
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'make_id'     => isset($_POST['make_id']) && !empty($_POST['make_id']) ? (int)$_POST['make_id'] : null,
                'model_name'  => sanitize($_POST['model_name'] ?? ''),
                'display'     => isset($_POST['display']) && in_array($_POST['display'], ['Y','N']) ? $_POST['display'] : 'Y',
                'created_by'  => 1, // Replace with actual user ID from session
                'updated_by'  => 1,
            ];

            // Validation
            if (empty($data['model_name'])) {
                echo json_encode(['success'=>false,'message'=>'Model Name is required.']);
                exit;
            }

            if (empty($data['make_id'])) {
                echo json_encode(['success'=>false,'message'=>'Please select a Make.']);
                exit;
            }

            $insertId = $db->insertData($table, $data);

            if ($insertId) {
                echo json_encode(['success'=>true,'message'=>'Model inserted successfully.','id'=>$insertId]);
            } else {
                echo json_encode(['success'=>false,'message'=>'Insert failed.']);
            }
            exit;
        }


        // 🔹 UPDATION (Edit existing model)
        elseif ($action === 'updation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid Model ID']);
                exit;
            }

            $data = [
                'make_id'    => isset($_POST['make_id']) && !empty($_POST['make_id']) ? (int)$_POST['make_id'] : null,
                'model_name' => htmlspecialchars(trim($_POST['model_name']), ENT_QUOTES),
                'display'    => in_array($_POST['display'], ['Y','N']) ? $_POST['display'] : 'Y',
                'updated_by' => 1 // Replace with actual user ID from session
            ];

            // Validation
            if (empty($data['model_name'])) {
                echo json_encode(['success'=>false,'message'=>'Model Name is required.']);
                exit;
            }

            if (empty($data['make_id'])) {
                echo json_encode(['success'=>false,'message'=>'Please select a Make.']);
                exit;
            }

            $update = $db->updateData($table, $data, ['id' => $id]);

            if($update){
                echo json_encode(['success'=>true,'message'=>'Model updated successfully']);
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
                echo json_encode(['success'=>false,'message'=>'Invalid Model ID']);
                exit;
            }

            $delete = $db->deleteData($table, ['id'=>$id]);
            if($delete){
                echo json_encode(['success'=>true,'message'=>'Model deleted successfully']);
            } else {
                echo json_encode(['success'=>false,'message'=>'Delete failed']);
            }
            exit;
        }
    }

    public function getModelById() {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if($id <= 0){
            echo json_encode(['success'=>false,'message'=>'Invalid ID']);
            exit;
        }

        $db = new Database();
        $model = $db->selectData('model_t','*',['id'=>$id]);
        if(!empty($model)){
            echo json_encode(['success'=>true,'data'=>$model[0]]);
        } else {
            echo json_encode(['success'=>false,'message'=>'Model not found']);
        }
        exit;
    }

}