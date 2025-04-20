<?php
// backend/api/clients.php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and model files
include_once '../config/database.php';
include_once '../models/Client.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize client object
$client = new Client($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Check if ID parameter exists
        if(isset($_GET['id'])) {
            // Get client by ID
            $client->id = $_GET['id'];
            
            if($client->readOne()) {
                $client_arr = array(
                    "id" => $client->id,
                    "client_name" => $client->client_name,
                    "client_email" => $client->client_email,
                    "is_client_inv_req_auto_incre" => $client->is_client_inv_req_auto_incre,
                    "order_prefix" => $client->order_prefix
                );
                
                http_response_code(200);
                echo json_encode($client_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Client not found."));
            }
        } else {
            // Get all clients
            $stmt = $client->read();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $clients_arr = array();
                $clients_arr["records"] = array();
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $client_item = array(
                        "id" => $id,
                        "client_name" => $client_name,
                        "client_email" => $client_email,
                        "is_client_inv_req_auto_incre" => $is_client_inv_req_auto_incre,
                        "order_prefix" => $order_prefix
                    );
                    
                    array_push($clients_arr["records"], $client_item);
                }
                
                http_response_code(200);
                echo json_encode($clients_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("message" => "No clients found.", "records" => array()));
            }
        }
        break;
        
    case 'POST':
        // Create client
        $data = json_decode(file_get_contents("php://input"));
        
        if(
            !empty($data->client_name) &&
            !empty($data->client_email) &&
            isset($data->is_client_inv_req_auto_incre) &&
            !empty($data->order_prefix)
        ) {
            $client->client_name = $data->client_name;
            $client->client_email = $data->client_email;
            $client->is_client_inv_req_auto_incre = $data->is_client_inv_req_auto_incre;
            $client->order_prefix = $data->order_prefix;
            
            if($client->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Client was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create client."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create client. Data is incomplete."));
        }
        break;
        
    case 'PUT':
        // Update client
        $data = json_decode(file_get_contents("php://input"));
        
        if(
            !empty($data->id) &&
            !empty($data->client_name) &&
            !empty($data->client_email) &&
            isset($data->is_client_inv_req_auto_incre) &&
            !empty($data->order_prefix)
        ) {
            $client->id = $data->id;
            $client->client_name = $data->client_name;
            $client->client_email = $data->client_email;
            $client->is_client_inv_req_auto_incre = $data->is_client_inv_req_auto_incre;
            $client->order_prefix = $data->order_prefix;
            
            if($client->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Client was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update client."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update client. Data is incomplete."));
        }
        break;
        
    case 'DELETE':
        // Delete client
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $client->id = $data->id;
            
            if($client->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Client was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete client."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete client. Please provide an ID."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
