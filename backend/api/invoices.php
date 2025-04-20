<?php
// backend/api/invoices.php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and model files
include_once '../config/database.php';
include_once '../models/Invoice.php';
include_once '../models/InvoiceItem.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$invoice = new Invoice($db);
$invoice_item = new InvoiceItem($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Check if ID parameter exists
        if(isset($_GET['id'])) {
            // Get invoice by ID
            $invoice->id = $_GET['id'];
            
            if($invoice->readOne()) {
                // Get invoice items
                $invoice_item->invoice_id = $invoice->id;
                $items_stmt = $invoice_item->readByInvoice();
                $items = array();
                
                while($row = $items_stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $item = array(
                        "id" => $id,
                        "invoice_id" => $invoice_id,
                        "task_desc" => $task_desc,
                        "price" => $price,
                        "is_subtask" => $is_subtask
                    );
                    array_push($items, $item);
                }
                
                $invoice_arr = array(
                    "id" => $invoice->id,
                    "client_id" => $invoice->client_id,
                    "order_no" => $invoice->order_no,
                    "order_date" => $invoice->order_date,
                    "payment_method" => $invoice->payment_method,
                    "client_name" => $invoice->client_name,
                    "client_email" => $invoice->client_email,
                    "items" => $items,
                    "total" => $invoice_item->calculateInvoiceTotal()
                );
                
                http_response_code(200);
                echo json_encode($invoice_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Invoice not found."));
            }
        } 
        // Check if client_id parameter exists
        else if(isset($_GET['client_id'])) {
            // Get invoices by client
            $invoice->client_id = $_GET['client_id'];
            $stmt = $invoice->getInvoicesByClient();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $invoices_arr = array();
                $invoices_arr["records"] = array();
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $invoice_item->invoice_id = $id;
                    $total = $invoice_item->calculateInvoiceTotal();
                    
                    $invoice_item = array(
                        "id" => $id,
                        "client_id" => $client_id,
                        "order_no" => $order_no,
                        "order_date" => $order_date,
                        "payment_method" => $payment_method,
                        "total" => $total
                    );
                    
                    array_push($invoices_arr["records"], $invoice_item);
                }
                
                http_response_code(200);
                echo json_encode($invoices_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("message" => "No invoices found for this client.", "records" => array()));
            }
        }
        else {
            // Get all invoices
            $stmt = $invoice->read();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $invoices_arr = array();
                $invoices_arr["records"] = array();
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $invoice_item->invoice_id = $id;
                    $total = $invoice_item->calculateInvoiceTotal();
                    
                    $invoice_item = array(
                        "id" => $id,
                        "client_id" => $client_id,
                        "client_name" => $client_name,
                        "order_no" => $order_no,
                        "order_date" => $order_date,
                        "payment_method" => $payment_method,
                        "total" => $total
                    );
                    
                    array_push($invoices_arr["records"], $invoice_item);
                }
                
                http_response_code(200);
                echo json_encode($invoices_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("message" => "No invoices found.", "records" => array()));
            }
        }
        break;
        
    case 'POST':
        // Create invoice with items
        $data = json_decode(file_get_contents("php://input"));
        
        if(
            !empty($data->client_id) &&
            !empty($data->order_date) &&
            !empty($data->payment_method) &&
            is_array($data->items) && 
            count($data->items) > 0
        ) {
            // Set invoice properties
            $invoice->client_id = $data->client_id;
            $invoice->order_date = $data->order_date;
            $invoice->payment_method = $data->payment_method;
            
            // For manual order_no
            if(isset($data->order_no) && !empty($data->order_no)) {
                $invoice->order_no = $data->order_no;
            }
            
            // Create invoice
            $invoice_id = $invoice->create();
            
            if($invoice_id) {
                // Set invoice item invoice_id
                $invoice_item->invoice_id = $invoice_id;
                
                // Insert invoice items
                $success = true;
                foreach($data->items as $item) {
                    $invoice_item->task_desc = $item->task_desc;
                    $invoice_item->price = $item->price;
                    $invoice_item->is_subtask = isset($item->is_subtask) ? $item->is_subtask : 0;
                    
                    if(!$invoice_item->create()) {
                        $success = false;
                        break;
                    }
                }
                
                if($success) {
                    http_response_code(201);
                    echo json_encode(array(
                        "message" => "Invoice was created.",
                        "invoice_id" => $invoice_id
                    ));
                } else {
                    // If items creation failed, delete the invoice
                    $invoice->id = $invoice_id;
                    $invoice->delete();
                    
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to create invoice items."));
                }
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create invoice."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create invoice. Data is incomplete."));
        }
        break;
        
    case 'PUT':
        // Update invoice with items
        $data = json_decode(file_get_contents("php://input"));
        
        if(
            !empty($data->id) &&
            !empty($data->client_id) &&
            !empty($data->order_no) &&
            !empty($data->order_date) &&
            !empty($data->payment_method) &&
            is_array($data->items)
        ) {
            // Set invoice properties
            $invoice->id = $data->id;
            $invoice->client_id = $data->client_id;
            $invoice->order_no = $data->order_no;
            $invoice->order_date = $data->order_date;
            $invoice->payment_method = $data->payment_method;
            
            // Update invoice
            if($invoice->update()) {
                // Delete existing items
                $invoice_item->invoice_id = $invoice->id;
                $invoice_item->deleteByInvoice();
                
                // Insert updated items
                $success = true;
                foreach($data->items as $item) {
                    $invoice_item->invoice_id = $invoice->id;
                    $invoice_item->task_desc = $item->task_desc;
                    $invoice_item->price = $item->price;
                    $invoice_item->is_subtask = isset($item->is_subtask) ? $item->is_subtask : 0;
                    
                    if(!$invoice_item->create()) {
                        $success = false;
                        break;
                    }
                }
                
                if($success) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Invoice was updated."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to update invoice items."));
                }
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update invoice."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update invoice. Data is incomplete."));
        }
        break;
        
    case 'DELETE':
        // Delete invoice
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $invoice->id = $data->id;
            
            // Delete invoice (will cascade delete items due to FK constraint)
            if($invoice->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Invoice was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete invoice."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete invoice. Please provide an ID."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
