<?php
// backend/api/invoice_items.php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and model files
include_once '../config/database.php';
include_once '../models/InvoiceItem.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize invoice item object
$invoice_item = new InvoiceItem($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Check if invoice_id parameter exists
        if(isset($_GET['invoice_id'])) {
            // Get items by invoice
            $invoice_item->invoice_id = $_GET['invoice_id'];
            $stmt = $invoice_item->readByInvoice();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $items_arr = array();
                $items_arr["records"] = array();
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $item = array(
                        "id" => $id,
                        "invoice_id" => $invoice_id,
                        "task_desc" => $task_desc,
                        "price" => $price,
                        "is_subtask" => $is_subtask
                    );
                    
                    array_push($items_arr["records"], $item);
                }
                
                http_response_code(200);
                echo json_encode($items_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("message" => "No items found for this invoice.", "records" => array()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Missing invoice_id parameter."));
        }
        break;
        
    case 'POST':
        // Create invoice item
        $data = json_decode(file_get_contents("php://input"));
        
        if(
            !empty($data->invoice_id) &&
            !empty($data->task_desc) &&
            isset($data->price)
        ) {
            $invoice_item->invoice_id = $data->invoice_id;
            $invoice_item->task_desc = $data->task_desc;
            $invoice_item->price = $data->price;
            $invoice_item->is_subtask = isset($data->is_subtask) ? $data->is_subtask : 0;
            
            if($invoice_item->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Item was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create item."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create item. Data is incomplete."));
        }
        break;
        
    case 'PUT':
        // Update invoice item
        $data = json_decode(file_get_contents("php://input"));
        
        if(
            !empty($data->id) &&
            !empty($data->task_desc) &&
            isset($data->price)
        ) {
            $invoice_item->id = $data->id;
            $invoice_item->task_desc = $data->task_desc;
            $invoice_item->price = $data->price;
            $invoice_item->is_subtask = isset($data->is_subtask) ? $data->is_subtask : 0;
            
            if($invoice_item->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Item was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update item."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update item. Data is incomplete."));
        }
        break;
        
    case 'DELETE':
        // Delete invoice item
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $invoice_item->id = $data->id;
            
            if($invoice_item->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Item was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete item."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete item. Please provide an ID."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
